<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\task;

/**
 * 系统任务配置类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/16 18:41 ConfigSystemTask.php $
 * @see SystemTaskInterface::configSystemTask()
 */
class ConfigSystemTask
{
    private string $title;
    
    private int    $later;
    
    private int    $loop;
    
    private mixed  $data;
    
    
    /**
     * 构造函数
     * @param mixed  $data 任务处理数据
     * @param string $title 任务标题
     * @param int    $later 延时执行秒数
     * @param int    $loop 循环执行间隔秒数
     */
    public function __construct(mixed $data, string $title = '', int $later = 0, int $loop = 0)
    {
        $this->data  = $data;
        $this->title = $title;
        $this->later = $later;
        $this->loop  = $loop;
    }
    
    
    /**
     * 获取任务标题
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }
    
    
    /**
     * 设置任务标题
     * @param string $title
     * @return static
     */
    public function setTitle(string $title) : static
    {
        $this->title = $title;
        
        return $this;
    }
    
    
    /**
     * 获取延时执行秒数
     * @return int
     */
    public function getLater() : int
    {
        return $this->later;
    }
    
    
    /**
     * 设置延时执行秒数
     * @param int $later
     * @return static
     */
    public function setLater(int $later) : static
    {
        $this->later = $later;
        
        return $this;
    }
    
    
    /**
     * 获取循环执行间隔秒数
     * @return int
     */
    public function getLoop() : int
    {
        return $this->loop;
    }
    
    
    /**
     * 设置循环执行间隔秒数
     * @param int $loop
     * @return static
     */
    public function setLoop(int $loop) : static
    {
        $this->loop = $loop;
        
        return $this;
    }
    
    
    /**
     * 获取任务处理数据
     * @return mixed
     */
    public function getData() : mixed
    {
        return $this->data;
    }
}