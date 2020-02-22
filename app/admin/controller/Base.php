<?php

/**
 * @Author: 小明
 * @Date:   2020-02-21 18:43:52
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */
namespace app\admin\controller;
use think\App;
use think\facade\Db;
use think\facade\Cache;
use think\facade\View;
use app\BaseController;
use app\common\service\Node as NodeService;
/**
 * 控制器基础类
 */
class Base extends BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    // 初始化
    protected function initialize()
    {
        if( !session('admin') ) $this->error('请先登录!',url('login/index'),[],'html');
        $this->menus = NodeService::getMenuNodeTree();
    }
}
