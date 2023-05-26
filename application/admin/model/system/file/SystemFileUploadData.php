<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\file;

use BusyPHP\uploader\interfaces\DataInterface;

/**
 * 系统文件上传数据
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/22 9:44 PM SystemFileUploadData.php $
 */
class SystemFileUploadData
{
    /**
     * @var DataInterface
     */
    private DataInterface $data;
    
    /**
     * @var string
     */
    private string $classType = '';
    
    /**
     * @var string
     */
    private string $classValue = '';
    
    /**
     * @var int
     */
    private int $userId = 0;
    
    /**
     * @var string
     */
    private string $disk = '';
    
    /**
     * @var string
     */
    private string $driver;
    
    
    /**
     * 构造函数
     * @param string        $driver 上传驱动名称
     * @param DataInterface $data 上传驱动数据
     */
    public function __construct(string $driver, DataInterface $data)
    {
        $this->data   = $data;
        $this->driver = $driver;
    }
    
    
    /**
     * 获取上传驱动数据
     * @return DataInterface
     */
    public function getData() : DataInterface
    {
        return $this->data;
    }
    
    
    /**
     * 获取上传驱动名称
     * @return string
     */
    public function getDriver() : string
    {
        return $this->driver;
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
     * @return static
     */
    public function setClassType(string $classType) : static
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
     * @return static
     */
    public function setClassValue(string $classValue) : static
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
     * @return static
     */
    public function setUserId(int $userId) : static
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
     * @return static
     */
    public function setDisk(string $disk) : static
    {
        $this->disk = $disk;
        
        return $this;
    }
}