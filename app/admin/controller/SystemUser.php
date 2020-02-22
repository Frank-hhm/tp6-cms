<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 17:03:44
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */

namespace app\admin\controller;
use app\admin\model\SystemUser as SystemUserModel;
use app\admin\model\Auth as AuthModel;
use app\common\service\Node as NodeService;

/**
 * 管理员管理
 * Class Icon
 * @package app\admin\controller
 */
class SystemUser extends Base
{
    protected $table = 'system_user';

    /**
	 * 列表
     * @auth true
     * @menu true
     */
    public function index($map=[])
    {
        $this->title = '管理员列表';
        $this->list = (new SystemUserModel)->getList($this->request->param());
        return $this->fetch();
    }
    /**
     * 添加
     * @auth true
     */
    public function add(){
        if($this->request->isGet()){
            $this->authorizes = AuthModel::getSelect();
            return $this->fetch('form');
        }else{
            $data = $this->request->post();
            // 刷新系统授权
            NodeService::applyUserAuth();
            // 用户权限处理
            $data['authorize'] = (isset($data['authorize']) && is_array($data['authorize'])) ? join(',', $data['authorize']) : '';
            $model = new SystemUserModel;
            if ($model->add($data)) {
                return $this->success('添加成功');
            }
            return $this->error('添加失败');
        }
    }
    /**
     * 修改
     * @auth true
     */
    public function edit()
    {
        if ($this->request->isGet()) {
            $this->vo = SystemUserModel::detail(input('user_id'));
            $this->authorizes = AuthModel::getSelect();
            return $this->fetch('form');
        }else{
            $data = $this->request->post();
            // 刷新系统授权
            NodeService::applyUserAuth();
            // 用户权限处理
            $data['authorize'] = (isset($data['authorize']) && is_array($data['authorize'])) ? join(',', $data['authorize']) : '';
            $model = new SystemUserModel;
            if ($model->edit($data)) {
                return $this->success('修改成功');
            }
            return $this->error('修改失败');
        }
    }

    /**
     * 开启
     * @auth true
     */
    public function resume()
    {
        $this->_save($this->table, 'user_id',['status' => '10']);
    }

    /**
     * 禁用
     * @auth true
     */
    public function forbid()
    {
        $this->_save($this->table, 'user_id',['status' => '20']);
    }

    /**
     * 删除
     * @auth true
     */
    public function remove()
    {
        $this->_delete($this->table,'user_id');
    }

	public function pass(){
		if ($this->request->isGet()) {
			$this->user_id = $this->request->get('user_id');
            $this->fetch();
        } else {
            $post = $this->request->post();
            if ($post['password'] !== $post['repassword']) {
                $this->error('两次输入的密码不一致！');
            }
            if ($this->_save($this->table,'user_id',['user_id' => $post['user_id'], 'password' => md5($post['password'])])) {
                $this->success('密码修改成功，下次请使用新密码登录！', '');
            } else {
                $this->error('密码修改失败，请稍候再试！');
            }
        }
	}
}