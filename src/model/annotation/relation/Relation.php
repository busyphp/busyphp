<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\relation;

use BusyPHP\Model;
use BusyPHP\model\Field;
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
    
    /**
     * 关联场景
     * @var array<string,string>
     */
    protected array $sceneMap = [];
    
    
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
     * 获取输出场景
     * @param Model $model
     * @return string
     */
    protected function getScene(Model $model) : string
    {
        return Field::getSceneByMap($this->sceneMap, $model);
    }
    
    
    /**
     * 设置关联场景
     * @param array $sceneMap
     * @return Relation
     */
    public function setSceneMap(array $sceneMap) : static
    {
        $this->sceneMap = $sceneMap;
        
        return $this;
    }
    
    
    /**
     * 处理数据
     * @param Model $model
     * @param array $list
     */
    abstract public function handle(Model $model, array &$list);
}