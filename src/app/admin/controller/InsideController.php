<?php

namespace BusyPHP\app\admin\controller;

use BusyPHP\helper\util\Str;

/**
 * admin内部基本控制器
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午6:33 下午 InsideController.php $
 * @internal
 */
class InsideController extends AdminCurdController
{
    protected function init($template)
    {
        return $this->parseTemplate(parent::init($template));
    }
    
    
    /**
     * 获取模板存放目录
     * @return string
     */
    protected function getViewPath() : string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;
    }
    
    
    /**
     * 获取当前控制器模板存放目录
     * @return string
     */
    protected function getTemplatePath() : string
    {
        $group      = '';
        $controller = $this->request->controller();
        if (false !== strpos($controller, '.')) {
            [$group, $controller] = explode('.', $controller);
            $group = Str::snake($group) . DIRECTORY_SEPARATOR;
        }
        $controller = Str::snake($controller);
        
        $dir = $this->request->route('dir');
        if ($dir) {
            $dir = Str::snake($dir) . DIRECTORY_SEPARATOR;
        }
        
        return $this->getViewPath() . $dir . $group . $controller . DIRECTORY_SEPARATOR;
    }
    
    
    /**
     * 解析模板地址
     * @param string $template
     * @return string
     */
    protected function parseTemplate($template = '')
    {
        if (!$template) {
            return $this->getTemplatePath() . $this->request->action() . '.html';
        } else {
            if (is_file($template)) {
                return $template;
            }
            
            if (false === strpos($template, '/')) {
                return $this->getTemplatePath() . $template . '.html';
            } elseif (false === strpos($template, '.')) {
                return $this->getViewPath() . $template . '.html';
            } else {
                return $template;
            }
        }
    }
}