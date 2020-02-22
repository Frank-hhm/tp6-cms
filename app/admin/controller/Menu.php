<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 00:53:07
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */
namespace app\admin\controller;
use app\common\service\Node as NodeService;
use app\admin\model\Menu as MenuModel;
/**
 * 菜单管理
 * Class Icon
 * @package app\admin\controller
 */
class Menu extends Base
{
    /**
     * 当前操作数据库
     * @var string
     */
    protected $table = 'system_menu';

	/**
     * 列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $wheres=[];
        if (!empty($this->request->get("module"))) {
            $wheres["module"]=$this->request->get("module");
        }
    	$data = MenuModel::getALL($wheres);
        foreach ($data as &$vo) {
            if ($vo['url'] !== '#') {
                $vo['url'] = url($vo['url']) . (empty($vo['params']) ? '' : "?{$vo['params']}");
            }
            $vo['ids'] = join(',', getArrSubIds($data, $vo['menu_id'],'menu_id'));
        }
        $this->title = '菜单管理';
        $this->list = arr2table($data,'menu_id');
        $this->fetch();
    }

    /**
     * 添加
     * @auth true
     */
    public function add()
    {
    	if ($this->request->isGet()) {
            $menus = MenuModel::getALL();
            foreach ($select = arr2table($menus,'menu_id') as $key => &$menu) {
                if (substr_count($menu['path'], '-') > 3) unset($select[$key]); 
            }
            $this->select = $select;
           	$this->vo = [];
            // 选择自己的上级菜单;
            if (empty($data['pid']) && $this->request->get('pid', '0')) {
                $this->vo['pid'] = $this->request->get('pid', '0');
            }
            $this->nodes = NodeService::getMenuNodeList();
	        // 读取功能节点
        	$this->fetch('form');
	    }else{
	    	$model = new MenuModel;
	        if ($model->add($this->request->post())) {
	            $this->success('添加成功');
	        }
	        $this->error('添加失败');
    	}
    }
    /**
     * 修改
     * @auth true
     */
    public function edit()
    {
        if ($this->request->isGet()) {
            $this->vo = MenuModel::find($this->request->get('menu_id'));
            $menus = MenuModel::getALL();
            foreach ($select = arr2table($menus,'menu_id') as $key => &$menu) {
                if (substr_count($menu['path'], '-') > 3) unset($select[$key]);
                
                elseif (isset($this->vo['pid']) && $this->vo['pid'] !== '' && $cur = "-{$this->vo['pid']}-{$this->vo['menu_id']}") {
                    if (stripos("{$menu['path']}-", "{$cur}-") !== false || $menu['path'] === $cur) unset($select[$key]); # 移除与自己相关联的菜单
                }
            }
            // 选择自己的上级菜单;
            if (empty($this->vo['pid']) && $this->request->get('pid', '0')) {
                $this->vo['pid'] = $this->request->get('pid', '0');
            }
            $this->select = $select;
            $this->nodes =NodeService::getMenuNodeList();
            // 读取功能节点
        	$this->fetch('form');
        }else{
            $model = new MenuModel;
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
        $this->_save($this->table, 'menu_id',['status' => '10']);
    }

    /**
     * 禁用
     * @auth true
     */
    public function forbid()
    {
        $this->_save($this->table, 'menu_id',['status' => '20']);
    }

    /**
     * 删除
     * @auth true
     */
    public function remove()
    {
        $this->_delete($this->table,'menu_id');
    }
}
