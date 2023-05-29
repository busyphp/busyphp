<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\relation;

use BusyPHP\Model;
use ReflectionProperty;
use RuntimeException;

/**
 * 数据关联基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/15 21:26 Relation.php $
 */
abstract class Relation
{
    protected string $propertyName;
    
    
    public function __invoke(string $propertyName) : static
    {
        $this->propertyName = $propertyName;
        
        return $this;
    }
    
    
    /**
     * 通过字段名获取属性名
     * @param Model  $model
     * @param string $field
     * @return string
     */
    protected function getPropertyNameByField(Model $model, string $field) : string
    {
        $fieldClass = $model->getFieldClass();
        if (!$name = $fieldClass::getPropertyName($field)) {
            throw new RuntimeException(sprintf('Cannot find property "%s" in class "%s"', $this->foreignKey, $fieldClass));
        }
        
        return $name;
    }
    
    
    /**
     * 处理数据
     * @param Model $model
     * @param array $list
     */
    abstract public function handle(Model $model, array &$list);
}