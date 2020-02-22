<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 01:32:17
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */
namespace app\common\model;
/**
 * 图标模型
 * Class Menu
 * @package app\common\model
 */
class Icon extends Base
{
	protected $name = 'system_icon';


    protected $pk = 'icon_id';

    public static function getList(){
        $model = new static;
        $select = $model->order('icon_id desc')->select();
        return $select;
    }
}
