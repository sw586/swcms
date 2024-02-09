<?php
// 多模块合并查询

$modules = \Phpcmf\Service::L('cache')->get('module-'.$system['site'].'-content');
if (!$modules) {
    return $return_data = $this->_return($system['return'], '站点('.$system['site'].')没有安装内容模块');
}

if ($system['module'] == 'all') {
    $system['module'] = '';
    foreach ($modules as $t) {
        $system['module'].= $t['dirname'].',';
        $module_all[] = $t['dirname'];
    }
    $system['module'] = trim($system['module'], ',');
} else {
    $module_all = explode(',', $system['module']);
    if (dr_count($module_all) == 1) {
        $rt = require 'Module.php';
        return $rt;
    }
}

if ($this->_return_sql) {
    $system['field'].= ',id,'.$system['sum'];
}

if (!$system['field']) {
    return $return_data = $this->_return($system['return'], '必须传入field参数来指定显示字段');
}

if ($system['more']) {
    $this->_list_error[] = '多模块查询时more参数将会无效';
}

if ($system['join']) {
    $this->_list_error[] = '多模块查询时join参数将会无效';
}

$system['field'] = trim($system['field'], ',');
$field = explode(',', $system['field']);

// 是否操作自定义where
if ($param['where']) {
    $where[] = [
        'adj' => 'SQL',
        'value' => urldecode($param['where'])
    ];
    unset($param['where']);
}

$form = [];

// 验证模块的有效性
foreach ($module_all as $m) {
    if (!isset($modules[$m])) {
        return $return_data = $this->_return($system['return'], '站点('.$system['site'].')没有安装内容模块('.$m.')');
    }
    $table = \Phpcmf\Service::M()->dbprefix($system['site'].'_'.$m);
    $mfield = \Phpcmf\Service::M()->db->getFieldNames($table);
    $infield = array_diff($field, $mfield);
    if ($infield) {
        return $return_data = $this->_return($system['return'], '站点('.$system['site'].')的内容模块('.$m.')不存在的字段：'.implode(',', $infield));
    }
    $mywhere = [
        // '`status` = 9',
    ];
    if (isset($where['my_related']) && $where['my_related']) {
        $mywhere[] = str_replace('{xunruicms_mid}', $m, $where['my_related']['value']);
    }
    // 推荐位调用
    if ($system['flag']) {
        $mywhere[] = "`$table`.`id` IN (".("select `id` from `{$table}_flag` where ".(strpos($system['flag'], ',') ? '`flag` IN ('.$this->_get_where_in($system['flag']).')' : '`flag`='.(int)$system['flag'])).")";
    }
    // 排除推荐位
    if ($system['not_flag']) {
        $mywhere[] = "`$table`.`id` NOT IN (".("select `id` from `{$table}_flag` where ".(strpos($system['not_flag'], ',') ? '`flag` IN ('.$this->_get_where_in($system['not_flag']).')' : '`flag`='.(int)$system['not_flag'])).")";
    }
    $module = \Phpcmf\Service::L('cache')->get('module-'.$system['site'].'-'.$m);
    $fields = $module['field'];
    // 栏目筛选
    if ($system['catid']) {
        $fwhere = [];
        if (strpos($system['catid'], ',') !== FALSE) {
            $temp = explode(',', $system['catid']);
            if ($temp) {
                $catids = [];
                foreach ($temp as $i) {
                    $cat = dr_cat_value($system['site'], $module['mid'], $i);
                    $catids = $cat['child'] ? array_merge($catids, $cat['catids']) : array_merge($catids, array($i));
                }
                $catids && $fwhere[] = '`'.$table.'`.`catid` IN ('.implode(',', $catids).')';
            }
            unset($temp);
        } else {
            $cat = dr_cat_value($system['site'], $module['mid'], $system['catid']);
            if ($cat['child']) {
                $catids = explode(',', $cat['childids']);
                $fwhere[] = '`'.$table.'`.`catid` IN ('.$cat['childids'].')';
            } else {
                $fwhere[] = '`'.$table.'`.`catid` = '.(int)$system['catid'];
                $catids = [$system['catid']];
            }
        }

        // 副栏目判断
        if (isset($fields['catids']) && $fields['catids']['fieldtype'] = 'Catids') {
            foreach ($catids as $c) {
                if (version_compare(\Phpcmf\Service::M()->db->getVersion(), '5.7.0') < 0) {
                    // 兼容写法
                    $fwhere[] = '`'.$table.'`.`catids` LIKE "%\"'.intval($c).'\"%"';
                } else {
                    // 高版本写法
                    $fwhere[] = "(`{$table}`.`catids` <>'' AND JSON_CONTAINS (`{$table}`.`catids`->'$[*]', '\"".intval($c)."\"', '$'))";
                }
            }
        }
        if ($fwhere) {
            $mywhere[] = ''.(count($fwhere) == 1 ? $fwhere[0] : '('.implode(' OR ', $fwhere).')');
        }
        unset($fwhere);
        unset($catids);
    }
    if ($mywhere) {
        $form[] = 'SELECT '.$system['field'].',\''.$m.'\' AS mid FROM `'.$table.'` WHERE '.implode(' AND ', $mywhere);
    } else {
        $form[] = 'SELECT '.$system['field'].',\''.$m.'\' AS mid FROM `'.$table.'`';
    }

}

