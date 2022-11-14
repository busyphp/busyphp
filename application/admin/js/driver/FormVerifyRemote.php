<?php

namespace BusyPHP\app\admin\js\driver;

use BusyPHP\app\admin\js\Driver;
use BusyPHP\app\admin\js\traits\ModelQuery;
use BusyPHP\model\Entity;
use RuntimeException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * JS组件[busyAdmin.plugins.FormVerify] Remote 异步验证
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/14 10:38 FormVerifyRemote.php $
 */
class FormVerifyRemote extends Driver
{
    use ModelQuery;
    
    /**
     * 要验证的字段
     * @var string
     */
    protected $field;
    
    /**
     * 要验证的内容
     * @var string
     */
    protected $value;
    
    /**
     * 排除的字段
     * @var string
     */
    protected $excludeField;
    
    /**
     * 排除的值
     * @var string
     */
    protected $excludeValue;
    
    /**
     * 错误消息文案
     * @var string
     */
    protected $errorMessage;
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->field        = $this->request->param('field/s', '', 'trim');
        $this->value        = $this->request->param('value/s', '', 'trim');
        $this->excludeField = $this->request->param('exclude_field/s', '', 'trim');
        $this->excludeValue = $this->request->param('exclude_value/s', '', 'trim');
        $this->errorMessage = $this->request->param('error_message/s', '', 'trim');
        
        $this->excludeField = $this->excludeField ?: 'id';
        $this->errorMessage = $this->errorMessage ?: '该值已被使用';
    }
    
    
    /**
     * 获取查重字段
     * @return string
     */
    public function getField() : string
    {
        return $this->field;
    }
    
    
    /**
     * 设置查重字段
     * @param string|Entity $field
     * @return $this
     */
    public function setField($field) : self
    {
        $this->field = (string) $field;
        
        return $this;
    }
    
    
    /**
     * 获取查重值
     * @return string
     */
    public function getValue() : string
    {
        return $this->value;
    }
    
    
    /**
     * 设置查重值
     * @param string $value
     * @return $this
     */
    public function setValue(string $value) : self
    {
        $this->value = $value;
        
        return $this;
    }
    
    
    /**
     * 获取排除查重字段
     * @return string
     */
    public function getExcludeField() : string
    {
        return $this->excludeField;
    }
    
    
    /**
     * 设置排除查重字段
     * @param string|Entity $excludeField
     * @return $this
     */
    public function setExcludeField($excludeField) : self
    {
        $this->excludeField = (string) $excludeField;
        
        return $this;
    }
    
    
    /**
     * 获取排除查重值
     * @return string
     */
    public function getExcludeValue() : string
    {
        return $this->excludeValue;
    }
    
    
    /**
     * 设置排除查重值
     * @param string $excludeValue
     * @return $this
     */
    public function setExcludeValue(string $excludeValue) : self
    {
        $this->excludeValue = $excludeValue;
        
        return $this;
    }
    
    
    /**
     * 获取查重错误消息
     * @return string
     */
    public function getErrorMessage() : string
    {
        return $this->errorMessage;
    }
    
    
    /**
     * 设置查重错误消息
     * @param string $errorMessage
     * @return $this
     */
    public function setErrorMessage(string $errorMessage) : self
    {
        $this->errorMessage = $errorMessage;
        
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
        $this->prepareHandler();
        
        if ($this->model && $this->field && $this->value !== '') {
            if (false !== $this->modelQuery()) {
                $this->model->where($this->field, $this->value);
                
                if ($this->excludeValue !== '') {
                    $this->model->where($this->excludeField, '<>', $this->excludeValue);
                }
            }
            
            if ($this->model->field($this->field)->find()) {
                throw new RuntimeException($this->errorMessage);
            }
            
            return [];
        }
        
        return null;
    }
}