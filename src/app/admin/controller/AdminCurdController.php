<?php

namespace BusyPHP\app\admin\controller;

use BusyPHP\model\Field;
use BusyPHP\model;
use BusyPHP\helper\page\Page;
use BusyPHP\helper\util\Filter;
use BusyPHP\exception\AppException;
use Closure;
use think\Response;
use think\response\View;
use think\route\Url;

/**
 * 快速增删改查基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/3 下午12:18 下午 AdminCurdController.php $
 */
abstract class AdminCurdController extends AdminController
{
    //+--------------------------------------
    //| 回调方法名
    //+--------------------------------------
    /** 查询列表回调, 参数: $list, 返回: array 覆盖$list */
    const CALL_SELECT_LIST = 1;
    
    /** 遍历列表回调, 参数: $item, $index, 返回 array 覆盖$list */
    const CALL_SELECT_LIST_EACH = 2;
    
    /** 条件解析回调, 参数: $where, 返回 array 覆盖$where */
    const CALL_SELECT_WHERE = 3;
    
    /** 显示回调, 参数: 无, 返回 array 则向页面赋值$info变量*/
    const CALL_DISPLAY = 4;
    
    /** 批量操作遍历开始回调, 参数: $params */
    const CALL_BATCH_EACH_BEFORE = 5;
    
    /** 批量操作遍历中回调, 参数: $value, $key, 返回: 无 */
    const CALL_BATCH_EACH = 6;
    
    /** 批量操作遍历结束回调, 参数: $params, 返回: 字符串覆盖提示消息，否则提示操作成功 */
    const CALL_BATCH_EACH_AFTER = 7;
    
    //+--------------------------------------
    //| 私有参数
    //+--------------------------------------
    /**
     * 提交按钮名称
     * @var string
     */
    protected $submitName = '';
    
    /**
     * 显示模板名称
     * @var string
     */
    protected $templateName = '';
    
    /**
     * 回调集合
     * @var callable[]
     */
    private $callback = [];
    
