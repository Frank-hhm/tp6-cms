<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 02:04:11
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */

namespace app\common\model;
/**
 * 角色模型
 * Class Auth
 * @package app\common\model
 */
class Auth extends Base
{
    protected $name = 'system_auth';
    protected $pk = 'auth_id';


	protected $pkVal = [
		'title',
		'status',
		'sort',
		'desc'
	];

	protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = false;
    /**
     * 获取列表数据
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        // 查询列表数据
        return $this->order(['create_at' => 'desc'])
            ->paginate(intval(request()->get('limit', cookie('page-limit'))), false, [
                'query' => request()->request()
            ]);
    }


    /**
     * 详情
     * @param $staff_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($staff_id)
    {
        return self::find($staff_id);
    }

    /**
     * 添加
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        return $this->allowField($this->pkVal)->save($data);
    }

    /**
     * 修改
     * @param $data
     * @return false|int
     */
    public function edit($data)
    {
        return $this->allowField($this->pkVal)->update($data);
    }

}
