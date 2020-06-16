<?php
declare(strict_types = 1);

namespace BusyPHP\middleware;

use BusyPHP\contract\interfaces\Middleware;
use BusyPHP\Request;
use Closure;
use think\Response;

/**
 * 路由基础中间件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午12:26 下午 BaseMiddleware.php $
 */
class RouteInit implements Middleware
{
    /**
     * @var Request
     */
    private $request;
    
    
    public function handle(Request $request, Closure $next)
    {
        $this->request = $request;
        
        // todo 未解决调用控制器中间件
        if ($request->route('type') == 'plugin') {
            $group = $request->route('group');
            $this->request->setController(($group ? $group . '.' : '') . $request->route('control'));
            $this->request->setAction($request->route('action'));
        }
        
        $this->parseRootUrl();
        $this->setDefines();
        
        return $next($request);
    }
    
    
    /**
     * 解析入口URL目录
     */
    private function parseRootUrl()
    {
        // 解析站点入口URL
        $root = $this->request->baseFile();
        if ($root && 0 !== strpos($this->request->url(), $root)) {
            $root = str_replace('\\', '/', dirname($root));
        }
        
        $root = rtrim($root, '/') . '/';
        $root = strpos($root, '.') ? ltrim(dirname($root), DIRECTORY_SEPARATOR) : $root;
        if ('' != $root) {
            $root = '/' . ltrim($root, '/');
        }
        $webUrl = rtrim($root, '/') . '/';
        $this->request->setWebUrl($webUrl);
        
        
        // 解析应用入口Url
        $appUrl = $this->request->root();
        if (false === strpos($appUrl, '.')) {
            $appUrl = $webUrl . trim($appUrl, '/');
        }
        $appUrl = rtrim($appUrl, '/') . '/';
        $this->request->setAppUrl($appUrl);
    }
    
    
    /**
     * 设置一些常量
     */
    private function setDefines()
    {
        // 分组 / 控制器 / 方法
        $groupName  = '';
        $controller = $this->request->controller();
        if (false !== strpos($controller, '.')) {
            $arr        = explode('.', $controller);
            $arr        = array_map('trim', $arr);
            $groupName  = $arr[0];
            $controller = $arr[1];
        }
        /**
         * 分组名称
         */
        define('GROUP_NAME', ucfirst($groupName));
        /**
         * 控制器名称
         */
        define('MODULE_NAME', ucfirst($controller));
        /**
         * 执行方法名称
         */
        define('ACTION_NAME', $this->request->action());
        /**
         * 网站根目录地址
         */
        define('URL_ROOT', $this->request->getWebUrl());
        /**
         * 当前项目地址
         */
        define('URL_APP', $this->request->getAppUrl());
        /**
         * 静态资源URL
         */
        define('URL_ASSETS', $this->request->getWebAssetsUrl());
        /**
         * 当前URL，包含QueryString
         */
        define('URL_SELF', $this->request->url());
        /**
         * 当前域名
         */
        define('URL_DOMAIN', $this->request->domain());
    }
    
    
    /**
     * 结束调度
     * @param Response $response
     */
    public function end(Response $response) : void
    {
    }
}