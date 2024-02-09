<?php
// 栏目搜索字段筛选

$catid = $system['catid'];
$module = \Phpcmf\Service::L('cache')->get('module-'.$system['site'].'-'.$dirname);
if (!$module) {
    return $this->_return($system['return'], '模块('.$dirname.')未安装');
} elseif (!$catid) {
    return $this->_return($system['return'], '没有catid值无法显示结果');
}

$cat = dr_cat_value($system['site'], $module['mid'], $catid);
if (!$cat) {
    return $this->_return($system['return'], '模块('.$dirname.')的栏目（'.$catid.'）不存在');
} elseif (dr_count($cat['field']) == 0) {
    return $this->_return($system['return'], '模块('.$dirname.')的栏目（'.$catid.'）没有分配模型字段');
}

$return = [];

foreach ($cat['field'] as $field) {
    $t = $module['category_data_field'][$field];
    if ($t) {
        $data = dr_format_option_array($t['setting']['option']['options']);
        if ($t['issearch'] && $t['ismain']
            && in_array($t['fieldtype'], ['Select', 'Selects', 'Radio', 'Checkbox']) && $data) {
            $list = [];
            foreach ($data as $value => $name) {
                $name && !is_null($value) && $list[] = array(
                    'name' => trim($name),
                    'value' => trim($value)
                );
            }
            $list && $return[] = array(
                'data' => $list,
                'name' => $t['name'],
                'field' => $t['fieldname'],
                'displayorder' => $t['displayorder'],
            );
        }
    }
}

$system['order'] && $return = dr_array_sort($return, 'displayorder');

return $this->_return($system['return'], $return, '');