<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\component\message\todo;

use BusyPHP\app\admin\component\message\Todo;
use BusyPHP\model\ObjectOption;
use think\route\Url;

/**
 * 待办任务消息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/17 下午9:17 下午 TodoNode.php $
 */
class TodoNode extends ObjectOption
{
    /**
     * 排序下标
     * @var int
     */
    public $sort = -1;
    
    /**
     * 待办ID
     * @var string
     */
    public $id = '';
    
    /**
     * 待办标题
     * @var string
     */
    public $title = '';
    
    /**
     * 待办描述
     * @var string
     */
    public $desc = '';
    
    /**
     * 操作URL
     * @var string
     */
    public $url = '';
    
    /**
     * 待办数
     * @var int
     */
    public $total = 0;
    
    /**
     * 待办级别
     * @var int
     */
    public $level = 0;
    
    /**
     * 待办级别名称
     * @var string
     */
    public $level_name = '';
    
    /**
     * 待办级别样式
     * @var string
     */
    public $level_style = '';
    
    /**
     * 自定义标签属性
     * @var array
     */
    public $attrs = [];
    
    
    public function __construct()
    {
        $this->setLevel(Todo::LEVEL_DEFAULT);
        
        parent::__construct();
    }
    
    
    /**
     * 获取待办ID
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }
    
    
    /**
     * 设置待办ID
     * @param string $id
     * @return static
     */
    public function setId($id) : static
    {
        $this->id = (string) $id;
        
        return $this;
    }
    
    
    /**
     * 获取待办标题
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }
    
    
    /**
     * 设置待办标题
     * @param string $title
     * @return static
     */
    public function setTitle($title) : static
    {
        $this->title = (string) $title;
        
        return $this;
    }
    
    
    /**
     * 获取待办描述
     * @return string
     */
    public function getDesc() : string
    {
        return $this->desc;
    }
    
    
    /**
     * 设置待办描述
     * @param string $desc
     * @return static
     */
    public function setDesc($desc) : static
    {
        $this->desc = (string) $desc;
        
        return $this;
    }
    
    
    /**
     * 获取待办操作URL
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }
    
    
    /**
     * 设置待办操作URL
     * @param string|Url $url
     * @return static
     */
    public function setUrl($url) : static
    {
        $this->url = (string) $url;
        
        return $this;
    }
    
    
    /**
     * 获取待办数
     * @return int
     */
    public function getTotal() : int
    {
        return $this->total;
    }
    
    
    /**
     * 设置待办数
     * @param int $total
     * @return static
     */
    public function setTotal(int $total) : static
    {
        $this->total = $total;
        
        return $this;
    }
    
    
    /**
     * 获取标签自定义属性
     * @return array
     */
    public function getAttrs() : array
    {
        return $this->attrs;
    }
    
    
    /**
     * 设置标签自定义属性
     * @param array $attrs
     * @return static
     */
    public function setAttrs(array $attrs) : static
    {
        $this->attrs = $attrs;
        
        return $this;
    }
    
    
    /**
     * 添加标签自定义属性
     * @param string $name 属性名称
     * @param mixed  $value 属性值
     * @return static
     */
    public function addAttr(string $name, $value) : static
    {
        $this->attrs[$name] = $value;
        
        return $this;
    }
    
    
    /**
     * 获取待办级别
     * @return int
     */
    public function getLevel() : int
    {
        return $this->level;
    }
    
    
    /**
     * 设置待办级别
     * @param int $level
     * @return static
     */
    public function setLevel(int $level) : static
    {
        $this->level = $level;
        
        if ($level === Todo::LEVEL_DEFAULT) {
            $this->level_name  = '';
            $this->level_style = '';
        } else {
            $config            = Todo::getLevels($level) ?? [];
            $this->level_name  = $config['name'] ?? '';
            $this->level_style = $config['style'] ?? '';
        }
        
        return $this;
    }
    
    
    /**
     * 设置排序下标
     * @param int $sort
     * @return static
     */
    public function setSort(int $sort) : static
    {
        $this->sort = $sort;
        
        return $this;
    }
    
    
    /**
     * 获取排序下标
     * @return int
     */
    public function getSort() : int
    {
        return $this->sort;
    }
}