<?php namespace Phpcmf\Field;
/**
 * www.xunruicms.com
 * 迅睿内容管理框架系统（简称：迅睿CMS）
 * 本文件是框架系统文件，二次开发时不可以修改本文件，可以通过继承类方法来重写此文件
 **/

class Catids extends \Phpcmf\Library\A_Field {

	/**
     * 构造函数
     */
    public function __construct(...$params) {
        parent::__construct(...$params);
        $this->fieldtype = ['TEXT' => ''];
        $this->defaulttype = 'TEXT';
    }

	/**
	 * 字段相关属性参数
	 *
	 * @param	array	$value	值
	 * @return  string
	 */
	public function option($option) {


		return ['
                <div class="form-group">
                  	<label class="col-md-2 control-label">'.dr_lang('重要提醒').'</label>
                    <div class="col-md-9"><label class="form-control-static">本字段名一定要是catids才能参与搜索</label></div>
                </div>
                 <div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('折叠显示到一行').'</label>
                    <div class="col-md-9">
                        <div class="mt-radio-inline">
                         <label class="mt-radio mt-radio-outline"><input type="radio" value="0" name="data[setting][option][collapse]" '.($option['collapse'] == 0 ? 'checked' : '').' > '.dr_lang('开启').' <span></span></label>
                        &nbsp; &nbsp;
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="1" name="data[setting][option][collapse]" '.($option['collapse'] == 1 ? 'checked' : '').' > '.dr_lang('关闭').' <span></span></label>
                             
                            </div>
						<span class="help-block">'.dr_lang('多选模式下是否折叠显示栏目').'</span>
                    </div>
                </div>
				', '<div class="form-group">
			<label class="col-md-2 control-label">'.dr_lang('控件宽度').'</label>
			<div class="col-md-9">
				<label><input type="text" class="form-control" size="10" name="data[setting][option][width]" value="'.$option['width'].'"></label>
				<span class="help-block">'.dr_lang('[整数]表示固定宽度；[整数%]表示百分比').'</span>
			</div>
		</div>'];
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
        $save = [];
        $data = \Phpcmf\Service::L('Field')->post[$field['fieldname']];
        if ($data) {
            $data = dr_string2array($data);
            if (!IS_ADMIN) {
                // 验证发布权限
                $category = \Phpcmf\Service::C()->_get_module_member_category(\Phpcmf\Service::C()->module, 'add');
                if (!$category) {
                    \Phpcmf\Service::C()->_json(1, dr_lang('模块[%s]没有可用栏目权限', \Phpcmf\Service::C()->module['dirname']));
                }
                foreach ($data as $t) {
                    if ($t) {
                        $save[] = $t;
                        if (!$category[$t]) {
                            \Phpcmf\Service::C()->_json(1, dr_lang('模块[%s]没有栏目(%s)权限', \Phpcmf\Service::C()->module['dirname'], $t));
                        }
                    }
                }
            } else {
                foreach ($data as $t) {
                    if ($t) {
                        $save[] = $t;
                    }
                }
            }
            $save = array_unique($save);
        }
        \Phpcmf\Service::L('Field')->data[$field['ismain']][$field['fieldname']] = dr_array2string($save);
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
                \Phpcmf\Service::C()->module['dirname'],
                dr_string2array($value),
                ' name=\'data['.$field['fieldname'].'][]\'  multiple="multiple" data-actions-box="true"',
                '', isset(\Phpcmf\Service::C()->module['setting']['pcatpost']) && \Phpcmf\Service::C()->module['setting']['pcatpost'] ? 0 : 1, 1
            );
        $str.= '<span class="help-block">'.$tips.'</span>';
        if ($field['setting']['option']['collapse']) {
            $str = str_replace('collapseTags: true,', 'collapseTags: false,', $str);
        }

        if (strpos($str, 'layCascader') === false) {
            $str =  '<label style="min-width: 200px">'.$str.'</label>';
        }

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


        return $this->input_format($field['fieldname'], $field['name'], '<div class="form-control-static">'.dr_linkagepos($field['setting']['option']['linkage'], $value, ' - ').'</div>');
    }

}