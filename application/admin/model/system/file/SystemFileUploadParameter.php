<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\file;

use BusyPHP\upload\interfaces\BindDriverParameterInterface;

/**
 * 系统文件上传参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/22 9:44 PM SystemFileUploadParameter.php $
 */
class SystemFileUploadParameter
{
    /** @var BindDriverParameterInterface */
    private $parameter;
    
    /** @var string */
    private $classType = '';
    
    /** @var string */
    private $classValue = '';
    
    /** @var int */
    private $userId = 0;
    
    /** @var string */
    private $disk = '';
    
    
    /**
     * 构造函数
     * @param BindDriverParameterInterface $parameter 上传驱动参数模版
     */
    public function __construct(BindDriverParameterInterface $parameter)
    {
        $this->parameter = $parameter;
    }
    
    
    /**
     * 获取上传驱动参数模版
     * @return BindDriverParameterInterface
     */
    public function getParameter() : BindDriverParameterInterface
    {
        return $this->parameter;
    }
    
    
    /**
     * 获取文件分类
     * @return string
     */
    public function getClassType() : string
    {
        return $this->classType;
    }
    
    
    /**
     * 设置文件分类
     * @param string $classType
     * @return $this
     */
    public function setClassType(string $classType) : self
    {
        $this->classType = $classType;
        
        return $this;
    }
    
    
    /**
     * 获取文件分类业务参数
     * @return string
     */
    public function getClassValue() : string
    {
        return $this->classValue;
    }
    
    
    /**
     * 设置文件分类业务参数
     * @param string $classValue
     * @return $this
     */
    public function setClassValue(string $classValue) : self
    {
        $this->classValue = $classValue;
        
        return $this;
    }
    
    
    /**
     * 获取用户ID
     * @return int
     */
    public function getUserId() : int
    {
        return $this->userId;
    }
    
    
    /**
     * 设置用户ID
     * @param int $userId
     * @return $this
     */
    public function setUserId(int $userId) : self
    {
        $this->userId = $userId;
        
        return $this;
    }
    
    
    /**
     * 获取磁盘名称
     * @return string
     */
    public function getDisk() : string
    {
        return $this->disk;
    }
    
    
    /**
     * 指定磁盘名称
     * @param string $disk
     * @return $this
     */
    public function setDisk(string $disk) : self
    {
        $this->disk = $disk;
        
        return $this;
    }
}