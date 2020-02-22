<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 01:45:37
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */
namespace app\admin\model;
use app\common\model\Icon as IconModel;
/**
 * 图标模型
 * Class Icon
 * @package app\admin\model
 */
class Icon extends IconModel
{
    /**
     * 新增
     */
    public function add($title)
    {
        return $this->save(compact('title'));
    }
    /**
     * 修改
     */
    public function edit($data)
    {
        return $this->update($data);
    }
}
