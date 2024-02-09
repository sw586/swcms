<?php
// 模块的相关文章

if (!$param['tag']) {
    $return_data = $this->_return($system['return'], '没有传入tag参数的内容'); // 没有查询到内容
    return;
}

$sql = [];
$array = explode(',', urldecode($param['tag']));
$tfield = 'keywords';
if (isset($param['tfield']) && $param['tfield']) {
    $tfield = $param['tfield'];
    unset($param['tfield']);
}

foreach ($array as $name) {
    if ($name) {
        $cfile = WRITEPATH.'tags/index_'.SITE_ID.'/'.md5($name).'.php';
        if (is_file($cfile)) {
            $tid = (int)file_get_contents($cfile);
            $sql[] = '`'.XR_M()->dbprefix($system['site'].'_{xunruicms_mid}').'`.id in (select cid from `'.XR_M()->dbprefix($system['site'].'_tag_{xunruicms_mid}').'` where tid='.$tid.')';
        } else {
            $sql[] = '(`title` LIKE "%'.dr_safe_replace($name).'%" OR `'.$tfield.'` LIKE "%'.dr_safe_replace($name).'%")';
        }

    }
}
$sql && $where['my_related'] = [
    'adj' => 'SQL',
    'value' => '('.implode(' OR ', $sql).')'
];
unset($param['tag']);
if (isset($where['tag'])) {
    unset($where['tag']);
}
// 跳转到module方法
if (strpos($system['module'], ',') || $system['module'] == 'all') {
    if (!$system['field']) {
        $system['field'] = 'id,title,url,'.$tfield;
    } elseif (strpos($system['field'], $tfield) === false) {
        $system['field'] = trim($system['field'], ',');
        $system['field'].= ','.$tfield;
    }
    require 'Modules.php';
} else {
    if (isset($where['my_related']) && $where['my_related']) {
        $where['my_related']['value'] = str_replace('{xunruicms_mid}', $system['module'], $where['my_related']['value']);
    }
    require 'Module.php';
}