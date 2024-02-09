<?php
// 模块的搜索

$this->_is_list_search = 1;

$total = (int)$system['total'];
unset($system['total']);

// 没有数据时返回空
if (!$total) {
    return $this->_return($system['return'], 'total参数为空', '', 0);
} elseif (!$dirname) {
    return $this->_return($system['return'], 'module参数不能为空');
} elseif (!$param['id']) {
    return $this->_return($system['return'], 'id参数为空', '', 0);
}

$module = \Phpcmf\Service::L('cache')->get('module-'.$system['site'].'-'.$dirname);
if (!$module) {
    return $this->_return($system['return'], '模块('.$dirname.')未安装');
}

$tableinfo = \Phpcmf\Service::L('cache')->get('table-'.$system['site']);
// 没有表结构缓存时返回空
if (!$tableinfo) {
    return $this->_return($system['return'], '表结构缓存不存在');
}

$table = \Phpcmf\Service::M()->dbprefix(dr_module_table_prefix($module['dirname'], $system['site'])); // 模块主表`
if (!isset($tableinfo[$table])) {
    return $this->_return($system['return'], '表（'.$table.'）结构缓存不存在');
}

// 分表储存
if ($module['is_ctable'] && $system['catid']) {
    $tmp = $tableinfo[$table];
    $table = dr_module_ctable($table, dr_cat_value($system['site'], $module['mid'], $system['catid']));
    $tableinfo[$table] = $tmp;
}

if ($where) {
    foreach ($where as $i => $t) {
        if ($t['name'] == 'id') {
            unset($where[$i]);
        }
    }
}

$index = \Phpcmf\Service::L('cache')->get_data('module-search-'.$dirname.'-'.$param['id']);
if (!$index) {
    $index = $this->_query('SELECT `params` FROM `'.$table.'_search` WHERE `id`="'.$param['id'].'"', $system, 0);
    if ($index) {
        $p = dr_string2array($index['params']);
        $index['sql'] = $p['sql'];
        $index['where'] = $p['where'];
    } else {
        return $this->_return($system['return'], '没有搜索结果', '', 0);
    }
}

if (isset($index['where']) && $index['where']) {
    $where[] = [
        'adj' => 'SQL',
        'value' => $index['where']
    ];
} elseif (isset($index['sql']) && $index['sql']) {
    if (isset($index['params']['order'])) {
        unset($index['params']['order']);
    }
    if ($index['params']) {
        $where[] = [
            'adj' => 'SQL',
            'value' => '(`'.$table.'`.`id` IN('.$index['sql'].'))'
        ];
    }
} else {
    return $this->_return($system['return'], '没有查询到内容', $index['sql'], 0);
}

unset($param['id']);

// 排序操作
if (!$system['order'] && isset($where['id']) && $where['id']['adj'] == 'IN' && $where['id']['value']) {
    // 按id序列来排序
    $system['order'] = strlen($where['id']['value']) < 10000 && $where['id']['value'] ? 'instr("'.$where['id']['value'].'", `'.$table.'`.`id`)' : 'NULL';
} else {
    // 默认排序参数
    !$system['order'] && ($system['order'] = $system['flag'] ? 'updatetime_desc' : ($action == 'hits' ? 'hits' : 'updatetime'));
}

$fields = $module['field']; // 主表的字段
$_order = [$table => $tableinfo[$table]];
$sql_from = '`'.$table.'`';
$sql_where = $this->_get_where($where); // sql的where子句

// 关联栏目模型表
if ($system['more'] && isset($module['category_data_field']) && $module['category_data_field']) {
    $fields = array_merge($fields, $module['category_data_field']);
    $table_more = $table.'_category_data'; // 栏目模型表
    $sql_from.= " LEFT JOIN $table_more ON `$table_more`.`id`=`$table`.`id`"; // sql的from子句
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
}

$system['order'] = $this->_set_orders_field_prefix($system['order'], $_order); // 给排序字段加上表前缀

// 分页处理
$page = $this->_get_page_id($system['page']);
$pagesize = (int)$system['pagesize'];
!$pagesize && $pagesize = 10;
if ($module['setting']['search']['max'] && $page * $pagesize > $module['setting']['search']['max']) {
    $return_data = $this->_return($system['return'], '搜索设置最大显示'.$module['setting']['search']['max'].'条，当前（'.($page * $pagesize).'）已超出', '', 0);
    return;
}
isset($index['params']) && $index['params'] && $system['firsturl'] = dr_module_search_url($index['params']);
$pages = $this->_new_pagination($system, $pagesize, $total);
$sql_limit = 'LIMIT ' . intval($pagesize * ($page - 1)) . ',' . $pagesize;

// 查询结果
$sql = "SELECT " .$this->_get_select_field($system['field'] ? $system['field'] : '*') . " FROM $sql_from " . ($sql_where ? "WHERE $sql_where" : "") . ($system['order'] == "null" || !$system['order'] ? "" : " ORDER BY {$system['order']}") . " $sql_limit";
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

return $this->_return($system['return'], $data, $sql, $total, $pages, $pagesize);