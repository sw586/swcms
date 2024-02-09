<?php
// 模块数据


// 通过栏目识别共享模块目录
if ((!$dirname || $dirname == 'share') && $system['catid']) {
    $cat = dr_share_cat_value($system['catid']);
    if ($cat && $cat['mid']) {
        $dirname = $cat['mid'];
        unset($cat);
    }
}

if (!$dirname) {
    $return_data = $this->_return($system['return'], '模块参数module未填写');
    return;
}

$module = \Phpcmf\Service::L('cache')->get('module-'.$system['site'].'-'.$dirname);
if (!$module) {
    if (strpos($system['module'], ',') || $system['module'] == 'all') {
        require 'Modules.php';
        return;
    }
    $return_data = $this->_return($system['return'], '模块('.$dirname.')未安装');
    return;
}


$tableinfo = \Phpcmf\Service::L('cache')->get('table-'.$system['site']);

// 没有表结构缓存时返回空
if (!$tableinfo) {
    $return_data = $this->_return($system['return'], '表结构缓存不存在');
    return;
}

$table = \Phpcmf\Service::M()->dbprefix(dr_module_table_prefix($module['dirname'], $system['site'])); // 模块主表`
if (!isset($tableinfo[$table])) {
    $return_data = $this->_return($system['return'], '表（'.$table.'）结构缓存不存在');
    return;
}

// 分表储存
if ($module['is_ctable'] && $system['catid']) {
    $tmp = $tableinfo[$table];
    $table = dr_module_ctable($table, dr_cat_value($system['site'], $module['mid'], $system['catid']));
    $tableinfo[$table] = $tmp;
}

// 加上状态判断
//$where[] = ['adj' => '', 'name' => 'status', 'value' => 9];

// 是否操作自定义where
if ($param['where']) {
    $where[] = [
        'adj' => 'SQL',
        'value' => urldecode($param['where'])
    ];
    unset($param['where']);
}

$fields = $module['field']; // 主表的字段

// 排序操作
if (!$system['order'] && isset($where['id']) && $where['id']['adj'] == 'IN' && $where['id']['value']) {
    // 按id序列来排序
    $system['order'] = strlen($where['id']['value']) < 10000 && $where['id']['value'] ? 'instr("'.$where['id']['value'].'", `'.$table.'`.`id`)' : 'NULL';
} else {
    // 默认排序参数
    !$system['order'] && ($system['order'] = $system['flag'] ? 'updatetime_desc' : ($action == 'hits' ? 'hits' : 'updatetime'));
}

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
    $fwhere && $where[] = [
        'adj' => 'SQL',
        'value' => urldecode(count($fwhere) == 1 ? $fwhere[0] : '('.implode(' OR ', $fwhere).')')
    ];
    unset($fwhere);
    unset($catids);
}

// 查找mwhere目录
$mwhere = \Phpcmf\Service::Mwhere_Apps();
if ($mwhere) {
    $mid = $dirname;
    $field = $tableinfo[$table];
    $siteid = $system['site'];
    foreach ($mwhere as $mapp) {
        $w = require dr_get_app_dir($mapp).'Config/Mwhere.php';
        if ($w) {
            $where[] = ['adj' => 'sql', 'value' => $w];
        }
    }
}

$where = $this->_set_where_field_prefix($where, $tableinfo[$table], $table, $fields); // 给条件字段加上表前缀
$system['field'] = $this->_set_select_field_prefix($system['field'], $tableinfo[$table], $table); // 给显示字段加上表前缀

// 多表组合排序
$_order = [];
$_order[$table] = $tableinfo[$table];

// sql的from子句
if ($action == 'hits') {
    // 分表储存
    $table2 = $table;
    if ($module['is_ctable'] && $system['catid']) {
        $table2 = \Phpcmf\Service::M()->dbprefix(dr_module_table_prefix($module['dirname'], $system['site'])); // 模块主表`
    }
    $sql_from = '`'.$table.'` LEFT JOIN `'.$table2.'_hits` ON `'.$table.'`.`id`=`'.$table2.'_hits`.`id`';
    $table_more = $table2.'_hits'; // hits表
    $system['field'] = $this->_set_select_field_prefix($system['field'], $tableinfo[$table_more], $table_more); // 给显示字段加上表前缀
    $_order[$table_more] = $tableinfo[$table_more];
    if (!$system['field']) {
        $system['field'] = '`'.$table.'`.*';
        $fields_more = \Phpcmf\Service::M()->db->getFieldNames($table_more);
        if ($fields_more) {
            foreach ($fields_more as $f) {
                if (!in_array($f, ['id', 'catid', 'uid'])) {
                    $system['field'].= ',`'.$table_more.'`.`'.$f.'`';
                }
            }
        }
    }
} else {
    $sql_from = '`'.$table.'`';
}

