<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 02:02:03
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */
namespace app\admin\controller;
use think\facade\Db;
use app\admin\model\Auth as AuthModel;
use app\common\service\Node as NodeService;

/**
 * 角色管理
 * Class Auth
 * @package  app\ac\controller
 */
class Auth extends Base
{
    /**
     * 当前操作数据库
     * @var string
     */
    protected $table = 'system_auth';
	/**
	 * 列表
     * @menu true
     * @auth true
     */
    public function index($map=[])
    {
        $this->title = '角色列表';
        $this->list = (new AuthModel)->getList();
        return $this->fetch();
    }


    /**
     * 添加
     * @auth true
     */
    public function add(){
        if($this->request->isGet()){
            return $this->fetch('form');
        }else{
            $model = new AuthModel;
            if ($model->add($this->request->post())) {
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
            $this->vo = AuthModel::detail(input('auth_id'));
            return $this->fetch('form');
        }else{
            $model = new AuthModel;
            if ($model->edit($this->request->post())) {
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
        $this->_save($this->table, 'auth_id',['status' => '10']);
    }

    /**
     * 禁用
     * @auth true
     */
    public function forbid()
    {
        $this->_save($this->table, 'auth_id',['status' => '20']);
    }

    /**
     * 删除
     * @auth true
     */
    public function remove()
    {
        $this->_delete($this->table,'auth_id');
    }


    /**
     * 权限配置节点
     * @auth true
     */
    public function apply()
    {
        $this->title = '权限配置节点';
        $auth =input('auth_id');
        switch (strtolower($this->request->post('action'))) {
            case 'get': // 获取权限配置
                $checks = Db::name('SystemAuthNode')->where(['auth' => $auth])->column('node');
				$this->success('获取权限节点成功！',null,NodeService::getAuthTree($checks));
            case 'save': // 保存权限配置
                list($post, $data) = [$this->request->post(), []];
                foreach (isset($post['nodes']) ? $post['nodes'] : [] as $node) {
                    $data[] = ['auth' => $auth, 'node' => $node];
                }
                Db::name('SystemAuthNode')->where(['auth' => $auth])->delete();
                Db::name('SystemAuthNode')->insertAll($data);
                NodeService::applyUserAuth();
                return $this->success('权限授权更新成功！');
            default:
	            $this->vo = AuthModel::detail($auth);
	        	return $this->fetch('apply');
        }
    }


    /**
     * 刷新
     */
    public function refresh()
    {
        try {
            NodeService::applyUserAuth(true);
            $this->success('刷新系统授权成功！');
        } catch (\think\exception\HttpResponseException $exception) {
            throw  $exception;
        } catch (\Exception $e) {
            $this->error("刷新系统授权失败<br>{$e->getMessage()}");
        }
    }
}