<?php
/**
 * @Author: 小明
 * @Date:   2020-02-22 00:54:20
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */
namespace app\common\model;
/**
 * 菜单模型
 * Class Menu
 * @package app\common\model
 */
class Menu extends Base
{
    protected $name = 'system_menu';

    protected $pk = 'menu_id';


    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';

    /**
     * 所有
     * @return mixed
     */
    public static function getALL($where=[])
    {
        $model = new static;
        return $model->where($where)->order(['sort' => 'desc', 'create_at' => 'desc'])->select()->ToArray();
    }
}