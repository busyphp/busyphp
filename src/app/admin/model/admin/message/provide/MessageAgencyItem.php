<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\message\provide;

/**
 * 待办任务item规定
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/17 下午9:17 下午 MessageAgencyItem.php $
 */
class MessageAgencyItem
{
    /** @var string */
    private $id = '';
    
    /** @var string */
    private $title = '';
    
    /** @var string */
    private $desc = '';
    
    /** @var string */
    private $operateUrl = '';
    
    
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
     */
    public function setId($id) : void
    {
        $this->id = trim((string) $id);
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
     */
    public function setTitle($title) : void
    {
        $this->title = trim((string) $title);
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
     */
    public function setDesc($desc) : void
    {
        $this->desc = trim((string) $desc);
    }
    
    
    /**
     * 获取待办操作URL
     * @return string
     */
    public function getOperateUrl() : string
    {
        return $this->operateUrl;
    }
    
    
    /**
     * 设置待办操作URL
     * @param string $operateUrl
     */
    public function setOperateUrl($operateUrl) : void
    {
        $this->operateUrl = trim((string) $operateUrl);
    }
}