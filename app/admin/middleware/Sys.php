<?php

/**
 * @Author: 小明
 * @Date:   2020-02-21 18:42:55
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */

declare (strict_types = 1);

namespace app\admin\middleware;
class Sys
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        return $next($request);
    }
}
