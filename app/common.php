<?php
// 应用公共文件
use think\facade\Cache;
use think\facade\Db;
use think\facade\Request;
use app\common\service\Node as NodeService;
 
if (!function_exists('sysconf')) {
    /**
     * 设备或配置系统参数
     * @param string $name 参数名称
     * @param boolean $value 无值为获取
     * @return string|boolean
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    function sysconf($name, $value = null)
    {
        static $data = [];
        list($field, $raw) = explode('|', "{$name}|");
        $key = md5(config('database.hostname') . '#' . config('database.database'));

        if ($value !== null) {
            Cache::set("system_config_{$key}", $data);
            list($row, $data) = [['sys_name' => $field, 'sys_value' => $value], []];
            $wh = Db::name('system_config')->where('sys_name','=',$field)->find();
            return Db::name('system_config')->where($wh)->save($row);
        }
        if (empty($data)) {
           	$data = Cache::get('system_config_{$key}');
            if (empty($data)) {
        		$data = Db::name('system_config')->column('sys_value','sys_name');
                Cache::set("system_config_{$key}", $data);
            }
        }

        if (isset($data[$field])) {
            if (strtolower($raw) === 'raw') {
                return $data[$field];
            } else {
                return htmlspecialchars($data[$field]);
            }
        } else {
            return '';
        }
    }   

}
 

if (!function_exists('auth')) {
    /**
     * 节点访问权限检查
     * @param string $node 需要检查的节点
     * @return boolean
     * @throws ReflectionException
     */
    function auth($node)
    {
        return NodeService::checkAuth($node);
    }
}

if (!function_exists('format_datetime')) {
    /**
     * 日期格式标准输出
     * @param string $datetime 输入日期
     * @param string $format 输出格式
     * @return false|string
     */
    function format_datetime($datetime, $format = 'Y年m月d日 H:i:s')
    {
        if (empty($datetime)) return '-';
        if (is_numeric($datetime)) {
            return date($format, $datetime);
        } else {
            return date($format, strtotime($datetime));
        }
    }
}



    
    /**
     * 获取当前域名及根路径
     * @return string
     */
    function base_url()
    {
        static $baseUrl = '';
        if (empty($baseUrl)) {
            $request = Request::instance();
            $subDir = str_replace('\\', '/', dirname($request->server('PHP_SELF')));
            $baseUrl = $request->scheme() . '://' . $request->host();
        }
        return $baseUrl;
    }

	/**
     * 一维数据数组生成数据树
     * @param array $list 数据列表
     * @param string $id 父ID Key
     * @param string $pid ID Key
     * @param string $son 定义子数据Key
     * @return array
     */
    function arr2tree($list, $id = 'id', $pid = 'pid', $son = 'sub')
    {
        list($tree, $map) = [[], []];
        foreach ($list as $item) $map[$item[$id]] = $item;
        foreach ($list as $item) if (isset($item[$pid]) && isset($map[$item[$pid]])) {
            $map[$item[$pid]][$son][] = &$map[$item[$id]];
        } else $tree[] = &$map[$item[$id]];
        unset($map);
        return $tree;
    }

    /**
     * 获取数据树子ID
     * @param array $list 数据列表
     * @param int $id 起始ID
     * @param string $key 子Key
     * @param string $pkey 父Key
     * @return array
     */
    function getArrSubIds($list, $id = 0, $key = 'id', $pkey = 'pid')
    {
        $ids = [intval($id)];
        foreach ($list as $vo) if (intval($vo[$pkey]) > 0 && intval($vo[$pkey]) === intval($id)) {
            $ids = array_merge($ids, getArrSubIds($list, intval($vo[$key]), $key, $pkey));
        }
        return $ids;
    }
    /**
     * 一维数据数组生成数据树
     * @param array $list 数据列表
     * @param string $id ID Key
     * @param string $pid 父ID Key
     * @param string $path
     * @param string $ppath
     * @return array
     */
    function arr2table(array $list, $id = 'id', $pid = 'pid', $path = 'path', $ppath = '')
    {
        $tree = [];
        foreach (arr2tree($list, $id, $pid) as $attr) {
            $attr[$path] = "{$ppath}-{$attr[$id]}";
            $attr['sub'] = isset($attr['sub']) ? $attr['sub'] : [];
            $attr['spt'] = substr_count($ppath, '-');
            $attr['spl'] = str_repeat(" ——— ", $attr['spt']);
            $sub = $attr['sub'];
            unset($attr['sub']);
            $tree[] = $attr;
            if (!empty($sub)) $tree = array_merge($tree, arr2table($sub, $id, $pid, $path, $attr[$path]));
        }
        return $tree;
    }


    /**
     * 获取当前域名及根路径
     * @return string
     */
    function module()
    {
        return app('http')->getName();
    }

    
    function replace_var($url,$string,$new_value)
    {
        while(substr($url,0,1)=="&"){
            $url=substr($url,1);
        }
        if($url!=""){
            $url_array=explode("&",$url);
            $new_url='';
            $string_len=strlen($string)+1;
            foreach ($url_array as $key => $value) {
                if(substr($url_array[$key],0,$string_len)==$string."="){
                    $url_array[$key]=$string."=".$new_value;
                }
                if($key > 0){
                    $url_array[$key]="&".$url_array[$key];
                }
                $new_url=$new_url.$url_array[$key];
            }
        }else{
            $new_url=$_SERVER['PHP_SELF'];
        }
        return $new_url;
    }

    function add_querystring_var($url, $key, $value) {
        $url=preg_replace('/(.*)(?|&)'.$key.'=[^&]+?(&)(.*)/i','$1$2$4',$url.'&');
        $url=substr($url,0,-1);
        if(strpos($url,'?') === false){
            return ($url.'?'.$key.'='.$value);
        } else {
            return ($url.'&'.$key.'='.$value);
        }
    }