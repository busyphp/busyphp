<?php

namespace BusyPHP\app\admin\js;

use BusyPHP\helper\util\Filter;
use BusyPHP\Model;
use BusyPHP\Request;
use think\Container;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * Table Js 插件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/7 下午下午8:32 TablePlugin.php $
 */
class TablePlugin
{
    /**
     * @var Request
     */
    private $request;
    
    /**
     * 排序字段
     * @var string
     */
    public $sort;
    
    /**
     * 排序方式
     * @var string
     */
    public $order;
    
    /**
     * 偏移量
     * @var int
     */
    public $offset;
    
    /**
     * 每页显示条数
     * @var int
     */
    public $limit;
    
    /**
     * 搜索的字段
     * @var string
     */
    public $field;
    
    /**
     * 搜索的内容
     * @var string
     */
    public $word;
    
    /**
     * 是否查询扩展数据
     * @var bool
     */
    public $isExtend;
    
    
    public function __construct()
    {
        $this->request  = Container::getInstance()->make(Request::class);
        $this->isExtend = $this->request->get('extend', 0, 'intval') > 0;
        $this->limit    = $this->request->get('limit', 0, 'intval');
        $this->offset   = $this->request->get('offset', 0, 'intval');
        $this->word     = $this->request->get('word', '', 'trim');
        $this->field    = $this->request->get('field', '', 'trim');
        $this->order    = $this->request->get('order', '', 'trim');
        $this->sort     = $this->request->get('sort', '', 'trim');
    }
    
    
    /**
     * 自动构建数据
     * @param Model|null $model
     * @return array|null
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function build(?Model $model = null) : ?array
    {
        if (!$model) {
            $model = $this->request->post('model', '', 'trim');
            $model = str_replace('/', '\\', $model);
            $model = class_exists($model) ? new $model() : null;
        }
        
        if ($model instanceof Model) {
            if ($this->word) {
                $model->whereLike($this->field, '%' . Filter::searchWord($this->word) . '%');
            }
            
            $model->order($this->sort, $this->order);
            
            // 不限制条数
            if ($this->limit > 0) {
                $model->limit($this->offset, $this->limit);
                $totalModel = clone $model;
                $totalModel->limit(0);
                $total = $totalModel->count();
            } else {
                $total = 0;
            }
            
            if ($this->isExtend) {
                $list = $model->selectExtendList();
            } else {
                $list = $model->selectList();
            }
            
            return $this->result($list, $this->limit > 0 ? $total : count($list));
        }
        
        return null;
    }
    
    
    /**
     * 返回数据处理
     * @param array $data 数据
     * @param int   $total 总条数
     * @return array
     */
    public function result(array $data, int $total) : array
    {
        return [
            'total'            => $total,
            'totalNotFiltered' => $total,
            'rows'             => $data,
        ];
    }
}