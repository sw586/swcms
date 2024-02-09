<?php
// 模块内容详细页面

$module = \Phpcmf\Service::L('cache')->get('module-'.$system['site'].'-'.$dirname);
if (!$module) {
    return $this->_return($system['return'], "模块({$dirname})未安装");
} elseif (!$param['id']) {
    return $this->_return($system['return'], "模块({$dirname})缺少id参数");
}

$tableinfo = \Phpcmf\Service::L('cache')->get('table-'.$system['site']);
if (!$tableinfo) {
    // 没有表结构缓存时返回空
    return $this->_return($system['return'], '表结构缓存不存在');
}

$table = \Phpcmf\Service::M()->dbprefix(dr_module_table_prefix($module['dirname'], $system['site'])); // 模块主表`
if (!isset($tableinfo[$table])) {
    return $this->_return($system['return'], '表（'.$table.'）结构缓存不存在');
}

// 初始化数据表
$db = \Phpcmf\Service::M('Content', $dirname);
$db->_init($dirname, $system['site']);
if ($module['category_data_field']) {
    $system['more'] = 1;
}
$data = $db->get_data(intval($param['id']), 0, [], $system['more']);

// 缓存查询结果
if (is_array($data) && $data) {
    // 模块表的系统字段
    $fields = $module['field']; // 主表的字段
    if ($module['category_data_field']) {
        $fields = dr_array2array($fields, $module['category_data_field']);
    }
    $fields['inputtime'] = array('fieldtype' => 'Date');
    $fields['updatetime'] = array('fieldtype' => 'Date');
    // 格式化显示自定义字段内容
    $dfield = \Phpcmf\Service::L('Field')->app($module['dirname']);
    $data['url'] = dr_url_rel(dr_url_prefix($data['url'], $dirname, $system['site'], $this->_is_mobile));
    $data = $dfield->format_value($fields, $data, 1);

    // 存储缓存
    $system['cache'] && $this->_save_cache_data($cache_name, [
        'data' => [$data],
        'sql' => '',
        'total' => 0,
        'pages' => 0,
        'pagesize' => 0,
        'page_used' => $this->_page_used,
        'page_urlrule' => $this->_page_urlrule,
    ], $system['cache']);

}
return $this->_return($system['return'], [$data]);