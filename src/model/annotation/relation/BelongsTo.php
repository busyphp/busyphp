<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\relation;

use Attribute;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\Model;
use BusyPHP\model\Entity;

/**
 * 定义相对关联注解
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/14 09:18 BelongsTo.php $
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class BelongsTo extends OneToOne
{
    /**
     * 构造函数
     * @param string                $model 关联模型类名
     * @param string|bool|callable  $foreignKey 当前模型外键，默认的外键规则是关联模型名+_id，设为true则是当前属性名+_id
     * @param string|callable       $localKey 关联模型主键，默认会自动获取也可以指定传入
     * @param string|array|callable $condition 自定义查询条件
     * @param string|array          $order 排序方式
     */
    public function __construct(string $model, callable|bool|string $foreignKey = '', callable|string $localKey = '', callable|array|string $condition = '', array|string $order = 'id DESC')
    {
        parent::__construct($model, '', $localKey, $condition, $order);
        $this->foreignKey = $foreignKey;
    }
    
    
    protected function getForeignKey(Model $model) : string
    {
        if ($obj = Entity::tryCallable($this->foreignKey)) {
            $this->foreignKey = (string) $obj;
            $this->dataKey    = $obj();
        } else {
            if ($this->foreignKey === true) {
                $this->foreignKey = StringHelper::snake($this->property->getName()) . '_id';
            }
            if (!$this->foreignKey) {
                $this->foreignKey = $this->getModel()->getName() . '_id';
            }
        }
        
        return $this->foreignKey;
    }
    
    
    protected function getLocalKey(Model $model) : string
    {
        if ($obj = Entity::tryCallable($this->localKey)) {
            $this->localKey = (string) $obj;
            $this->dataKey  = $obj();
        } elseif (!$this->localKey) {
            $this->localKey = $this->getModel()->getPk();
            $this->dataKey  = $this->getPropertyNameByField($this->getModel(), $this->localKey);
        }
        
        return $this->localKey;
    }
    
    
    protected function getDataKey(Model $model) : string
    {
        $this->getLocalKey($model);
        
        return $this->dataKey;
    }
    
    
    public function handle(Model $model, array &$list)
    {
        $property   = $this->property->getName();
        $foreignKey = $this->getForeignKey($model);
        $data       = $this->prepareModel()
            ->extend(false)
            ->where($this->getLocalKey($model), 'in', array_column($list, $foreignKey))
            ->selectList();
        $data       = ArrayHelper::listByKey($data, $this->getDataKey($model));
        foreach ($list as &$vo) {
            $vo[$property] = $data[$vo[$foreignKey]] ?? null;
        }
    }
}