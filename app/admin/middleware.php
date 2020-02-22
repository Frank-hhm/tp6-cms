<?php

/**
 * @Author: 小明
 * @Date:   2020-02-21 18:58:39
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */

// 中间件定义文件
return [
	\app\admin\middleware\Sys::class,
	'think\middleware\SessionInit'
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    // 多语言加载
    // \think\middleware\LoadLangPack::class,
    // Session初始化
    // \think\middleware\SessionInit::class
];
