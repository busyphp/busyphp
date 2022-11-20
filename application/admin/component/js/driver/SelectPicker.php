<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\js\driver;

use BusyPHP\app\admin\component\js\Driver;
use BusyPHP\app\admin\component\js\driver\SelectPicker\SelectPickerHandler;
use BusyPHP\app\admin\component\js\driver\SelectPicker\SelectPickerNode;
use BusyPHP\app\admin\component\js\traits\Lists;
use BusyPHP\app\admin\component\js\traits\ModelOrder;
use BusyPHP\app\admin\component\js\traits\ModelQuery;
use BusyPHP\app\admin\component\js\traits\ModelSelect;
use BusyPHP\app\admin\component\js\traits\ModelTotal;
use BusyPHP\helper\FilterHelper;
use BusyPHP\model\Entity;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * JS组件[busyAdmin.plugins.SelectPicker]
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 20:14 SelectPicker.php $
 * @property SelectPickerHandler                                                          $handler
 * @property callable($node SelectPickerNode, $item mixed, $group bool, $index int):mixed $itemCallback
 * @method SelectPicker handler(SelectPickerHandler $handler)
 */
class SelectPicker extends Driver
{
    use ModelSelect;
    use ModelOrder;
    use ModelTotal;
    use ModelQuery;
    use Lists;
    
    /**
     * 请求类型
     * @var bool
     */
    public $value;
    
    /**
     * 分页
     * @var int
     */
    public $page;
    
    /**
     * 每页显示条数
     * @var int
     */
    public $length;
    
    /**
     * id字段
     * @var string
     */
    public $idField;
    
    /**
     * text字段
     * @var string
     */
    public $textField;
    
    /**
     * 搜索关键词或默认值
     * @var mixed
     */
    public $word;
    
    
    final protected static function defineAbstract() : string
    {
        return self::class;
    }
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->value     = $this->request->param('action/s', '', 'trim') === 'value';
        $this->page      = $this->request->param('page/d', 1);
        $this->length    = $this->request->param('length/d', 0);
        $this->idField   = $this->request->param('id_field/s', '', 'trim');
        $this->textField = $this->request->param('text_field/s', '', 'trim');
        $this->word      = $this->request->param('word', '');
        
        $this->idField   = $this->idField ?: 'id';
        $this->textField = $this->textField ?: 'name';
        $this->length    = $this->length < 0 ? 20 : $this->length;
        $this->word      = is_array($this->word) ? FilterHelper::trimArray($this->word) : trim((string) $this->word);
        $this->page      = max($this->page, 1);
    }
    
    
    /**
     * 是否获取选中值
     * @return bool
     */
    public function isValue() : bool
    {
        return $this->value;
    }
    
    
    /**
     * 获取每页显示条数
     * @return int
     */
    public function getLength() : int
    {
        return $this->length;
    }
    
    
    /**
     * 设置每页显示条数，0为不限
     * @param int $length
     * @return SelectPicker
     */
    public function setLength(int $length) : self
    {
        $this->length = $length < 0 ? 20 : $length;
        
        return $this;
    }
    
    
    /**
     * 获取选项ID字段
     * @return string
     */
    public function getIdField() : string
    {
        return $this->idField;
    }
    
    
    /**
     * 设置选项ID字段
     * @param string|Entity $idField
     * @return SelectPicker
     */
    public function setIdField($idField) : self
    {
        $this->idField = (string) $idField;
        
        return $this;
    }
    
    
    /**
     * 获取选项文本字段
     * @return string
     */
    public function getTextField() : string
    {
        return $this->textField;
    }
    
    
    /**
     * 设置选项文本字段
     * @param string|Entity $textField
     * @return $this
     */
    public function setTextField($textField) : self
    {
        $this->textField = (string) $textField;
        
        return $this;
    }
    
    
    /**
     * 获取查询的关键词
     * @return string|string[]
     */
    public function getWord()
    {
        return $this->word;
    }
    
    
    /**
     * 设置查询的关键词或默认值
     * @param string|string[] $word
     * @return $this
     */
    public function setWord($word) : self
    {
        $this->word = $word;
        
        return $this;
    }
    
    
    /**
     * 构建JS组件数据
     * @return null|array
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function build() : ?array
    {
        $total = 0;
        $this->prepareHandler();
        
        if ($this->model && is_null($this->list)) {
            // 处理查询回调
            $query = $this->modelQuery();
            
            // 查询选中值
            if ($this->value) {
                if (false !== $query && ($this->word || (is_string($this->word) && $this->word !== ''))) {
                    if (is_array($this->word)) {
                        $this->model->whereIn($this->idField, $this->word);
                    } else {
                        $this->model->where($this->idField, $this->word);
                    }
                }
            }
            
            // else
            // 查询选项数据集
            else {
                if (false !== $query && $this->word !== '') {
                    $this->model->whereLike($this->textField, '%' . FilterHelper::searchWord($this->word) . '%');
                }
                
                // 统计总数和分页
                if ($this->length > 0) {
                    $total = $this->modelTotal();
                    $this->model->page($this->page, $this->length);
                }
            }
            
            $this->list = $this->modelOrder()->modelSelect();
        }
        
        // 如果传入了静态数据
        // 且需要获取选中项
        elseif (!is_null($this->list) && $this->value && ($this->word || (is_string($this->word) && $this->word !== ''))) {
            $list = $this->list instanceof Collection ? $this->list : Collection::make($this->list);
            if (is_array($this->word)) {
                $list = $list->whereIn($this->idField, $this->word);
            } else {
                $list = $list->where($this->idField, $this->word);
            }
            $this->list = $this->list instanceof Collection ? Collection::make(array_values($list->all())) : array_values($list->all());
        }
        
        // 数据集处理
        if (!$this->handleList()) {
            return null;
        }
        
        // 节点处理
        $index = 0;
        $data  = [];
        foreach ($this->list as $item) {
            $node = SelectPickerNode::init($item);
            
            // 节点处理回调
            $result = null;
            if ($this->handler) {
                $result = $this->handler->item($node, $item, false, $index);
            } elseif ($this->itemCallback) {
                $result = call_user_func_array($this->itemCallback, [$node, $item, false, $index]);
            } else {
                $node->setId($item[$this->getIdField()] ?? '');
                $node->setText($item[$this->getTextField()] ?? '');
            }
            $index++;
            
            if (false === $result || $node->getText() === '' || $node->getId() === '') {
                continue;
            }
            $data[] = $node;
        }
        
        return [
            'results'    => $data,
            'pagination' => [
                'more' => $this->page * $this->length < $total
            ]
        ];
    }
}