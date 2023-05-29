<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\relation;

use Attribute;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\Model;
use BusyPHP\model\annotation\field\Separate;
use BusyPHP\model\Field;

/**
 * 固定字段值关联注解，适用于某个字段值是多个id组成的情况
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/5/29 14:05 StaticToMany.php $
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class FixedToMany extends OneToOne
{
    public function handle(Model $model, array &$list)
    {
        $dataKey  = $this->getDataKey($model);
        $localKey = $this->getLocalKey($model);
    
        // 获取localKey对应的属性名称
        $fieldClass = $model->getFieldClass();
        $localName  = $fieldClass::getPropertyName($localKey);
        $format     = null;
        if (null !== $localName && null !== $attrs = $fieldClass::getPropertyAttrs($localName)) {
            $format = $attrs[Field::ATTR_FORMAT];
        }
    
        $range     = [];
        $formatKey = '__private_format_decode_' . $localKey;
        $format    = $format ?: new Separate();
        foreach ($list as &$item) {
            $item[$formatKey] = (array) $format->decode($item[$localKey]);
            $range            = array_merge($range, $item[$formatKey]);
        }
    
        $data = ArrayHelper::listByKey($this->prepareModel()
            ->extend(true)
            ->where($this->getForeignKey($model), 'in', $range)
            ->selectList(), $dataKey);
    
        foreach ($list as &$item) {
            $item[$this->propertyName] = [];
            foreach ($item[$formatKey] as $id) {
                if (isset($data[$id])) {
                    $item[$this->propertyName][] = $data[$id];
                }
            }
            unset($item[$formatKey]);
        }
    }
}