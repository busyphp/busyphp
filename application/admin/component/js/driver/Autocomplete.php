<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\js\driver;

use BusyPHP\app\admin\component\js\Driver;
use BusyPHP\app\admin\component\js\driver\autocomplete\AutocompleteHandler;
use BusyPHP\app\admin\component\js\driver\autocomplete\AutocompleteNode;
use BusyPHP\app\admin\component\js\traits\Lists;
use BusyPHP\app\admin\component\js\traits\ModelOrder;
use BusyPHP\app\admin\component\js\traits\ModelQuery;
use BusyPHP\app\admin\component\js\traits\ModelSelect;
use BusyPHP\helper\FilterHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\model\Entity;
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
class Autocomplete extends Driver implements ContainerInterface
{
    use ModelSelect;
    use ModelOrder;
    use ModelQuery;
    use Lists;
    
    /**
     * 显示的字段
     * @var string
     */
    protected $textField;
    
    /**
     * 搜索关键词或默认值
     * @var string
     */
    protected $word;
    
    /**
     * 最大条数限制，0为不限
     * @var int
     */
    protected $limit;
    
    
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->textField = $this->request->param('text_field/s', '', 'trim');
        $this->limit     = $this->request->param('limit/d', 0);
        $this->word      = $this->request->param('word/s', '', 'trim');
        
        $this->textField = $this->textField ?: 'name';
        $this->limit     = $this->limit < 0 ? 20 : $this->limit;
    }
    
    
    /**
     * 设置查询关键词
     * @param string $word
     * @return static
     */
    public function setWord(string $word) : static
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
     * 设置查询限制条数，设为0则不限
     * @param int $limit
     * @return static
     */
    public function setLimit(int $limit) : static
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
     * 设置选项文本字段
     * @param string|Entity $textField
     * @return static
     */
    public function setTextField($textField) : static
    {
        $this->textField = (string) $textField;
        
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
     * 构建JS组件数据
     * @return null|array
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function build() : ?array
    {
        $this->prepareHandler();
        
        if ($this->model && is_null($this->list)) {
            // 自定义查询条件
            if ($this->modelQuery() !== false && $this->word !== '') {
                $this->model->whereLike($this->textField, '%' . FilterHelper::searchWord($this->word) . '%');
            }
            
            // 限制条数
            if ($this->limit > 0) {
                $this->model->limit($this->limit);
            }
            
            $this->list = $this->modelOrder()->modelSelect();
        }
        
        // 数据处理
        if (!$this->handleList()) {
            return null;
        }
        
        $data  = [];
        $index = 0;
        foreach ($this->list as $item) {
            $node   = AutocompleteNode::init($item);
            $result = null;
            if ($this->handler) {
                $result = $this->handler->item($node, $item, $index);
            } elseif ($this->itemCallback) {
                $result = call_user_func_array($this->itemCallback, [$node, $item, $index]);
            } else {
                $node->setText($item[$this->textField] ?? '');
            }
            $index++;
            
            if ($result === false || $node->getText() === '') {
                continue;
            }
            $data[] = $node;
        }
        
        return [
            'results' => $data
        ];
    }
}