<?php

/**
 * @Author: 小明
 * @Date:   2020-01-17 17:06:47
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */
namespace app\common\service;

use think\facade\Db;
use think\facade\App;
use think\facade\Cache;
use think\facade\Request;

/**
 * 功能节点管理服务
 * Class Node
 * @package app\common\service
 */
class Node
{


    /**
     * 获取标准访问节点
     * @param string $node
     * @return string
     */
    public static function get($node = null)
    {
        if (empty($node)) return self::current();
        if (count(explode('/', $node)) === 1) {
            $node = module() . '/' . Request::controller() . '/' . $node;
        }
        return self::parseString(trim($node));
    }
    /**
     */
    public static function moduleKry(){
        return module().'_id';
    }
    public static function subMenus(){
        if(!empty( $subid = Request()->get("sub-id")) && !empty( $sidebar = Request()->get("sidebar-id")) ){
            $subid = Request()->get("sub-id");
            $sidebar = Request()->get("sidebar-id");
            $list = Db::name('system_menu')->where("menu_id='".$subid."' or pid='".$subid."'")->order('sort desc,create_at desc')->select()->ToArray();
            foreach ($list as $key => $value) {
                if ($value["menu_id"] == $sidebar) {
                    $list[$key]["sidebar"] = true;
                }
            }
            return arr2tree($list,'menu_id');
        }
        
    }
    /**
     * 强制验证访问权限
     * --- 需要加载控制器解析注释
     * @param null|string $node
     * @return boolean
     * @throws \ReflectionException
     */
    public static function forceAuth($node = null)
    {
        if (session('admin.user_id') === 10000) return true;
        $real = is_null($node) ? self::current() : self::full($node);
        list($module, $controller, $action) = explode('/', $real);
        if (class_exists($class = App::parseClass($module, 'controller', $controller))) {
            $reflection = new \ReflectionClass($class);
            if ($reflection->hasMethod($action)) {
                $comment = preg_replace("/\s/", '', $reflection->getMethod($action)->getDocComment());
                if (stripos($comment, '@authtrue') === false) return true;
                return in_array($real, (array)session(module().'.auth_nodes'));
            }
        }
        return true;
    }




    /**
     * 获取标准访问节点
     * @param string $node
     * @return string
     */
    public static function full($node = null)
    {
        if (empty($node)) return self::current();
        if (count(explode('/', $node)) === 1) {
            $node = module()  . '/' . Request()->controller() . '/' . $node;
        }
        return self::parseString(trim($node, " /"));
    }


    /**
     * 获取当前访问节点
     * @return string
     */
    public static function current()
    {
        return self::parseString(module()  . '/' . Request()->controller() . '/' . Request()->action());
    }

    /**
     * 驼峰转下划线规则
     * @param string $node 节点名称
     * @return string
     */
    public static function parseString($node)
    {
        if (count($nodes = explode('/', $node)) > 1) {
            $dots = [];
            foreach (explode('.', $nodes[1]) as $dot) {
                $dots[] = trim(preg_replace("/[A-Z]/", "_\\0", $dot), "_");
            }
            $nodes[1] = join('.', $dots);
        }
        return strtolower(join('/', $nodes));
    }

    /**
     * 初始化用户权限
     * @param boolean $force 是否重置系统权限
     */
    public static function applyUserAuth($force = false)
    {
        if ($force) {
            Cache::delete('NodeAuthList');
            Cache::delete('NodeClassData');
            Cache::delete('NodeMethodData');
        }
        if (($aids = session(module().'.authorize'))) {
            $where = [['status', '=', '10'], ['auth_id', 'in', $aids]];
            $subsql = Db::table('system_auth')->field('auth_id')->where($where)->buildSql();
            session(module().'.auth_nodes', array_unique(Db::table('system_auth_node')->whereRaw("auth in {$subsql}")->column('node')));
        } else {
            session(module().'.auth_nodes');
        }
    }



    /**
     * 获取系统菜单树数据
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getMenuNodeTree()
    {
        $list = Db::name('system_menu')->where(['status' => '10','module' => module() ])->order('sort desc,menu_id asc')->select();
        return self::buildMenuData(arr2tree($list,'menu_id'), self::getMethodList());
    }

    /**
     * 主菜单权限过滤
     * @param array $menus 当前菜单列表
     * @param array $nodes 系统权限节点
     * @return array
     * @throws \ReflectionException
     */
    private static function buildMenuData($menus, $nodes)
    {
        foreach ($menus as $key => &$menu) {
            if (!empty($menu['sub'])) $menu['sub'] = self::buildMenuData($menu['sub'], $nodes);
            if (!empty($menu['sub'])) $menu['url'] = '#';
            elseif (preg_match('/^https?\:/i', $menu['url'])) continue;
            elseif ($menu['url'] === '#') unset($menus[$key]);
            else {
                $node = join('/', array_slice(explode('/', preg_replace('/[\W]/', '/', $menu['url'])), 0, 3));
                $menu['url'] = url($menu['url']) . (empty($menu['params']) ? '' : "?{$menu['params']}");
                if (!self::checkAuth($node)) unset($menus[$key]);
            }
        }
        return $menus;
    }

