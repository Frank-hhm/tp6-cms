<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 17:04:12
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */

namespace app\common\model;
use SoftDelete;
/**
 * 管理员模型
 * Class Menu
 * @package app\common\model
 */
class SystemUser extends Base
{
    protected $name = 'SystemUser';

    protected $pk = 'user_id';



	protected $pkVal = [
		'username',
		'password',
		'qq',
		'email',
		'phone',
		'authorize',
		'desc',
		'status'
	];

	protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = false;

     /**
     * 权限
     * @param $value
     * @return array
     */
    public function getAuthorizeAttr($value)
    {
        return !empty($value)?explode(',',$value):[];
    }

    /**
     * 获取用户信息
     * @param $where
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($where)
    {
        $filter = [];
        if (is_array($where)) {
            $filter = array_merge($filter, $where);
        } else {
            $filter['user_id'] = (int)$where;
        }
        return static::where($filter)->find();
    }



    /**
     * 获取数据
     */
    public function getList($query)
    {
        $that = $this->where(['delete_at'=>0]);
    	if(!empty($query)){
    		if (isset($query['search']) && !empty($query['search'])) {
	            $that->where('username|phone|email|qq', 'like', '%' . trim($query['search']) . '%');
	        }
	        if (isset($query['start_time']) && !empty($query['start_time'])) {
	            $that->whereTime('create_at', '>=', $query['start_time']);
	        }
	        if (isset($query['end_time']) && !empty($query['end_time'])) {
	            $that->whereTime('create_at', '<', $query['end_time']);
	        }
    	}
        // 查询列表数据
        return $that->order(['create_at'=>'desc'])
            ->paginate(intval(request()->get('limit', cookie('page-limit'))), false, [
                'query' => request()->request()
            ]);
    }


    /**
     * 添加
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        if(!empty($data['password'])) $data['password'] = md5($data['password']);
        return $this->allowField($this->pkVal)->save($data);
    }

    /**
     * 修改
     * @param $data
     * @return false|int
     */
    public function edit($data)
    {
        if(!empty($data['password'])) $data['password'] = md5($data['password']);
        return $this->allowField($this->pkVal)->update($data);
    }
}