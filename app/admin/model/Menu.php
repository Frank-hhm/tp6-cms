<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 00:53:43
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */
namespace app\admin\model;
use app\common\model\Menu as MenuModel;
/**
 * 菜单模型
 * Class Menu
 * @package app\store\model
 */
class Menu extends MenuModel
{
    /**
     * 添加
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        return $this->save($data);
    }


    /**
     * 修改
     * @param $data
     * @return false|int
     */
    public function edit($data)
    {
		return $this->update($data);
    }
}