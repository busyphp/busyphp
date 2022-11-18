<?php
declare (strict_types = 1);

namespace BusyPHP;

use think\Exception;
use think\exception\ValidateException;
use think\facade\Route;
use think\Response;
use think\response\Json;
use think\response\Jsonp;
use think\response\Redirect;
use think\response\View;
use think\response\Xml;
use think\route\Url;
use think\Validate;
use Throwable;

/**
 * 控制器基础类
 * @method bool isGet 当前是否get请求
 * @method bool isPost 当前是否post请求
 * @method bool isPut 当前是否put请求
 * @method bool isDelete 当前是否delete请求
 * @method bool isHead 当前是否head请求
 * @method bool isPatch 当前是否patch请求
 * @method bool isOptions 当前是否options请求
 * @method bool isCli 当前是否运行在CLI模式下
 * @method bool isCgi 当前是否运行在CGI模式下
 * @method bool isSsl 当前是否https请求
 * @method bool isJson 当前是否请求json
 * @method bool isAjax(bool $ajax = false) 当前是否Ajax请求 <br /> $ajax true 获取原始ajax请求
 * @method bool isPjax(bool $ajax = false) 当前是否Pjax请求 <br /> $ajax true 获取原始pjax请求
 * @method bool isMobile 是否在手机端运行
 * @method bool isAndroid 是否在安卓端运行
 * @method bool isIos 是否在苹果上运行
 * @method mixed param(string $key = "", callable $filter = "", string $default = null) 获取参数
 * @method mixed get(string $key = "", callable $filter = "", string $default = null) 获取$_GET参数
 * @method mixed post(string $key = "", callable $filter = "", string $default = null) 获取$_POST参数
 * @method mixed route(string $key = "", callable $filter = "", string $default = null) 获取路由参数
 * @method mixed cookie(string $key = "", callable $filter = "", string $default = null) 获取Cookie
 * @method Json json(mixed $data = [], int $code = 200, array $header = [], array $options = []) 输出JSON数据
 * @method Jsonp jsonp(mixed $data = [], int $code = 200, array $header = [], array $options = []) 输出JSONP数据
 * @method Xml xml(mixed $data = [], int $code = 200, array $header = [], array $options = []) 输出XML数据
 * @method Redirect redirect($url = '', $code = 302) 执行URL跳转
 */
abstract class Controller
{
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @var App
     */
    protected $app;
    
