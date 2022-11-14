<?php

namespace BusyPHP\app\admin\plugin;

use BusyPHP\App;
use BusyPHP\app\admin\js\driver\FormVerifyRemote;
use BusyPHP\Model;
use BusyPHP\Request;
use RuntimeException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * FormVerify远程验证插件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/3/16 9:04 PM FormVerifyRemotePlugin.php $
 * @deprecated 请使用 {@see FormVerifyRemote}，未来某个版本会删除
 */
class FormVerifyRemotePlugin
{
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * 要验证的字段
     * @var string
     */
    public $field;
    
    /**
     * 要验证的内容
     * @var string
     */
    public $value;
    
    /**
     * 排除的字段
     * @var string
     */
    public $excludeField;
    
    /**
     * 排除的值
     * @var string
     */
    public $excludeValue;
    
    /**
     * 错误消息文案
     * @var string
     */
    public $errorMessage;
    
    
    public function __construct()
    {
        $this->request      = App::getInstance()->request;
        $this->field        = $this->request->param('field/s', 'trim');
        $this->value        = $this->request->param('value/s', 'trim');
        $this->excludeField = $this->request->param('exclude_field/s', 'trim');
        $this->excludeValue = $this->request->param('exclude_value/s', 'trim');
        $this->errorMessage = $this->request->param('error_message/s', 'trim');
    }
    
    
    /**
     * 自动构建数据
     * @param Model|null $model 模型
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function build(?Model $model = null) : ?array
    {
        if (!$model) {
            $model = $this->request->get('model/s', '', 'trim');
            $model = str_replace('/', '\\', $model);
            $model = class_exists($model) ? new $model() : null;
        }
        
        if ($model instanceof Model) {
            if ($this->field && $this->value !== '') {
                $model->where($this->field, $this->value);
                
                if ($this->excludeField && $this->excludeValue !== '') {
                    $model->where($this->excludeField, '<>', $this->excludeValue);
                }
            }
            
            if ($model->find()) {
                throw new RuntimeException($this->errorMessage);
            }
            
            return $this->result();
        }
        
        return null;
    }
    
    
    /**
     * 返回数据处理
     * @return array
     */
    public function result() : array
    {
        return [];
    }
}