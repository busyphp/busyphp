<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\relation;

use Attribute;
use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 一对一关联注解
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/11 16:09 OneToOne.php $
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class OneToOne extends Relation
{
    protected string       $modelName;
    
    protected mixed        $foreignKey;
    
    protected mixed        $localKey;
    
    protected mixed        $condition;
    
    protected string|array $order;
    
    protected mixed        $model;
    
    protected string       $dataKey = '';
    
    
    /**
     * 构造函数
     * @param string                $model 关联模型类名
     * @param string|callable       $foreignKey 当前模型外键，默认的外键规则是当前模型名+_id
     * @param string|callable       $localKey 当前模型主键，默认会自动获取也可以指定传入
     * @param string|array|callable $condition 自定义查询条件
     * @param string|array          $order 排序方式
     */
    public function __construct(string $model, string|callable $foreignKey = '', string|callable $localKey = '', string|array|callable $condition = '', string|array $order = 'id DESC')
    {
        $this->modelName  = $model;
        $this->foreignKey = $foreignKey;
        $this->localKey   = $localKey;
        $this->condition  = $condition;
        $this->order      = $order;
    }
    
    
    /**
     * 获取关联模型
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
     * 获取排序方式
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
     * 获取自定义查询条件
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
     * 准备关联模型查询
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
     * @inheritDoc
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function handle(Model $model, array &$list)
    {
        $localKey = $this->getLocalKey($model);
        $range    = array_column($list, $localKey);
        $data     = $this->prepareModel()
            ->extend(true)
            ->where($this->getForeignKey($model), 'in', $range)
            ->scene($this->getScene($this->getModel()), $this->sceneMap)
            ->selectList();
        $data     = ArrayHelper::listByKey($data, $this->getDataKey($model));
        foreach ($list as &$vo) {
            $vo[$this->propertyName] = $data[$vo[$localKey]] ?? null;
        }
    }
    
    
    /**
     * 获取外键字段名称
     * @param Model $model
     * @return string
     */
    protected function getForeignKey(Model $model) : string
    {
        if ($obj = Entity::tryCallable($this->foreignKey)) {
            $this->foreignKey = (string) $obj;
            $this->dataKey    = $obj();
        } else {
            // 当前模型名+_id
            if (!$this->foreignKey) {
                $this->foreignKey = $model->getName() . '_id';
            }
            
            if (!$this->dataKey) {
                $this->dataKey = $this->getPropertyNameByField($this->getModel(), $this->foreignKey);
            }
        }
        
        return $this->foreignKey;
    }
    
    
    /**
     * 获取外键属性名称
     * @param Model $model
     * @return string
     */
    protected function getDataKey(Model $model) : string
    {
        $this->getForeignKey($model);
        
        return $this->dataKey;
    }
    
    
    /**
     * 获取主键
     * @param Model $model
     * @return string
     */
    protected function getLocalKey(Model $model) : string
    {
        if ($obj = Entity::tryCallable($this->localKey)) {
            $this->localKey = (string) $obj;
        } elseif (!$this->localKey) {
            $this->localKey = $model->getPk();
        }
        
        return $this->localKey;
    }
}