// 关联栏目模型表
if ($system['more']) {
    $table_more = $table.'_category_data'; // 栏目模型表
    if (isset($module['category_data_field']) && $module['category_data_field']) {
        $fields = array_merge($fields, $module['category_data_field']);
        $where = $this->_set_where_field_prefix($where, $tableinfo[$table_more], $table_more, $fields); // 给条件字段加上表前缀
        $system['field'] = $this->_set_select_field_prefix($system['field'], $tableinfo[$table_more], $table_more); // 给显示字段加上表前缀
        $_order[$table_more] = $tableinfo[$table_more];
    }
    $sql_from.= " LEFT JOIN $table_more ON `$table_more`.`id`=`$table`.`id`"; // sql的from子句
    if (!$system['field']) {
        $system['field'] = '`'.$table.'`.*';
        $fields_more = \Phpcmf\Service::M()->db->getFieldNames($table_more);
        if ($fields_more) {
            foreach ($fields_more as $f) {
                if (!in_array($f, ['id', 'catid', 'uid'])) {
                    $system['field'].= ',`'.$table_more.'`.`'.$f.'`';
                }
            }
        }
    }
}

// 关联表
if ($system['join'] && $system['on']) {
    $rt = $this->_join_table($table, $system, $where, $_order, $sql_from);
    if (!$rt['code']) {
        $return_data =  $this->_return($system['return'], $rt['msg']);
        return;
    }
    list($system, $where, $_order, $sql_from) = $rt['data'];
}

$sql_limit = $pages = '';
$sql_where = $this->_get_where($where); // sql的where子句

// 商品有效期
// isset($fields['order_etime']) && ($system['oot'] ? $sql_where.= ' AND `order_etime` BETWEEN 1 AND '.SYS_TIME : $sql_where.= ' AND NOT (`order_etime` BETWEEN 1 AND '.SYS_TIME.')');

// 推荐位调用
if ($system['flag']) {
    if ($system['show_flag']) {
        $sql_from.= ' LEFT JOIN `'.$table.'_flag'.'` ON `'.$table.'`.`id`=`'.$table.'_flag`.`id`';
        $sql_where = ($sql_where ? $sql_where.' AND' : '').' `'.$table.'_flag`.'.(strpos($system['flag'], ',') ? '`flag` IN ('.$this->_get_where_in($system['flag']).')' : '`flag`='.(int)$system['flag']);
    } else {
        $flag = "select `id` from `{$table}_flag` where ".(strpos($system['flag'], ',') ? '`flag` IN ('.$this->_get_where_in($system['flag']).')' : '`flag`='.(int)$system['flag']);
        $sql_where = ($sql_where ? $sql_where.' AND' : '')." `$table`.`id` IN (".$flag.")";
        unset($flag);
    }
}
// 排除推荐位
if ($system['not_flag']) {
    $flag = "select `id` from `{$table}_flag` where ".(strpos($system['not_flag'], ',') ? '`flag` IN ('.$this->_get_where_in($system['not_flag']).')' : '`flag`='.(int)$system['not_flag']);
    $sql_where = ($sql_where ? $sql_where.' AND' : '')." `$table`.`id` NOT IN (".$flag.")";
    unset($flag);
}

// groupid查询
if (isset($param['groupid']) && $param['groupid']) {

    if (strpos($param['groupid'], ',') !== false) {
        $gwhere = ' `'.$table.'`.`uid` in (select uid from `'.\Phpcmf\Service::M()->dbprefix('member').'_group_index` where `gid` in ('.dr_safe_replace($param['groupid']).'))';
    } elseif (strpos($param['groupid'], '-') !== false) {
        $arr = explode('-', $param['groupid']);
        $gwhere = [];
        foreach ($arr as $t) {
            $t = intval($t);
            $t && $gwhere[] = ' `'.$table.'`.`uid` in (select uid from `'.\Phpcmf\Service::M()->dbprefix('member').'_group_index` where `gid` = '. $t.')';
        }
        $gwhere = $gwhere ? '('.implode(' AND ', $gwhere).')' : '';
    } else {
        $gwhere = ' `'.$table.'`.`uid` in (select uid from `'.\Phpcmf\Service::M()->dbprefix('member').'_group_index` where `gid` = '. intval($param['groupid']).')';
    }
    $gwhere && $where['id'] = [
        'adj' => 'SQL',
        'name' => 'id',
        'value' => $gwhere
    ];
    unset($param['groupid']);
}

