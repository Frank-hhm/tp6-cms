<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 00:55:34
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */

namespace app\common\model;

use think\Model;
use think\facade\Request;
use think\facade\Session;

/**
 * 模型基类
 * Class Base
 * @package app\common\model
 */
class Base extends Model
{
    public static $storeId;
    public static $userId;
    public static $base_url;

    protected $alias = '';

    /**
     * 模型基类初始化
     */
    public static function init()
    {
        parent::init();
    }

    /**
     * 获取当前调用的模块名称
     * 例如：admin, api, store, task
     * @return string|bool
     */
    protected static function getCalledModule()
    {
        if (preg_match('/app\\\(\w+)/', get_called_class(), $class)) {
            return $class[1];
        }
        return false;
    }
}
