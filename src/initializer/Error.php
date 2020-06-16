<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://zjzit.cn>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace BusyPHP\initializer;

use think\exception\ErrorException;
use think\facade\Log;

/**
 * 错误和异常处理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/16 下午11:57 上午 Error.php $
 */
class Error extends \think\initializer\Error
{
    /**
     * Error Handler
     * @access public
     * @param integer $errno 错误编号
     * @param string  $errstr 详细错误信息
     * @param string  $errfile 出错的文件
     * @param integer $errline 出错行号
     * @throws ErrorException
     */
    public function appError(int $errno, string $errstr, string $errfile = '', int $errline = 0) : void
    {
        // 针对一些不致命的错误只输出到Console
        if (in_array($errno, $this->app->config->get('app.error_level_exclude', []))) {
            if (strpos($errstr, ':')) {
                $name    = strstr($errstr, ':', true);
                $errstr = $this->app->lang->has($name) ? $this->app->lang->get($name) . strstr($errstr, ':') : $errstr;
            }
        
            Log::record("[{$errno}]{$errstr}[{$errfile}:{$errline}]", 'error');
        
            return;
        }
        
        $exception = new ErrorException($errno, $errstr, $errfile, $errline);
        
        if (error_reporting() & $errno) {
            // 将错误信息托管至 think\exception\ErrorException
            throw $exception;
        }
    }
}