// 统计标签
if ($this->_return_sql) {
    $sql = "SELECT ".$this->_select_rt_name." FROM $sql_from ".($sql_where ? "WHERE $sql_where" : "")." ORDER BY NULL";
} else {
    $first_url = $system['firsturl'];
    if ($system['page']) {
        $page = $this->_get_page_id($system['page']);
        if ($system['catid'] && is_numeric($system['catid'])) {
            if (!$system['sbpage']) {
                if ($system['pagesize']) {
                    $this->_list_error[] = '存在catid参数和page参数时，pagesize参数将会无效';
                }
                if ($system['urlrule']) {
                    $this->_list_error[] = '存在catid参数和page参数时，urlrule参数将会无效';
                }
                if ($this->_is_mobile) {
                    $system['pagesize'] = (int)$cat['setting']['template']['mpagesize'];
                } else {
                    $system['pagesize'] = (int)$cat['setting']['template']['pagesize'];
                }
                //  防止栏目生成第一页问题
                if ($system['action'] == 'module') {
                    $first_url = dr_module_category_url($module, $cat);
                    if (!$this->_is_pc || SITE_ID > 1) {
                        $first_url = dr_url_prefix($first_url, $module['dirname']);
                    }
                }
                $system['urlrule'] = dr_module_category_url($module, $cat, '{page}');
                if (!$this->_is_pc || SITE_ID > 1) {
                    $system['urlrule'] = dr_url_prefix($system['urlrule'], $module['dirname']);
                }
            }
        }
        if ($system['num']) {
            $this->_list_error[] = '存在page参数时，num参数将会无效';
        }
        $pagesize = (int)$system['pagesize'];
        !$pagesize && $pagesize = 10;
        // 数据量
        $sql = "SELECT count(*) as c FROM $sql_from " . ($sql_where ? "WHERE $sql_where" : "") . " ORDER BY NULL";
        $row = $this->_query($sql, $system, FALSE);
        $total = (int)$row['c'];
        if ($system['maxlimit'] && $total > $system['maxlimit']) {
            $total = $system['maxlimit']; // 最大限制
            if ($page * $pagesize > $total) {
                $return_data = $this->_return($system['return'], 'maxlimit设置最大显示'.$system['maxlimit'].'条，当前（'.($page * $pagesize).'）已超出', $sql, 0);
                return;
            }
        }
        // 没有数据时返回空
        if (!$total) {
            $return_data = $this->_return($system['return'], '没有查询到内容', $sql, 0);
            return;
        }
        $system['firsturl'] = $first_url;
        $pages = $this->_new_pagination($system, $pagesize, $total);
        $sql_limit = 'LIMIT ' . intval($pagesize * ($page - 1)) . ',' . $pagesize;
    } elseif ($system['num']) {
        $pages = '';
        $sql_limit = "LIMIT {$system['num']}";
    }

    $system['order'] = $this->_set_orders_field_prefix($system['order'], $_order); // 给排序字段加上表前缀
    $sql = "SELECT " .$this->_get_select_field($system['field'] ? $system['field'] : '*') . " FROM $sql_from " . ($sql_where ? "WHERE $sql_where" : "") . ($system['order'] == "null" || !$system['order'] ? "" : " ORDER BY {$system['order']}") . " $sql_limit";
}

$data = $this->_query($sql, $system);

// 缓存查询结果
if (is_array($data) && $data) {
    // 模块表的系统字段
    $fields['inputtime'] = ['fieldtype' => 'Date'];
    $fields['updatetime'] = ['fieldtype' => 'Date'];
    // 格式化显示自定义字段内容
    $dfield = \Phpcmf\Service::L('Field')->app($module['dirname']);
    foreach ($data as $i => $t) {
        $t['url'] = dr_url_rel(dr_url_prefix($t['url'], $dirname, $system['site'], $this->_is_mobile));
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