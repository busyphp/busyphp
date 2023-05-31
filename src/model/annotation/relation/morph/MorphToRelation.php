<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\relation\morph;

use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use RuntimeException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 多态关联类型与模型关系映射类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/5/29 12:25 MorphToRelation.php $
 */
class MorphToRelation
{
    protected mixed        $type;
    
    protected Model        $model;
    
    protected string       $modelName;
    
    protected mixed        $localKey;
    
    protected mixed        $condition;
    
    protected string|array $order;
    
    protected string       $dataKey;
    
    
    /**
     * 构造函数
     * @param mixed                 $type 多态类型
     * @param string                $model 多态类型对应的模型
     * @param string|callable       $localKey 当前模型主键，默认会自动获取也可以指定传入
     * @param string|array|callable $condition 自定义查询条件
     * @param string|array          $order 排序方式
     */
    public function __construct(mixed $type, string $model, string|callable $localKey = '', string|array|callable $condition = '', string|array $order = 'id DESC')
    {
        $this->type      = (string) $type;
        $this->modelName = $model;
        $this->localKey  = $localKey;
        $this->condition = $condition;
        $this->order     = $order;
    }
    
    
    /**
     * 获取多态关联模型类型
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }
    
    
    /**
     * 获取多态关联模型排序方式
     * @return array|string
     */
    protected function getOrder() : array|string
    {
        $order = $this->order;
        if (is_array($order)) {
            $data = [];
            foreach ($order as $key => $value) {
                // [[Field::class, '字段'], 'desc']
                if (is_array($value)) {
                    if (isset($value[0]) && $obj = Entity::tryCallable($value[0])) {
                        $data[(string) $obj] = $value[1] ?? '';
                    }
                } else {
                    $data[$key] = $value;
                }
            }
            $order = $data;
        }
        
        return $order;
    }
    
    
    /**
     * 获取多态关联模型自定义查询条件
     * @return mixed
     */
    protected function getCondition() : mixed
    {
        if (is_array($this->condition) && is_callable($this->condition)) {
            return call_user_func($this->condition, $this->getModel());
        }
        
        return $this->condition;
    }
    
    
    /**
     * 准备多态关联模型查询
     * @return Model
     */
    protected function prepareModel() : Model
    {
        $model = $this->getModel();
        if ($condition = $this->getCondition()) {
            $model->where($condition);
        }
        
        if (!$model->getOptions('order') && $order = $this->getOrder()) {
            $model->order($order);
        }
        
        return $model;
    }
    
    
    /**
     * 获取多态关联模型主键
     * @return string
     */
    protected function getLocalKey() : string
    {
        if ($obj = Entity::tryCallable($this->localKey)) {
            $this->localKey = (string) $obj;
            $this->dataKey  = $obj();
        } else {
            if (!$this->localKey) {
                $this->localKey = $this->getModel()->getPk();
            }
            
            if (!isset($this->dataKey)) {
                $fieldClass = $this->getModel()->getFieldClass();
                if (!$name = $fieldClass::getPropertyName($this->localKey)) {
                    throw new RuntimeException(sprintf('Cannot find property "%s" in class "%s"', $this->localKey, $fieldClass));
                }
                
                $this->dataKey = $name;
            }
        }
        
        return $this->localKey;
    }
    
    
    /**
     * 获取多态关联模型数据键
     * @return string
     */
    protected function getDataKey() : string
    {
        if (!isset($this->dataKey)) {
            $this->getLocalKey();
        }
        
        return $this->dataKey;
    }
    
    
    /**
     * 获取多态关联模型
     * @return Model
     */
    protected function getModel() : Model
    {
        if (!isset($this->model)) {
            if (!is_subclass_of($this->modelName, Model::class)) {
                throw new ClassNotExtendsException($this->modelName, Model::class);
            }
            
            $this->model = call_user_func([$this->modelName, 'init']);
        }
        
        return $this->model;
    }
    
    
    /**
     * 查询多态关联数据
     * @param array $range
     * @param array $sceneMap
     * @return array<string,array>
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function query(array $range, array $sceneMap = []) : array
    {
        $data = $this->prepareModel()
            ->where($this->getLocalKey(), 'in', $range)
            ->scene(Field::getSceneByMap($sceneMap, $this->getModel()), $sceneMap)
            ->extend(true)
            ->selectList();
        
        return ArrayHelper::listByKey($data, $this->getDataKey());
    }
}