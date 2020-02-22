<?php
declare (strict_types = 1);

namespace app;

use think\App;
use think\facade\Validate;
use think\facade\Db;
use think\validate\ValidateRule as Rule;
use think\exception\ValidateException;
use think\exception\HttpResponseException;
use think\Response;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;
        // 控制器初始化
        $this->initialize();
        // 列表排序操作
        if ($this->request->isPost()) $this->_sort();
    }

    // 初始化
    protected function initialize()
    {
    }

     /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = true)
    {
        if (is_array($validate)) {
            $v = Validate::rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);
        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }
        try {
            $v->failException(true)->check($data);
            return $data;
        }catch (ValidateException $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 返回视图内容
     * @param string $tpl 模板名称
     * @param array $vars 模板变量
     */
    public function fetch($tpl = '', $vars = [])
    {
        foreach ($this as $name => $value) $vars[$name] = $value;

        throw new HttpResponseException(view($tpl, $vars));
    }


     /**
    * 返回失败的操作
    * @access protected
    * @access protected
    * @param mixed $msg 提示信息
    * @param string $url 跳转的URL地址
    * @param mixed $data 返回的数据
    * @param integer $wait 跳转等待时间
    * @param array $header 发送的Header信息
    * @return void
    */
    protected function error($msg='',string $url=null, $data = [],string $type = '', int $wait = 1, array $header = [],int $code = 0){

        if (is_null($url)) {
            $url = $this->request->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : $this->app->route->buildUrl($url);
        }
        $result = [
            'code' => 0,
            'msg' => $msg,
            'data' => $data,
        ];
        if(app('http')->getName() != 'api'){
            $result['url'] = $url;
            $result['wait'] = $wait;
        }
        if(empty($type)){
            throw new HttpResponseException(json($result));
        }else{
            $type = $this->getResponseType();
            $result['url'] = $url;
            $result['wait'] = 3;
            if ('html' == strtolower($type)) {
                $type = 'view';
                $response = Response::create($this->app->config->get('app.dispatch_error_tmpl'), $type)->assign($result)->header($header);
            } else {
                $response = Response::create($result, $type)->header($header);
            }
            throw new HttpResponseException($response);
        }
    }
    /**
    * 返回成功的操作
    * @access protected
    * @access protected
    * @param mixed $msg 提示信息
    * @param string $url 跳转的URL地址
    * @param mixed $data 返回的数据
    * @param integer $wait 跳转等待时间
    * @param array $header 发送的Header信息
    * @return void
    */
    protected function success($msg='', string $url=null,$data = [],string $type = '', int $wait =1, array $header = [],int $code = 0){
        
        if (is_null($url)) {
            $url = $this->request->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ($url == 'back') {
             $url = 'javascript:history.back();';
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : $this->app->route->buildUrl($url);
        }
        $result = [
            'code' => 1,
            'msg' => $msg,
            'data' => $data,
        ];
        if(app('http')->getName() != 'api'){
            $result['url'] = $url;
            $result['wait'] = $wait;
        }
        if(empty($type)){
            throw new HttpResponseException(json($result));
        }else{
            $type = $this->getResponseType();
            $wait = 3;
            if ('html' == strtolower($type)) {
                $type = 'view';
                $response = Response::create($this->app->config->get('app.dispatch_success_tmpl'), $type)->assign($result)->header($header);
            } else {
                $response = Response::create($result, $type)->header($header);
            }

            throw new HttpResponseException($response);
        }
    }

    /**
     * 快捷删除逻辑器
     * @param string|\think\db\Query $dbQuery
     * @param string $pkField 数据对象主键
     * @param array $where 额外更新条件
     * @return boolean|null
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    protected function _delete($dbQuery, $pkField = '', $where = [])
    {
        $query = Db::table($dbQuery);
        if (!isset($where[$pkField]) ) {
            $query->whereIn($pkField, explode(',', $this->request->post($pkField)));
        }

        // 执行删除操作
        if (method_exists($query, 'getTableFields') && in_array('delete_at', $query->getTableFields())) {
            $result = $query->where($where)->update(['delete_at' => time()]);
        } else {
            $result = $query->where($where)->delete();
        }
        // 回复前端结果
        if ($result !== false) {
            return $this->success('删除成功！', '');
        } else {
            return $this->error('删除失败, 请稍候再试！');
        }
    }

    /**
     * 快捷更新逻辑器
     * @param string|\think\db\Query $dbQuery
     * @param array $data 表单扩展数据
     * @param string $pkField 数据对象主键
     * @param array $where 额外更新条件
     * @return boolean
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    protected function _save($dbQuery, $pkField = '',$data = [], $msg='', $where = [])
    {

        $query = Db::table($dbQuery);
        // 主键限制处理
        if (!isset($where[$pkField])) {
            $query->whereIn($pkField, explode(',',$this->request->post($pkField)));
            if (isset($data)) unset($data[$pkField]);
        }
        // 执行更新操作
        $result = $query->where($where)->update($data) !== false;
        // 回复前端结果
        if ($result !== false) {
            return $this->success('保存成功!');
        } else {
            return $this->error('保存失败, 请稍候再试!');
        }
    }
    /**
     * 列表排序操作
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    protected function _sort()
    {
        switch (strtolower($this->request->post('action', ''))) {
            case 'resort':
                foreach ($this->request->post() as $key => $value) {
                    if (preg_match('/^_\d{1,}$/', $key) && preg_match('/^\d{1,}$/', $value)) {
                        list($where, $update) = [['id' => trim($key, '_')], ['sort' => $value]];
                        if (false === Db::table($this->table)->where($where)->update($update)) {
                            return $this->error('排序失败, 请稍候再试！');
                        }
                    }
                }
                return $this->success('排序成功, 正在刷新页面！', '');
            case 'sort':
                $where = $this->request->post();
                $sort = intval($this->request->post('sort'));
                unset($where['action'], $where['sort']);
                if (Db::table($this->table)->where($where)->update(['sort' => $sort]) !== false) {
                    return $this->success('排序参数修改成功！');
                }
                return $this->error('排序参数修改失败，请稍候再试！');
        }
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        return $this->request->isJson() || $this->request->isAjax() ? 'json' : 'html';
    }

}
