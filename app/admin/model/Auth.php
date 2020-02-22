<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 02:03:45
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */
namespace app\admin\model;
use app\common\model\Auth as AuthModel;
/**
 * 角色模型
 * Class Auth
 * @package app\api\model
 */
class Auth extends AuthModel
{

	protected $hidden = [];
    /**
     * 获取数据
     */
    public static function getSelect()
    {
        // 查询列表数据
        return self::where(['status' => 10])->order('sort desc,auth_id desc')->select();
    }
    /**
     * 获取数据
     */
    public function getList()
    {
        // 查询列表数据
        return $this->order('create_at,desc')
            ->paginate(intval(request()->get('limit', cookie('page-limit'))), false, [
                'query' => request()->request()
            ]);
    }
}