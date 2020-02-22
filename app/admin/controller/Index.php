<?php

/**
 * @Author: 小明
 * @Date:   2020-02-21 18:44:07
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */
namespace app\admin\controller;
class Index extends Base
{
    public function index()
    {
        return $this->fetch();
    }

    public function main(){
    	$this->fetch();
    }
}