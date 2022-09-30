<?php
declare (strict_types = 1);

namespace BusyPHP\app\general\controller;

use BusyPHP\app\admin\setting\StorageSetting;
use BusyPHP\Controller;
use BusyPHP\image\driver\Local;
use think\Response;
use Throwable;

/**
 * 动态缩图
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/26 下午下午3:44 ThumbController.php $
 */
class ThumbController extends Controller
{
    /**
     * TODO 支持任意磁盘系统
     * 动态缩图
     */
    public function index() : Response
    {
        try {
            $path    = $this->param('path/s', 'trim');
            $process = $this->param('process/s', 'trim');
            
            return StorageSetting::init()
                ->getLocalFileSystem()
                ->image()
                ->response(Local::convertProcessRuleToParameter($process, $path));
        } catch (Throwable $e) {
            abort(404, $e->getMessage());
        }
    }
}