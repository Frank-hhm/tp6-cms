<?php

/**
 * @Author: 小明
 * @Date:   2020-02-21 18:45:40
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */
namespace app\admin\controller;
use app\BaseController;
use think\facade\Db;
use app\common\service\Node as NodeService;
/**
 * 登录类
 */
class Login extends BaseController
{
	
	public function index(){
		if ($this->request->isGet()) {
            // // 运行环境检查
            $this->devMode = in_array($this->request->rootDomain(), [ '0.1', 'localhost']);
            // 登录状态检查
            $this->loginskey = session('admin_loginskey','');
            if (empty($this->loginskey)) {
                $this->loginskey = uniqid();
                session('admin_loginskey', $this->loginskey);
            }
            return $this->fetch();
        }else{
            $data = $this->validate([
                'username' => $this->request->post('username'),
                'password' => $this->request->post('password'),
            ], [
                'username' => 'require|min:4',
                'password' => 'require|min:4',
            ], [
                'username.require' => '登录账号不能为空！',
                'password.require' => '登录密码不能为空！',
                'username.min'     => '登录账号长度不能少于4位有效字符！',
                'password.min'     => '登录密码长度不能少于4位有效字符！',
            ]);
            $map = ['delete_at' => '0', 'username' => $data['username']];
            $user = Db::name('SystemUser')->where($map)->find();
            if (empty($user)) $this->error('登录账号或密码错误，请重新输入!');
            if ($user['status'] == 20) $this->error('账号已经被禁用，请联系管理员!');
            // 账号锁定消息
            $cache = cache("user_login_{$user['username']}");
            if (is_array($cache) && !empty($cache['number']) && !empty($cache['time'])) {
                if ($cache['number'] >= 10 && ($diff = $cache['time'] + 3600 - time()) > 0) {
                    list($m, $s, $info) = [floor($diff / 60), floor($diff % 60), ''];
                    if ($m > 0) $info = "{$m} 分";
                    $this->error("<strong class='color-red'>抱歉，该账号已经被锁定！</strong><p class='nowrap'>连续 10 次登录错误，请 {$info} {$s} 秒后再登录！</p>");
                }
            }

            if (md5($user['password'] . session('admin_loginskey')) !== $data['password']) {
                if (empty($cache) || empty($cache['time']) || empty($cache['number']) || $cache['time'] + 3600 < time()) {
                    $cache = ['time' => time(), 'number' => 1, 'geoip' => $this->request->ip()];
                } elseif ($cache['number'] + 1 <= 10) {
                    $cache = ['time' => time(), 'number' => $cache['number'] + 1, 'geoip' => $this->request->ip()];
                }
                cache("user_login_{$user['username']}", $cache);
                if (($diff = 10 - $cache['number']) > 0) {
                    $this->error("<strong class='color-red'>登录账号或密码错误！</strong><p class='nowrap'>还有 {$diff} 次尝试机会，将锁定一小时内禁止登录！</p>");
                } else {
                    $this->error("<strong class='color-red'>登录账号或密码错误！</strong><p class='nowrap'>尝试次数达到上限，锁定一小时内禁止登录！</p>");
                }
            }
            // 登录成功并更新账号
            cache("user_login_{$user['username']}", null);
            Db::name('SystemUser')->where(['user_id' => $user['user_id']])->update([
                'login_at' => time(), 'login_ip' => $this->request->ip(),'login_num' => Db::raw('login_num+1')

            ]);
            session('admin_loginskey', null);
            session('admin', $user);
            NodeService::applyUserAuth();
            $this->success('登录成功，正在进入系统...',url('admin/Index/index'));
        }
    }

    public function out(){
        \think\facade\Session::clear('admin');
        $this->success('退出登录成功！', url('@admin/login'));
    }
}