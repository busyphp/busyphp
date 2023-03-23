<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller;

use think\exception\HttpException;
use think\Response;

/**
 * admin内部基本控制器
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午6:33 下午 InsideController.php $
 * @internal 仅供内部使用
 */
abstract class InsideController extends AdminController
{
    /**
     * 内置模版显示
     * @param string $template
     * @param string $charset
     * @param string $contentType
     * @param string $content
     * @param array  $config
     * @return Response
     * @internal 仅供内部使用
     */
    protected function insideDisplay($template = '', $charset = 'utf-8', $contentType = '', $content = '', array $config = []) : Response
    {
        $this->app->config->set(array_merge([
            'view_path'   => dirname(__DIR__) . '/view/',
            'view_depr'   => DIRECTORY_SEPARATOR,
            'view_suffix' => 'html',
            'auto_rule'   => 1
        ], $config), 'view');
        
        return $this->display($template, $charset, $contentType, $content);
    }
    
    
    /**
     * 发行模式下禁用
     * @param string ...$excludeAction 排除的方法
     * @internal 仅供内部使用
     */
    protected function releaseDisabled(...$excludeAction)
    {
        if (!$this->app->isDebug() && !in_array($this->request->action(), $excludeAction)) {
            throw new HttpException(404);
        }
    }
}