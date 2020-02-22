<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 00:00:42
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */

namespace app\admin\controller;
use think\facade\Request;
/**
 * 系统管理
 * Class Icon
 * @package app\admin\controller
 */
class System extends Base
{
    /**
     * 参数配置
     * @auth true
     * @menu true
     */
	public function config(){
        if (Request::isGet()) {
            $this->title = '系统参数配置';
            $this->fetch();
        } else {
            foreach (Request::post() as $key => $value) {
                sysconf($key, $value);
            }
            $this->success('系统参数配置成功！');
        }
	}
}