<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 16:36:11
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */

namespace app\common\service;

use think\facade\Db;
use think\facade\App;
use think\facade\Cache;
use think\facade\Request;
use think\facade\Filesystem;
use think\exception\ValidateException;
use app\common\model\Files as FilesModel;

/**
 * 文件上传
 * Class FileUpload
 * @package app\common\service
 */
class FileUpload 
{
	

	public static function upload($type='image'){
		try {
			$file_name = $_FILES['file']['name']|'';
        	$file = request()->file('file');
        	switch ($type) {
        		case 'image':
        			$v = \think\facade\Validate::rule('img','fileSize:2097152|fileExt:jpg,png,gif,jpge,bmp|fileMime:image/gif,image/jpeg,image/bmp,image/png');
					$path = 'images';
					if (!$v->check(['img'=>$file])) {
					    return $v->getError();
					}
					$file_type =  substr($file->getMime(),0,strrpos($file->getMime(),"/"));
        			break;
        		default:
					$file_type = 'file';
					$path = 'files';
        			break;
        	}
	        $path  = Filesystem::disk('public')->putFile($path, $file,'uniqid');
	        $url 	= Filesystem::getDiskConfig('public', 'url') . '/' . str_replace('\\', '/', $path);
			
		    $info = [
	    		'storage'	=>static::setStorage(),
			    'file_path' => $path,
			    'file_url'  => $url,
			    'file_size' => $file->getSize(),
			    'file_name' => $file_name,
			    'file_type' => $file_type,
			    'extension' => substr($file->getMime(),strripos($file->getMime(),"/")+1),
			    'file_domain'=>base_url()
			];
		    $model = new FilesModel();
		    if($model->add($info)){
		    	$info['file_id'] =  (int)$model->file_id;
		    	$info['file_url'] = base_url().$info['file_url'];
		    	return  $info;
		    }
		    return $model->getError();

	    } catch (think\exception\ValidateException $e) {
		    return json([
		        'code' => 1,
		        'msg'  => $e->getMessage(),
		    ]);
	    }
	}


	/**
	 * 获取上传存储方式
	 */
	public static function setStorage(){
		return 'local';
	}

}