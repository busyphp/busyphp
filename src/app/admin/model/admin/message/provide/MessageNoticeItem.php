<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\message\provide;

/**
 * 消息通知item规定
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/17 下午9:17 下午 MessageNoticeItem.php $
 */
class MessageNoticeItem
{
    /** @var string */
    private $id = '';
    
    /** @var string */
    private $title = '';
    
    /** @var string */
    private $desc = '';
    
    /** @var bool */
    private $read = false;
    
    /** @var int */
    private $readTime = 0;
    
    /** @var int */
    private $createTime = 0;
    
    /** @var string */
    private $operateUrl = '';
    
    /** @var string */
    private $icon = '';
    
    /** @var string */
    private $imageUrl = '';
    
    /** @var bool */
    private $iconClass = false;
    
    /** @var string */
    private $iconColor = '';
    
    
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
     * @param string $id
     */
    public function setId($id) : void
    {
        $this->id = trim($id);
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
     * @param string $title
     */
    public function setTitle($title) : void
    {
        $this->title = trim($title);
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
     * @param string $desc
     */
    public function setDesc($desc) : void
    {
        $this->desc = trim($desc);
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
     */
    public function setRead(bool $read) : void
    {
        $this->read = $read;
    }
    
    
    /**
     * 获取已读时间
     * @return int
     */
    public function getReadTime() : int
    {
        return $this->readTime;
    }
    
    
    /**
     * 设置已读时间
     * @param int $readTime
     */
    public function setReadTime($readTime) : void
    {
        $this->readTime = intval($readTime);
    }
    
    
    /**
     * 获取创建时间
     * @return int
     */
    public function getCreateTime() : int
    {
        return $this->createTime;
    }
    
    
    /**
     * 设置创建时间
     * @param int $createTime
     */
    public function setCreateTime($createTime) : void
    {
        $this->createTime = intval($createTime);
    }
    
    
    /**
     * 获取操作URL
     * @return string
     */
    public function getOperateUrl() : string
    {
        return $this->operateUrl;
    }
    
    
    /**
     * 设置操作URL
     * @param string $operateUrl
     */
    public function setOperateUrl($operateUrl) : void
    {
        $this->operateUrl = trim($operateUrl);
    }
    
    
    /**
     * 获取图标类名
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
        return $this->iconColor;
    }
    
    
    /**
     * 获取图标地址
     * @return string
     */
    public function getImageUrl() : string
    {
        return $this->imageUrl;
    }
    
    
    /**
     * 图标是否图标类
     * @return bool
     */
    public function isIconClass() : bool
    {
        return $this->iconClass;
    }
    
    
    /**
     * 设置图标类名
     * @param bool   $iconClass 是否ICON图标类
     * @param string $icon 图标类或图片地址
     * @param string $iconColor 图标颜色
     */
    public function setIcon(bool $iconClass, $icon, $iconColor = '') : void
    {
        $this->iconClass = $iconClass;
        if ($this->iconClass) {
            $this->icon      = trim($icon);
            $this->iconColor = $iconColor;
        } else {
            $this->imageUrl = trim($icon);
        }
    }
}