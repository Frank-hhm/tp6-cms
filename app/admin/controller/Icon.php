<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 01:40:01
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */

namespace app\admin\controller;
use app\admin\model\Icon as IconModel;
use think\facade\View;

/**
 * 图标库管理
 * Class Icon
 * @package app\admin\controller
 */
class Icon extends Base
{

    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'icon';
    /**
     * 列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title='矢量图标库';
        $this->list= (new IconModel())->getList();
        $this->fetch();
    }

    /**
     * 添加
     * @auth true
     */
    public function add()
    {
       	if ($this->request->isGet()) {
        	$this->fetch('form');
	    }else{
	    	$model = new IconModel;
	        if ($model->add('ui '.$this->request->post('title'))) {
	            $this->success('添加成功');
	        }
	        $this->error('添加失败');
    	}
    }

    /**
     * 编辑
     * @auth true
     */
    public function edit()
    {
    	if ($this->request->isGet()) {
            $this->vo = IconModel::find($this->request->get('icon_id'));
        	$this->fetch('form');
        }else{
            $model = new IconModel;
            if ($model->edit($this->request->post())) {
                $this->success('修改成功');
            }
            $this->error('修改失败');
        }
    }

    /**
     * 删除
     * @auth true
     */
    public function remove()
    {
        $this->_delete($this->table,'icon_id');
    }

}