    /**
     * 获取方法节点列表
     * @return array
     * @throws \ReflectionException
     */
    public static function getMethodList()
    {
        static $nodes = [];
        if (count($nodes) > 0) return $nodes;
        $nodes = Cache::get('NodeMethodData');
        // if (count((array)$nodes) > 0) return $nodes;
        self::eachController(function (\ReflectionClass $reflection, $prenode) use (&$nodes) {
            foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $action = strtolower($method->getName());
                list($node, $comment) = ["{$prenode}{$action}", preg_replace("/\s/", '', $method->getDocComment())];

                $nodes[$node] = [
                    'auth'  => stripos($comment, '@authtrue') !== false,
                    'menu'  => stripos($comment, '@menutrue') !== false,
                    'title' => preg_replace('/^\/\*\*\*(.*?)\*.*?$/', '$1', $comment),
                ];
                if (stripos($nodes[$node]['title'], '@') !== false) $nodes[$node]['title'] = '';
            }
        });
        Cache::set('NodeMethodData', $nodes);
        return $nodes;
    }
    /**
     * 控制器扫描回调
     * @param callable $callable
     * @throws \ReflectionException
     */
    public static function eachController($callable)
    {
        foreach (self::scanPath(app_path() . "controller/") as $file) {
            if (!preg_match("|/(\w+)/controller/(.+)\.php$|", $file, $matches)) continue;
            list($module, $controller) = [$matches[1], strtr($matches[2], '/', '.')];
            if (class_exists($class  = substr(strtr('app'.$matches[0], '/', '\\'), 0, -4))) {
                call_user_func($callable, new \ReflectionClass($class), Node::parseString("{$module}/{$controller}/"));
            }
        }
    }
    /**
     * 获取所有PHP文件列表
     * @param string $dirname 扫描目录
     * @param array $data 额外数据
     * @param string $ext 有文件后缀
     * @return array
     */
    private static function scanPath($dirname, $data = [], $ext = 'php')
    {
        foreach (glob("{$dirname}*") as $file) {
            if (is_dir($file)) {
                $data = array_merge($data, self::scanPath("{$file}/"));
            } elseif (is_file($file) && pathinfo($file, 4) === $ext) {
                $data[] = str_replace('\\', '/', $file);
            }
        }
        return $data;
    }

    /**
     * 检查指定节点授权
     * --- 需要读取缓存或扫描所有节点
     * @param null|string $node
     * @return boolean
     * @throws \ReflectionException
     */
    public static function checkAuth($node = null)
    {
        if (session('admin.user_id') === 10000) return true;
        $real = is_null($node) ? self::current() : self::full($node);
        if (isset(self::getAuthList()[$real])) {
            return in_array($real, (array)session(module().'.auth_nodes'));
        } else {
            return true;
        }
    }

    /**
     * 获取授权节点列表
     * @return array
     * @throws \ReflectionException
     */
    public static function getAuthList()
    {
        static $nodes = [];
        if (count((array)$nodes) > 0) return $nodes;
        $nodes = Cache::get('NodeAuthList');
        if (count((array)$nodes) > 0) return $nodes;
        foreach (self::getMethodList() as $key => $node) {
            if ($node['auth']) $nodes[$key] = $node['title'];
        }
        Cache::set('NodeAuthList', $nodes);
        return $nodes;
    }

    /**
     * 获取授权节点列表
     * @param array $checkeds
     * @return array
     * @throws \ReflectionException
     */
    public static function getAuthTree($checkeds = [])
    {
        static $nodes = [];
        if ( count($nodes) > 0) return $nodes;
        foreach (self::getAuthList() as $node => $title) {
            $pnode = substr($node, 0, strripos($node, '/'));
            $nodes[$node] = ['node' => $node, 'title' => $title, 'pnode' => $pnode, 'checked' => in_array($node, $checkeds)];
        }
        foreach (self::getClassList() as $node => $title) foreach (array_keys($nodes) as $key) {
            if (stripos($key, "{$node}/") !== false) {
                $pnode = substr($node, 0, strripos($node, '/'));
                $nodes[$node] = ['node' => $node, 'title' => $title, 'pnode' => $pnode, 'checked' => in_array($node, $checkeds)];
                $nodes[$pnode] = ['node' => $pnode, 'title' => ucfirst($pnode), 'checked' => in_array($pnode, $checkeds)];
            }
        }
        return $nodes = arr2tree($nodes, 'node', 'pnode', '_sub_');
    }
    /**
     * 获取控制器节点列表
     * @return array
     * @throws \ReflectionException
     */
    public static function getClassList()
    {
        static $nodes = [];
        if (count($nodes) > 0) return $nodes;
        $nodes = cache('NodeClassData');
        if ($nodes!=null &&count($nodes) > 0) return $nodes;
        self::eachController(function (\ReflectionClass $reflection, $prenode) use (&$nodes) {
            list($node, $comment) = [trim($prenode, ' / '), $reflection->getDocComment()];
            $nodes[$node] = preg_replace('/^\/\*\*\*(.*?)\*.*?$/', '$1', preg_replace("/\s/", '', $comment));
            if (stripos($nodes[$node], '@') !== false) $nodes[$node] = '';
        });
        cache('NodeClassData', $nodes);
        return $nodes;
    }


    /**
     * 获取可选菜单节点
     * @return array
     * @throws \ReflectionException
     */
    public static function getMenuNodeList()
    {
        static $nodes = [];
        if (count($nodes) > 0) return $nodes;
        foreach (self::getMethodList() as $node => $method) if ($method['menu']) {
            $nodes[] = ['node' => $node, 'title' => $method['title']];
        }
        return $nodes;
    }


}
