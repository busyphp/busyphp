<?php

namespace BusyPHP\app\admin\controller;

use BusyPHP\helper\util\Str;

/**
 * admin内部基本控制器
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午6:33 下午 InsideController.php $
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
    protected function getViewPath()
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;
    }
    
    
    /**
     * 获取当前控制器模板存放目录
     * @return string
     */
    protected function getTemplatePath()
    {
        $group = '';
        if (defined('GROUP_NAME') && GROUP_NAME) {
            $group = Str::snake(GROUP_NAME) . DIRECTORY_SEPARATOR;
        }
        
        return $this->getViewPath() . $group . Str::snake(MODULE_NAME) . DIRECTORY_SEPARATOR;
    }
    
    
    /**
     * 解析模板地址
     * @param string $template
     * @return string
     */
    protected function parseTemplate($template = '')
    {
        if (!$template) {
            return $this->getTemplatePath() . ACTION_NAME . '.html';
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