<?php

namespace BusyPHP\exception;

use BusyPHP\model;

/**
 * 数据库错误异常
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/28 下午11:35 上午 SQLException.php $
 */
class SQLException extends AppException
{
    /**
     * @var Model
     */
    protected $model;
    
    /**
     * @var string
     */
    protected $lastSQL;
    
    /**
     * @var string
     */
    protected $errorSQL;
    
    
    /**
     * 构造器
     * @param string $message
     * @param Model  $model
     */
    public function __construct($message, Model $model)
    {
        if ($message instanceof self) {
            $this->model    = $message->getModel();
            $this->message  = $message->getMessage();
            $this->lastSQL  = $message->getLastSQL();
            $this->errorSQL = $message->getErrorSQL();
            $message        = $this->message;
        } else {
            $this->model    = $model;
            $this->lastSQL  = $this->model->getLastSql();
            $this->errorSQL = $this->model->getErrorSQL();
        }
        
        $this->setData('SQL ERROR', [
            'SQL'   => $model->getLastSql(),
            'ERROR' => $model->getErrorSQL()
        ]);
        
        parent::__construct($message);
    }
    
    
    /**
     * 获取错误的SQL信息
     * @return string
     */
    public function getErrorSQL() : string
    {
        return $this->errorSQL;
    }
    
    
    /**
     * 获取最后执行的SQL语句
     * @return string
     */
    public function getLastSQL() : string
    {
        return $this->lastSQL;
    }
    
    
    /**
     * 获取模型
     * @return Model
     */
    public function getModel() : Model
    {
        return $this->model;
    }
}