if (isset($where['my_related']) && $where['my_related']) {
    unset($where['my_related']);
}

$sql_limit = $pages = '';
$where = $this->_set_where_field_prefix($where, $field, 'my', $fields); // 给条件字段加上表前缀
$sql_where = $this->_get_where($where); // sql的where子句


$sql_from = '('. implode(' UNION ALL ', $form).') as my';

// 统计标签
if ($this->_return_sql) {
    $sql = "SELECT ".$this->_select_rt_name." FROM $sql_from ".($sql_where ? "WHERE $sql_where" : "")." ORDER BY NULL";
} else {
    if ($system['page']) {
        // 统计数量
        $page = $this->_get_page_id($system['page']);
        $pagesize = (int)$system['pagesize'];
        !$pagesize && $pagesize = 10;
        $sql = "SELECT count(*) as c FROM $sql_from " . ($sql_where ? "WHERE $sql_where" : "") . " ORDER BY NULL";
        $row = $this->_query($sql, $system, FALSE);
        $total = (int)$row['c'];
        if ($system['maxlimit'] && $total > $system['maxlimit']) {
            $total = $system['maxlimit']; // 最大限制
            if ($page * $pagesize > $total) {
                $return_data = $this->_return($system['return'], 'maxlimit设置最大显示'.$system['maxlimit'].'条，当前（'.$total.'）已超出', $sql, 0);
                return;
            }
        }
        // 没有数据时返回空
        if (!$total) {
            return $return_data = $this->_return($system['return'], '没有查询到内容', $sql, 0);
        }
        // 计算分页标签
        $pages = $this->_new_pagination($system, $pagesize, $total);
        $sql_limit = 'LIMIT ' . intval($pagesize * ($page - 1)) . ',' . $pagesize;
    } elseif ($system['num']) {
        $pages = '';
        $sql_limit = "LIMIT {$system['num']}";
    }

    $system['order'] = $this->_set_order_field_prefix($system['order'], $field, 'my'); // 给排序字段加上表前缀
    $sql = "SELECT " .$system['field'] . ",mid FROM $sql_from " . ($sql_where ? "WHERE $sql_where" : "") . ($system['order'] == "null" || !$system['order'] ? "" : " ORDER BY {$system['order']}") . " $sql_limit";
}

$data = $this->_query($sql, $system);

// 缓存查询结果
if (is_array($data) && $data) {
    // 模块表的系统字段
    $fields['inputtime'] = ['fieldtype' => 'Date'];
    $fields['updatetime'] = ['fieldtype' => 'Date'];
    // 格式化显示自定义字段内容
    $dfield = \Phpcmf\Service::L('Field')->app($m);
    foreach ($data as $i => $t) {
        $t['url'] = dr_url_rel(dr_url_prefix($t['url'], $t['mid'], $system['site'], $this->_is_mobile));
        $data[$i] = $dfield->format_value($fields, $t, 1);
    }
}

// 存储缓存
$system['cache'] && $this->_save_cache_data($cache_name, [
    'data' => $data,
    'sql' => $sql,
    'total' => $total,
    'pages' => $pages,
    'pagesize' => $pagesize,
    'page_used' => $this->_page_used,
    'page_urlrule' => $this->_page_urlrule,
], $system['cache']);

$return_data = $this->_return($system['return'], $data, $sql, $total, $pages, $pagesize);