    /**
     * 配置
     * @var array
     */
    private $options = [
        'select' => [
            'limit'  => 20,
            'order'  => '',
            'where'  => [],
            'simple' => false
        ],
        'batch'  => [
            'primary_key' => ''
        ]
    ];
    
    
    /**
     * 解析搜索条件
     * @return array
     */
    protected function parseSelectWhere()
    {
        $field    = $this->iRequest('field', 'trim');       // 搜索字段
        $word     = $this->iRequest('word', 'trim');        // 搜索词
        $accurate = $this->iRequest('accurate', 'intval');  // 是否精确搜索
        
        // 条件
        $where = $this->iRequest('static');
        $where = is_array($where) ? $where : [];  // 固定条件
        $where = array_map('trim', $where);
        
        // 搜索字段为空或搜索词为空
        if ($field && $word !== '') {
            // 精确搜索
            if ($accurate > 0) {
                $where[$field] = $word;
            } else {
                $searchWord    = Filter::searchWord($word);
                $where[$field] = ['like', "%{$searchWord}%"];
            }
        }
        
        // 合并搜索条件
        $where = array_merge($where, (is_array($this->options['select']['where']) ? $this->options['select']['where'] : []));
        
        // 查询条件解析回调
        if (isset($this->callback[self::CALL_SELECT_WHERE])) {
            $where = $this->trigger(self::CALL_SELECT_WHERE, [$where]);
            if (!is_array($where) && $where instanceof Field) {
                $where = $where->getWhere();
            }
        }
        
        $this->options['select']['where'] = $where;
        
        return $where;
    }
    
    
    /**
     * 设置查询条件
     * @param array|Field|callable $where 前置条件
     * @param callable             $callback 解析回调 callback(array $where) = array
     * @return $this
     */
    protected function setSelectWhere($where = [], $callback = null)
    {
        if (is_callable($where)) {
            $this->bind(self::CALL_SELECT_WHERE, $where);
        } else {
            if ($where instanceof Field) {
                $where = $where->getWhere();
            }
            $this->options['select']['where'] = $where;
            $this->bind(self::CALL_SELECT_WHERE, $callback);
        }
        
        return $this;
    }
    
    
    /**
     * 设置查询每页数量
     * @param false|int $limit false代表查询所有数据
     * @return $this
     */
    protected function setSelectLimit($limit)
    {
        $this->options['select']['limit'] = $limit;
        
        return $this;
    }
    
    
    /**
     * 设置查询排序条件
     * @param string $order
     * @return $this
     */
    protected function setSelectOrder($order)
    {
        $this->options['select']['order'] = $order;
        
        return $this;
    }
    
    
    /**
     * 设置分页查询是否启用简洁模式适用与大型数据
     * @param bool $simple
     * @return $this
     */
    protected function setSelectSimple(bool $simple)
    {
        $this->options['select']['simple'] = $simple;
        
        return $this;
    }
    
    
    /**
     * 查询操作
     * @param Model $model 模型
     * @param bool  $isAjax 是否ajax输出
     * @param bool  $isExtends
     * @return Response|View
     */
    protected function select($model, $isAjax = false, $isExtends = false)
    {
        $model->whereof($this->parseSelectWhere());
        
        
        // 定义排序条件
        if (!$this->options['select']['order']) {
            $this->options['select']['order'] = "{$model->getPk()} DESC";
        }
        $model->order($this->options['select']['order']);
        
        
        // 如果不是全部显示，则设置每页显示条数
        $page         = Page::getCurrentPage();
        $simple       = $this->options['select']['simple'];
        $defaultLimit = 20;
        if (false !== $limit = $this->options['select']['limit']) {
            $limit = $limit < 1 ? $defaultLimit : $limit;
            
            // 简洁模式
            if ($simple) {
                $model->limit(($page - 1) * $limit, $limit + 1);
            } else {
                $model->limit(($page - 1) * $limit, $limit);
            }
        }
        
        
        // 复制模型用于统计
        $totalModel = clone $model;
        
        // 执行查询
        $list = $isExtends ? $model->selectExtendList() : $model->selectList();
        $list = is_array($list) ? $list : [];
        
        // 条数统计
        $total = null;
        if (!$this->options['select']['simple']) {
            $totalModel->removeOption('limit');
            $totalModel->removeOption('order');
            $totalModel->removeOption('field');
            $totalModel->removeOption('page');
            $total = floatval($totalModel->count());
        }
        
        // 实例化分页
        $limit     = is_numeric($limit) ? $limit : 0;
        $paginator = $this->page($list, $limit, $page, $total, $simple);
        
        // 查询回调
        if (isset($this->callback[self::CALL_SELECT_LIST])) {
            $list = $this->trigger(self::CALL_SELECT_LIST, [$list]);
        }
        
        // 遍历回调
        if (isset($this->callback[self::CALL_SELECT_LIST_EACH])) {
            foreach ($list as $i => $r) {
                $list[$i] = $this->trigger(self::CALL_SELECT_LIST_EACH, [$r]);
            }
        }
        
        // ajax显示处理
        if ($isAjax) {
            // 遍历回调
            $this->assign('list', $list);
            
            return $this->success('', '', [
                'list'       => $this->fetch($this->templateName),
                'total'      => $total,
                'page'       => $paginator->render(),
                'total_page' => $limit > 0 ? ($simple ? 0 : $paginator->lastPage()) : 0
            ]);
        } else {
            $this->assign('list', $list);
            $this->assign('total', $total);
            $this->assign('page', $paginator->render());
            
            return $this->display();
        }
    }
    
    
    /**
     * 提交捕获
     * @param string  $method 捕获方式
     * @param Closure $callback 捕获回调, 参数: $data, 返回: 字符串覆盖提示消息，否则提示提交成功
     * @param Closure $display
     * @return Response
     */
    protected function submit($method, Closure $callback, Closure $display = null)
    {
        try {
            $status  = true;
            $message = '';
            $method  = strtoupper($method);
            if ('AJAX' === $method && $this->isAjax()) {
                $message = call_user_func_array($callback, [$this->iRequest()]);
            } elseif ('POST' === $method && $this->isPost()) {
                $message = call_user_func_array($callback, [$this->iPost()]);
            } elseif ('REQUEST' === $method) {
                $message = call_user_func_array($callback, [$this->iRequest()]);
            } else {
                $status = false;
            }
            
            if ($status) {
                if ($message instanceof Response) {
                    return $message;
                }
                
                return $this->success($message ?: '提交成功', $this->getRedirectUrl(), MESSAGE_SUCCESS_GOTO);
            } else {
                if ($display instanceof Closure) {
                    $view = call_user_func($display);
                    if ($view instanceof Response) {
                        return $view;
                    } elseif (!isset($this->callback[self::CALL_DISPLAY])) {
                        $this->assign('info', $view);
                    }
                }
                
                return $this->display();
            }
        } catch (\Exception $e) {
            return $this->error($e);
        }
    }
    
    
    /**
     * 批量操作
     * @param string $key
     * @param string $emptyMessage
     * @return Response
     */
    protected function batch($key = '', $emptyMessage = '')
    {
        try {
            $key   = $key ?: 'id';
            $value = $this->iRequest($key);
            if (!is_array($value)) {
                if (false === strpos($value, ',')) {
                    $params = [$value];
                } else {
                    $params = explode(',', $value);
                }
            } else {
                $params = $value;
            }
            
            $params = array_map('trim', $params);
            if (!$params) {
                throw new AppException($emptyMessage ?: '请选择要操作的信息');
            }
            
            // 遍历前回调
            $this->trigger(self::CALL_BATCH_EACH_BEFORE, [$params]);
            
            // 遍历回调
            if (isset($this->callback[self::CALL_BATCH_EACH])) {
                foreach ($params as $key => $value) {
                    $this->trigger(self::CALL_BATCH_EACH, [$value, $key]);
                }
            }
            
            // 遍历完成回调
            $message = $this->trigger(self::CALL_BATCH_EACH_AFTER, [$params]);
            
            return $this->success($message ?: '操作成功', $this->getRedirectUrl(), MESSAGE_SUCCESS_GOTO);
        } catch (\Exception $e) {
            return $this->error($e);
        }
    }
    
    
    /**
     * 获取返回地址
     * @return string
     */
    protected function getRedirectUrl()
    {
        $redirectUrl = session('admin_curd_redirect');
        session('admin_curd_redirect', null);
        
        return $redirectUrl ?: $_SERVER['HTTP_REFERER'];
    }
    
    
    /**
     * 设置执行操作后返回地址
     * @param string|true|null $url TRUE则自动获取，null清空记录, 其它为返回地址
     */
    protected function setRedirectUrl($url = true)
    {
        if (!is_string($url)) {
            $url = (string) $url;
        }
        if (true === $url) {
            $url = $_SERVER['HTTP_REFERER'];
        }
        
        if ($url instanceof Url) {
            $url = (string) $url;
        }
        
        session('admin_curd_redirect', $url);
    }
    
    
    /**
     * 绑定回调
     * @param int      $name 回调名
     * @param callable $callback 回调方法
     * @return $this
     */
    protected function bind($name, $callback)
    {
        $this->callback[$name] = $callback;
        
        return $this;
    }
    
    
    /**
     * 触发回调
     * @param int   $name 回调名
     * @param mixed $args 回调参数
     * @return mixed
     */
    protected function trigger($name, $args = [])
    {
        if (isset($this->callback[$name])) {
            return call_user_func_array($this->callback[$name], $args);
        }
        
        return null;
    }
    
    
    protected function fetch($templateFile = '', $content = '')
    {
        return parent::fetch($this->init($templateFile), $content);
    }
    
    
    protected function display($template = '', $charset = 'utf-8', $contentType = '', $content = '')
    {
        return parent::display($this->init($template), $charset, $contentType, $content);
    }
    
    
    /**
     * 初始化视图
     * @param $template
     * @return string
     */
    protected function init($template)
    {
        // 触发显示回调
        if ($result = $this->trigger(self::CALL_DISPLAY)) {
            $this->assign('info', $result);
        }
        
        // 提交按钮名称
        if ($this->submitName) {
            $this->assign('submit_name', $this->submitName);
        }
        
        if (!$template && $this->templateName) {
            $template = $this->templateName;
        }
        
        return $template;
    }
}