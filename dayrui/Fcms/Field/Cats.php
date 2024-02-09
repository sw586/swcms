<?php namespace Phpcmf\Field;
/**
 * www.xunruicms.com
 * 迅睿内容管理框架系统（简称：迅睿CMS）
 * 本文件是框架系统文件，二次开发时不可以修改本文件，可以通过继承类方法来重写此文件
 **/

class Cats extends \Phpcmf\Field\Cat {
	
	/**
     * 构造函数
     */
    public function __construct(...$params) {
        parent::__construct(...$params);
        $this->fieldtype = ['TEXT' => ''];
        $this->defaulttype = 'TEXT';
    }

	/**
	 * 字段输出
	 */
	public function output($value) {
        return dr_string2array($value);
	}

	/**
	 * 字段入库值
	 *
	 * @param	array	$field	字段信息
	 * @return  void
	 */
    public function insert_value($field) {
        $value = \Phpcmf\Service::L('Field')->post[$field['fieldname']];
        if (is_array($value)) {
            $value = dr_array2string($value);
        }
        \Phpcmf\Service::L('Field')->data[$field['ismain']][$field['fieldname']] = $value;
    }

	/**
	 * 字段表单输入
	 *
	 * @param	string	$field	字段数组
	 * @param	array	$value	值
	 * @return  string
	 */
	public function input($field, $value = null) {

		// 字段禁止修改时就返回显示字符串
		if ($this->_not_edit($field, $value)) {
			return $this->show($field, $value);
		}

		// 字段存储名称
		$name = $field['fieldname'];

		// 字段显示名称
		$text = ($field['setting']['validate']['required'] ? '<span class="required" aria-required="true"> * </span>' : '').dr_lang($field['name']);

		// 字段提示信息
		$tips = ($name == 'title' && APP_DIR) || $field['setting']['validate']['tips'] ? '<span class="help-block" id="dr_'.$field['fieldname'].'_tips">'.$field['setting']['validate']['tips'].'</span>' : '';

		// 开始输出
		$str = '';

        $str.= \Phpcmf\Service::L('category', 'module')->select(
                $field['setting']['option']['module'],
                dr_string2array($value),
                ' name=\'data['.$field['fieldname'].'][]\'  multiple="multiple" data-actions-box="true" '.(isset($field['setting']['option']['is_search']) && $field['setting']['option']['is_search'] ? ' data-live-search="true" ' : ''),
                '', (isset($field['setting']['option']['parent']) && $field['setting']['option']['parent'] ? 0 : 1), 0
            );
        if ($field['setting']['option']['collapse']) {
            $str = str_replace('collapseTags: true,', 'collapseTags: false,', $str);
        }
        if (strpos($str, 'layCascader') === false) {
            $str =  '<label style="min-width: 200px">'.$str.'</label>';
        }

        $str.= \Phpcmf\Service::L('Field')->get('select')->get_select_search_code().'<span class="help-block">'.$tips.'</span>';
		return $this->input_format($name, $text, $str);
	}

    /**
     * 字段表单显示
     *
     * @param	string	$field	字段数组
     * @param	array	$value	值
     * @return  string
     */
    public function show($field, $value = null) {

        $html = '';
        $value = dr_string2array($value);
        if ($value) {
            foreach ($value as $t) {
                $html.= '<label class="btn btn-xs default">'.dr_catpos($t, ' - ', false, '', $field['setting']['option']['module']).'</label>';
            }
        }

        return $this->input_format($field['fieldname'], $field['name'], '<div class="form-control-static">'.$html.'</div>');
    }

}