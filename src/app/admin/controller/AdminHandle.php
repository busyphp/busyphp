<?php

namespace BusyPHP\app\admin\controller;

use BusyPHP\App;
use BusyPHP\app\admin\model\admin\user\AdminUserInfo;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\setting\AdminSetting;
use BusyPHP\app\admin\setting\PublicSetting;
use BusyPHP\Handle;
use BusyPHP\helper\util\Arr;
use BusyPHP\Request;
use BusyPHP\Url;
use Exception;
use stdClass;
use think\Container;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\exception\HttpResponseException;
use think\Response;
use think\response\View;
use Throwable;

/**
 * 后台异常处理类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/18 下午下午1:40 AdminHandle.php $
 */
class AdminHandle extends Handle
{
    /**
     * 处理数据渲染
     * @param Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e) : Response
    {
        if ($e instanceof HttpResponseException) {
            return parent::render($request, $e);
        }
        
        if ($request->isAjax() && !self::isSinglePage()) {
            return self::restResponseError($e);
        }
        
        if ($request->isJson()) {
            return parent::render($request, $e);
        }
        
        // 异常页面
        try {
            /** @var View $view */
            $view = View::create(__DIR__ . DIRECTORY_SEPARATOR . '../view/exception.html', 'view');
            foreach (self::templateBaseData('系统发生错误') as $key => $value) {
                $view->assign($key, $value);
            }
            
            foreach ($this->convertExceptionToArray($e) as $key => $value) {
                $view->assign($key, $value);
            }
            
            return $view;
        } catch (Exception $e) {
            return parent::render($request, $e);
        }
    }
    
    
    /**
     * 是否单页请求
     * @return bool
     */
    public static function isSinglePage() : bool
    {
        return Request::init()->header('Busy-Admin-Plugin', '') === 'SinglePage';
    }
    
    
    /**
     * 模板基础数据
     * @param string             $pageTitle
     * @param AdminUserInfo|null $adminUser
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     */
    public static function templateBaseData($pageTitle = '', ?AdminUserInfo $adminUser = null) : array
    {
        $request = Request::init();
        $data    = [];
        
        // 全局URL
        $data['url'] = [
            'root'       => $request->getWebUrl(),
            'app'        => $request->getAppUrl(),
            'self'       => url(),
            'controller' => $request->controller(),
            'action'     => $request->action(),
        ];
        
        
        // 计算面包屑
        $menuModel   = SystemMenu::init();
        $hashList    = $menuModel->getHashList();
        $breadcrumb  = [];
        $currentMenu = $hashList[md5($request->getPath())] ?? null;
        if ($currentMenu) {
            $idList     = $menuModel->getIdList();
            $parentList = $menuModel->getIdParens();
            $root       = $request->getAppUrl();
            foreach ($parentList[$currentMenu->id] ?? [] as $id) {
                if ($item = ($idList[$id] ?? null)) {
                    $breadcrumb[] = [
                        'name' => $item->name,
                        'url'  => $item->url ? $root . ltrim($item->url, '/') : '',
                    ];
                }
            }
            krsort($breadcrumb);
            $breadcrumb = array_values($breadcrumb);
            
            // 最终页面
            $query = [];
            foreach ($currentMenu->paramList as $item) {
                $query[$item] = $request->get($item);
            }
            
            $breadcrumb[] = [
                'name' => $currentMenu->name,
                'url'  => $root . ltrim($currentMenu->url, '/') . ($query ? '?' . http_build_query($query) : '')
            ];
        }
        
        
        // 页面名称
        if (!$pageTitle && $currentMenu) {
            $pageTitle = $currentMenu->name;
        }
        
        // 系统信息
        $data['system'] = [
            'title'             => AdminSetting::init()->getTitle(),
            'page_title'        => $pageTitle,
            'page_title_suffix' => ' - ' . AdminSetting::init()->getTitle(),
            'favicon'           => PublicSetting::init()->getFavicon(),
            'logo_icon'         => AdminSetting::init()->getLogoIcon(),
            'logo_horizontal'   => AdminSetting::init()->getLogoHorizontal(),
            'user'              => $adminUser ?? [],
            'breadcrumb'        => $breadcrumb
        ];
        
        // 样式路径配置
        $skinRoot     = $request->getAssetsUrl() . 'admin/';
        $data['skin'] = [
            'root'   => $skinRoot,
            'css'    => $skinRoot . 'css/',
            'js'     => $skinRoot . 'js/',
            'images' => $skinRoot . 'images/',
            'lib'    => $skinRoot . 'lib/'
        ];
        
        return $data;
    }
    
    
    /**
     * Rest响应
     * @param int    $code 错误码，1为成功
     * @param string $message 消息
     * @param array  $result 数据
     * @param mixed  $url 跳转的URL
     * @return Response
     */
    public static function restResponse(int $code = 1, string $message = '', array $result = [], $url = '')
    {
        /** @var App $app */
        $app = Container::getInstance()->make(App::class);
        $url = (string) $url;
        
        if ($code === 1) {
            if ($result && !Arr::isAssoc($result)) {
                return self::restResponse(0, '返回数据结构必须是键值对形式');
            }
        } else {
            $result = new stdClass();
        }
        
        $data = [
            'code'    => $code,
            'message' => $message ?: ($code === 1 ? 'Succeeded' : 'Failed'),
            'result'  => $result,
            'url'     => $url,
        ];
        if ($app->isDebug()) {
            $data['traces'] = trace();
        }
        
        return Response::create($data, 'json');
    }
    
    
    /**
     * Rest响应成功
     * @param string|array     $message 成功消息或成功的数据
     * @param array|string|Url $result 成功数据或跳转的URL
     * @param string|Url       $url 跳转的地址
     * @return Response
     */
    public static function restResponseSuccess($message = '', $result = [], $url = '')
    {
        if (is_array($message)) {
            $url     = $result;
            $result  = $message;
            $message = '';
        } elseif (!is_array($result) && $result) {
            $url = $result;
        }
        
        return self::restResponse(1, $message, $result, $url);
    }
    
    
    /**
     * Rest响应失败
     * @param string|Throwable $message 失败消息或异常类对象
     * @param string|Url|int   $url 跳转地址或错误代码
     * @param int              $code 错误代码
     * @return Response
     */
    public static function restResponseError($message = '', $url = '', int $code = 0)
    {
        if ($message instanceof Throwable) {
            if ($message->getCode() !== 1) {
                $code = $message->getCode();
            }
            $message = $message->getMessage();
        }
        
        if (is_numeric($url)) {
            $code = $url;
        }
        
        return self::restResponse($code === 1 ? 0 : $code, $message, [], $url);
    }
}