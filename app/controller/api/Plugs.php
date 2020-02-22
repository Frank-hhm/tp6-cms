<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 01:30:45
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */
namespace app\controller\api;
use app\BaseController;
use think\facade\Cache;
use think\facade\Db;
use think\facade\View;
use think\facade\Filesystem;
use app\admin\model\FilesGroup as FilesGroupModel;
use app\admin\model\Files as FilesModel;
use app\common\service\FileUpload as FileUploadService;
use app\common\model\Icon as IconModel;


/**
 * 插件管理
 * Class Plugs
 * @package app\admin\controller\api
 */
class Plugs extends BaseController
{

    /**
     * 图标选择器
     */
    public function icon()
    {
        View::assign('title','图标选择器');
        View::assign('field',input('field', 'icon'));
        View::config(['view_path' => root_path().'view/']);
        View::assign('list', (new IconModel())->getList());
        return View::fetch('common/icon');
    }
    /**
     * 获取文件上传参数
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function files($field  = 'image',$fileId  = '', $group_id = -1 ,$multiple = 'false')
    {
        // 分组列表
        $group_list = (new FilesGroupModel)->getALL();
        // 文件列表
        $file_list = (new FilesModel)->getlist(intval($group_id), 0)->ToArray();
        $view=[
            'title'=>'图标选择器',
            'field'=>input('field',$field),
            'fileId'=>input('fileId',$fileId),
            'multiple'=>input('multiple',$multiple),
        ];
        View::assign($view);
        View::config(['view_path' => root_path().'view/']);
        return View::fetch('common/files');
    }

    public function upload(){
        if(FileUploadService::upload()){
             return $this->success('上传成功');
        }
        return $this->error('上传失败');
    }
}