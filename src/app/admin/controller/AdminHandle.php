<?php

namespace BusyPHP\app\admin\controller;

use BusyPHP\App;
use BusyPHP\Handle;
use BusyPHP\helper\util\Arr;
use BusyPHP\Request;
use BusyPHP\Url;
use stdClass;
use think\Container;
use think\exception\HttpResponseException;
use think\Response;
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
        if ($request->isAjax()) {
            if ($e instanceof HttpResponseException) {
                return parent::render($request, $e);
            }
            
            return self::restResponseError($e);
        }
        
        return parent::render($request, $e);
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