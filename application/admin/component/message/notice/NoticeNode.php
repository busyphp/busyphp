<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\message\notice;

use BusyPHP\model\ObjectOption;
use think\route\Url;

/**
 * 消息通知消息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/17 下午9:17 下午 NoticeNode.php $
 */
class NoticeNode extends ObjectOption
{
    /**
     * 消息ID
     * @var string
     */
    public $id = '';
    
    /**
     * 消息标题
     * @var string
     */
    public $title = '';
    
    /**
     * 消息描述
     * @var string
     */
    public $desc = '';
    
    /**
     * 是否已读
     * @var bool
     */
    public $read = false;
    
    /**
     * 创建时间
     * @var string
     */
    public $create_time = '';
    
    /**
     * 操作URL
     * @var string
     */
    public $url = '';
    
    /**
     * 操作图标
     * @var string
     */
    public $icon = '';
    
    /**
     * 图标颜色
     * @var string
     */
    public $icon_color = '';
    
    /**
     * 自定义标签属性
     * @var array
     */
    public $attrs = [];
    
    
    /**
     * 获取消息ID
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }
    
    
    /**
     * 设置消息ID
     * @param mixed $id
     * @return static
     */
    public function setId($id) : static
    {
        $this->id = (string) $id;
        
        return $this;
    }
    
    
    /**
     * 获取消息标题
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }
    
    
    /**
     * 设置消息标题
     * @param mixed $title
     * @return static
     */
    public function setTitle($title) : static
    {
        $this->title = (string) $title;
        
        return $this;
    }
    
    
    /**
     * 获取消息描述
     * @return string
     */
    public function getDesc() : string
    {
        return $this->desc;
    }
    
    
    /**
     * 设置消息描述
     * @param mixed $desc
     * @return static
     */
    public function setDesc($desc) : static
    {
        $this->desc = (string) $desc;
        
        return $this;
    }
    
    
    /**
     * 是否已读
     * @return bool
     */
    public function isRead() : bool
    {
        return $this->read;
    }
    
    
    /**
     * 设置是否已读
     * @param bool $read
     * @return static
     */
    public function setRead(bool $read) : static
    {
        $this->read = $read;
        
        return $this;
    }
    
    
    /**
     * 获取创建时间
     * @return string
     */
    public function getCreateTime() : string
    {
        return $this->create_time;
    }
    
    
    /**
     * 设置创建时间
     * @param string $create_time
     * @return static
     */
    public function setCreateTime(string $create_time) : static
    {
        $this->create_time = $create_time;
        
        return $this;
    }
    
    
    /**
     * 获取操作URL
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }
    
    
    /**
     * 设置操作URL
     * @param string|Url $url
     * @return static
     */
    public function setUrl($url) : static
    {
        $this->url = (string) $url;
        
        return $this;
    }
    
    
    /**
     * 获取图标
     * @return string
     */
    public function getIcon() : string
    {
        return $this->icon;
    }
    
    
    /**
     * 图标颜色
     * @return string
     */
    public function getIconColor() : string
    {
        return $this->icon_color;
    }
    
    
    /**
     * 设置图标颜色
     * @param string $icon_color
     * @return static
     */
    public function setIconColor(string $icon_color) : static
    {
        $this->icon_color = $icon_color;
        
        return $this;
    }
    
    
    /**
     * 设置图标类名
     * @param string $icon 图标类或图片地址
     * @return static
     */
    public function setIcon(string $icon) : static
    {
        $this->icon = $icon;
        
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
}