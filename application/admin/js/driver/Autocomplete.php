<?php

namespace BusyPHP\app\admin\js\driver;

use BusyPHP\app\admin\js\Driver;
use BusyPHP\app\admin\js\driver\autocomplete\AutocompleteHandler;
use BusyPHP\helper\FilterHelper;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use Closure;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * JS组件[busyAdmin.plugins.Autocomplete]
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/12 23:34 Autocomplete.php $
 * @property AutocompleteHandler $handler
 * @method Autocomplete handler(AutocompleteHandler $handler)
 */
class Autocomplete extends Driver
{
    /**
     * 查询的字段
     * @var string
     */
    protected $field;
    
    /**
     * 显示的字段
     * @var string
     */
    protected $text;
    
    /**
     * 排序方式
     * @var array
     */
    protected $order;
    
    /**
     * 搜索关键词或默认值
     * @var string
     */
    protected $word;
    
    /**
     * 是否查询扩展数据
     * @var bool
     */
    protected $extend;
    
    /**
     * 最大条数限制，0为不限
     * @var int
     */
    protected $limit;
    
    /**
     * 模糊匹配方向
     * @var string
     */
    protected $direction;
    
    /**
     * 数据集
     * @var array|Collection|null
     */
    protected $list = null;
    
    /**
     * 查询处理回调
     * @var null|callable($model Model):void
     */
    protected $queryCallback;
    
    /**
     * item处理回调
     * @var null|callable($item):string
     */
    protected $itemCallback;
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->field     = $this->request->param('field/s', '', 'trim'); // TODO JS组件目前不支持该参数
        $this->text      = $this->request->param('text_field/s', '', 'trim');
        $this->order     = $this->request->param('order/s', '', 'trim');
        $this->extend    = $this->request->param('extend/b', false);
        $this->limit     = $this->request->param('limit/d', 0);
        $this->word      = $this->request->param('word/s', '', 'trim');
        $this->direction = $this->request->param('direction/s', '', 'trim,strtolower'); // TODO JS组件目前不支持该参数
        
        $this->text  = $this->text ?: 'name';
        $this->field = $this->field ?: $this->text;
        $this->limit = $this->limit < 0 ? 20 : $this->limit;
    }
    
    
    /**
     * 指定数据集
     * @param array|Collection|callable($list array):array $list 数据集 或 数据集的Item处理回调
     * @param null|callable($item array|Field):string      $callback 数据集的Item处理回调
     * @return $this
     */
    public function list($list, callable $callback = null) : self
    {
        if ($list instanceof Closure) {
            $callback = $list;
            $list     = null;
        }
        
        $this->list         = $list;
        $this->itemCallback = $callback;
        
        return $this;
    }
    
    
    /**
     * 指定查询处理回调
     * @param null|callable($model Model):void $callback 查询处理回调
     * @return $this
     */
    public function query(callable $callback) : self
    {
        $this->queryCallback = $callback;
        
        return $this;
    }
    
    
    /**
     * 设置查询字段
     * @param string|Entity $field
     * @return $this
     */
    public function field($field) : self
    {
        $this->field = (string) $field;
        
        return $this;
    }
    
    
    /**
     * 设置查询关键词
     * @param string $word
     * @return $this
     */
    public function setWord(string $word) : self
    {
        $this->word = $word;
        
        return $this;
    }
    
    
    /**
     * 获取查询关键词
     * @return string
     */
    public function getWord() : string
    {
        return $this->word;
    }
    
    
    /**
     * 设置模糊匹配方向
     * @param string $direction
     * @return $this
     */
    public function setDirection(string $direction) : self
    {
        $this->direction = strtolower($direction);
        
        return $this;
    }
    
    
    /**
     * 获取模糊匹配方向
     * @return string
     */
    public function getDirection() : string
    {
        return $this->direction;
    }
    
    
    /**
     * 设置是否查询扩展信息
     * @param bool $extend
     * @return $this
     */
    public function setExtend(bool $extend) : self
    {
        $this->extend = $extend;
        
        return $this;
    }
    
    
    /**
     * 是否查询扩展信息
     * @return bool
     */
    public function isExtend() : bool
    {
        return $this->extend;
    }
    
    
    /**
     * 设置查询限制条数，设为0则不限
     * @param int $limit
     * @return $this
     */
    public function setLimit(int $limit) : self
    {
        $this->limit = $limit;
        
        return $this;
    }
    
    
    /**
     * 获取查询限制条数
     * @return int
     */
    public function getLimit() : int
    {
        return $this->limit;
    }
    
    
    /**
     * 设置排序字段
     * @param string|Entity $order
     * @return $this
     */
    public function setOrder($order) : self
    {
        $this->order = (string) $order;
        
        return $this;
    }
    
    
    /**
     * 获取排序字段
     * @return string
     */
    public function getOrder() : string
    {
        return $this->order;
    }
    
    
    /**
     * 设置选项文本字段
     * @param string|Entity $text
     * @return $this
     */
    public function setText($text) : self
    {
        $this->text = (string) $text;
        
        return $this;
    }
    
    
    /**
     * 获取选项文本字段
     * @return string
     */
    public function getText() : string
    {
        return $this->text;
    }
    
    
    /**
     * 构建JS组件数据
     * @return null|array
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function build() : ?array
    {
        if ($this->handler) {
            $this->handler->prepare($this);
        }
        
        if ($this->model && is_null($this->list)) {
            if ($this->word !== '') {
                if ($this->direction == 'left') {
                    $word = FilterHelper::searchWord($this->word, false) . '%';
                } elseif ($this->direction == 'right') {
                    $word = '%' . FilterHelper::searchWord($this->word, false);
                } else {
                    $word = '%' . FilterHelper::searchWord($this->word) . '%';
                }
                
                $this->model->whereLike($this->castField($this->field), $word);
            }
            
            // 自定义查询条件
            if ($this->handler) {
                $this->handler->query();
            } elseif ($this->queryCallback) {
                call_user_func_array($this->queryCallback, [$this->model]);
            }
            
            // 限制条数
            if ($this->limit > 0) {
                $this->model->limit($this->limit);
            }
            
            // 排序
            if ($this->order && !$this->model->getOptions('order')) {
                $this->model->order($this->order);
            }
            
            if ($this->extend) {
                $this->list = $this->model->selectExtendList();
            } else {
                $this->list = $this->model->selectList();
            }
        }
        
        if (is_null($this->list)) {
            return null;
        }
        
        $data  = [];
        $index = 0;
        foreach ($this->list as $item) {
            if ($this->handler) {
                $text = $this->handler->item($item, $index);
            } elseif ($this->itemCallback) {
                $text = call_user_func_array($this->itemCallback, [$item, $index]);
            } else {
                $text = $item[$this->text] ?? '';
            }
            if ($text !== '') {
                $data[] = ['text' => $text];
            }
            
            $index++;
        }
        
        return [
            'results' => $data
        ];
    }
}