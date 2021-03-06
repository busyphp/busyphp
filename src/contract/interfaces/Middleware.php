<?php
declare(strict_types = 1);

namespace BusyPHP\contract\interfaces;

use BusyPHP\Request;
use Closure;
use think\Response;

/**
 * 中间件接口类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/16 下午9:30 下午 Middleware.php $
 */
interface Middleware
{
    /**
     * 执行调度
     * @param Request $request
     * @param Closure $next
     * @return Closure|Response
     */
    public function handle(Request $request, Closure $next);
    
    
    /**
     * 结束调度
     * @param Response $response
     */
    public function end(Response $response) : void;
}