    /**
     * @var View
     */
    protected $view;
    
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
     * @param App $app 应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;
        $this->view    = Response::create('', 'view', 200);
        $this->initialize();
    }
    
    
    /**
     * 初始化控制基本事物
     */
    protected function initialize()
    {
    }
    
    
    /**
     * 验证数据
     * @access protected
     * @param array        $data 数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array        $message 提示信息
     * @param bool         $batch 是否批量验证
     * @return bool
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false) : bool
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
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
        
        return $v->failException(true)->check($data);
    }
    
    
    /**
     * 赋值模板变量
     * @param string|array $name 变量名称或批量数组
     * @param mixed        $value 变量值
     */
    protected function assign($name, $value = null)
    {
        if (is_array($name)) {
            $this->view->assign($name);
        } else {
            $this->view->assign($name, $value);
        }
    }
    
    
    /**
     * 输出之前处理
     */
    protected function initView() : void
    {
    }
    
    
    /**
     * 模板显示
     * @param string $template 指定要调用的模板文件 默认为空 由系统自动定位模板文件
     * @param string $charset 输出编码
     * @param string $contentType 输出类型
     * @param string $content 输出内容
     * @return Response
     */
    protected function display($template = '', $charset = 'utf-8', $contentType = '', $content = '') : Response
    {
        $this->initView();
        $this->view->data($template);
        $this->view->header([
            'X-Powered-By' => 'BusyPHP'
        ]);
        
        if ($contentType && $charset) {
            $this->view->contentType($contentType, $charset);
        }
        
        if ($content) {
            $this->view->content($content);
        }
        
        $this->view->filter(function(string $content) {
            return str_replace([
                '__ROOT__',
                '__APP__',
                '__SELF__',
                '__URL__',
                '__ROUTE__',
                '__ASSETS__',
                '__DOMAIN__',
            ], [
                $this->request->getWebUrl(),
                $this->request->getAppUrl(),
                $this->request->url(),
                (string) Route::buildUrl(),
                $this->request->getRoutePath(),
                $this->request->getAssetsUrl(),
                $this->request->domain(),
            ], $content);
        });
        
        return $this->view;
    }
    
    
    /**
     * 获取输出页面内容
     * @param string $template 指定要调用的模板文件 默认为空 由系统自动定位模板文件
     * @param string $charset 输出编码
     * @param string $contentType 输出类型
     * @param string $content 输出内容
     * @return string
     */
    protected function fetch($template = '', $charset = 'utf-8', $contentType = '', $content = '') : string
    {
        return $this->display($template, $charset, $contentType, $content)->getContent();
    }
    
    
    /**
     * 一些方法实现
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        $upName = strtoupper($name);
        switch ($upName) {
            // 请求判断
            case 'ISGET':
            case 'ISPOST':
            case 'ISPUT':
            case 'ISDELETE':
            case 'ISHEAD':
            case 'ISPATCH':
            case 'ISOPTIONS':
            case 'ISCLI':
            case 'ISCGI':
            case 'ISSSL':
            case 'ISJSON':
            case 'ISAJAX':
            case 'ISPJAX':
            case 'ISMOBILE':
            case 'ISANDROID':
            case 'ISIOS':
                $method = 'is' . ucfirst(strtolower(substr($upName, 2)));
                
                if (in_array($upName, ['ISAJAX', 'ISPJAX'])) {
                    return $this->request->$method($arguments[0] ?? false);
                } elseif (in_array($upName, ['ISANDROID', 'ISIOS'])) {
                    $method = 'is_' . strtolower(substr($upName, 2));
                    
                    return $method();
                }
                
                return $this->request->$method();
            
            // 获取参数
            case 'GET':
            case 'POST':
            case 'PARAM':
            case 'COOKIE':
            case 'ROUTE':
                $method  = $upName;
                $name    = '';
                $default = null;
                $filter  = '';
                if ($arguments) {
                    $name    = $arguments[0] ?? '';
                    $filter  = $arguments[1] ?? '';
                    $default = $arguments[2] ?? null;
                }
                
                // 转换默认类型
                if ((false !== $index = strpos($name, '/')) && $default === null) {
                    $type = substr($name, $index + 1);
                    switch (strtolower($type)) {
                        // 数组
                        case 'a':
                            $default = [];
                        break;
                        // 数字
                        case 'd':
                            $default = 0;
                        break;
                        // 浮点
                        case 'f':
                            $default = 0.0;
                        break;
                        // 布尔
                        case 'b':
                            $default = false;
                        break;
                        // 字符串
                        case 's':
                            $default = '';
                    }
                }
                
                return $this->request->$method($name, $default, $filter);
            
            // 输出
            case 'JSON':
            case 'XML':
            case 'JSONP':
                $method  = strtolower($upName);
                $data    = [];
                $code    = 200;
                $header  = [];
                $options = [];
                if ($arguments) {
                    $data    = $arguments[0] ?? [];
                    $code    = isset($arguments[1]) && $arguments[1] > 0 ? $arguments[1] : 200;
                    $header  = $arguments[2] ?? [];
                    $options = $arguments[3] ?? [];
                }
                
                return $method($data, $code, $header, $options);
            
            // 跳转
            case 'REDIRECT':
                $url  = '';
                $code = 302;
                if ($arguments) {
                    $url  = $arguments[0] ?? '';
                    $code = isset($arguments[1]) && $arguments[1] > 0 ? $arguments[1] : 302;
                }
                
                if ($url instanceof Url) {
                    $url = (string) $url;
                }
                
                return Response::create($url, 'redirect', $code);
            
            default:
                throw new Exception('未定义方法:' . $name);
        }
    }
    
    
    /**
     * 操作错误跳转的快捷方法
     * @param mixed  $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @return Response
     */
    protected function error($message, $jumpUrl = '') : Response
    {
        return $this->dispatchJump($message, false, $jumpUrl);
    }
    
    
    /**
     * 操作成功跳转的快捷方法
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @return Response
     */
    protected function success($message, $jumpUrl = '') : Response
    {
        return $this->dispatchJump($message, true, $jumpUrl);
    }
    
    
    /**
     * 默认跳转操作 支持错误导向和正确跳转
     * 提示页面为可配置 支持模板标签
     * @param mixed  $message 提示信息
     * @param bool   $status 状态
     * @param string $jumpUrl 页面跳转地址
     * @return Response
     */
    protected function dispatchJump($message, bool $status = true, $jumpUrl = '') : Response
    {
        if ($message instanceof Throwable) {
            $message = $message->getMessage();
        }
        
        $this->assign('status', $status);
        $this->assign('wait_second', $status ? 1 : 3);
        $this->assign('message', $message);
        if ($status) {
            $this->assign('jump_url', $jumpUrl ?: $this->request->getRedirectUrl($this->request->getAppUrl()));
            $template = $this->app->config->get('app.success_tmpl') ?: __DIR__ . '/../assets/template/message.html';
        } else {
            $this->assign('jump_url', $jumpUrl ?: 'javascript:history.back(-1);');
            $template = $this->app->config->get('app.error_tmpl') ?: __DIR__ . '/../assets/template/message.html';
        }
        
        return $this->display($template);
    }
}
