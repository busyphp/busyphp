<?php

namespace BusyPHP\app\admin\event;

use BusyPHP\App;
use BusyPHP\Request;
use think\facade\Event;
use think\facade\View;

/**
 * 后台管理面板事件基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/10/13 下午1:21 下午 AdminPanelEvent.php $
 */
abstract class AdminPanelDisplayEvent
{
    /**
     * @var App
     */
    protected $app;
    
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @var array
     */
    private $vars = [];
    
    
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;
    }
    
    
    /**
     * 执行事件
     * @param $params
     * @return AdminPanelDisplayEventResult
     */
    public function handle($params)
    {
        $data = $this->onRender($params);
        $res  = new AdminPanelDisplayEventResult();
        if (preg_match('/<extend-block.*name="content">(.*?)<\/extend-block>/is', $data, $match)) {
            $res->setContent($match[1]);
        }
        if (preg_match('/<extend-block.*name="head">(.*?)<\/extend-block>/is', $data, $match)) {
            $res->setHead($match[1]);
        }
        if (preg_match('/<extend-block.*name="foot">(.*?)<\/extend-block>/is', $data, $match)) {
            $res->setFoot($match[1]);
        }
        
        return $res;
    }
    
    
    /**
     * 渲染HTML模板并返回渲染后的字符
     * @param mixed $params 参数
     * @return string
     */
    abstract protected function onRender($params);
    
    
    /**
     * 模板赋值
     * @param $name
     * @param $value
     */
    protected function assign($name, $value)
    {
        $this->vars[$name] = $value;
    }
    
    
    /**
     * 获取模板内容
     * @param string $template
     * @return string
     */
    protected function fetch($template = '')
    {
        return View::fetch($template, $this->vars);
    }
    
    
    /**
     * 触发模板显示事件
     * @param string     $event
     * @param mixed|null $params
     * @return AdminPanelDisplayEventResult
     */
    public static function triggerEvent($event, $params = null) : AdminPanelDisplayEventResult
    {
        $result = Event::trigger($event, $params, true);
        
        if (!$result instanceof AdminPanelDisplayEventResult) {
            $result = new AdminPanelDisplayEventResult();
        }
        
        return $result;
    }
}