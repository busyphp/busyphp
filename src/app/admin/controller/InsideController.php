<?php

namespace BusyPHP\app\admin\controller;

use BusyPHP\helper\StringHelper;
use BusyPHP\Service;
use think\Container;

/**
 * admin内部基本控制器
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午6:33 下午 InsideController.php $
 * @internal
 */
class InsideController extends AdminController
{
    /**
     * @inheritDoc
     */
    protected function display($template = '', $charset = 'utf-8', $contentType = '', $content = '')
    {
        return parent::display($this->parseTemplate($template), $charset, $contentType, $content);
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
            $group = StringHelper::snake($group) . DIRECTORY_SEPARATOR;
        }
        $controller = StringHelper::snake($controller);
        
        $dir = $this->request->route(Service::ROUTE_VAR_DIR);
        if ($dir) {
            $dir = StringHelper::snake($dir) . DIRECTORY_SEPARATOR;
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
        if (0 === strpos($template, '@')) {
            return substr($template, 1);
        }
        
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
    
    
    /**
     * 获取自定义模板属性
     * @param string $key 自定义模板键
     * @param string $attr 属性名称
     * @param mixed  $default 默认值
     * @return mixed
     */
    protected function getUseTemplateAttr(string $key, string $attr, $default = null)
    {
        return $this->app->config->get("app.admin.template.{$key}.{$attr}", $default);
    }
    
    
    /**
     * 获取自定义模板
     * @param string $name 自定义模板键
     * @param string $defaultTemplate 默认魔板名称
     * @param array  $assignVars 给模板赋值的变量
     * @return string
     */
    protected function getUseTemplate(string $name, string $defaultTemplate = '', array $assignVars = []) : string
    {
        $template = $this->app->config->get("app.admin.template.{$name}", '');
        if (is_array($template)) {
            $assign   = $template['assign'] ?? '';
            $template = $template['path'] ?? '';
            $title    = $template['title'] ?? '';
            if ($title) {
                $this->setPageTitle($title);
            }
            
            if ($assign) {
                $assigns = Container::getInstance()->invokeFunction($assign, [$assignVars]);
                foreach ($assigns as $key => $item) {
                    $this->assign($key, $item);
                }
            }
        }
        
        foreach ($assignVars as $key => $item) {
            $this->assign($key, $item);
        }
        
        if ($template && !is_file($template)) {
            $template = "@{$template}";
        }
        
        return $template ?: $defaultTemplate;
    }
}