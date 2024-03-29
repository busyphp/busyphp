<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\relation;

use Attribute;
use BusyPHP\Model;

/**
 * 一对多关联注解
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/12 09:55 OneToMany.php $
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class OneToMany extends OneToOne
{
    public function handle(Model $model, array &$list)
    {
        $dataKey  = $this->getDataKey($model);
        $localKey = $this->getLocalKey($model);
        $dataList = $this->prepareModel()
            ->extend(true)
            ->where($this->getForeignKey($model), 'in', array_column($list, $localKey))
            ->scene($this->getScene($this->getModel()), $this->sceneMap)
            ->selectList();
        
        $data = [];
        foreach ($dataList as $vo) {
            $data[$vo[$dataKey]][] = $vo;
        }
        
        foreach ($list as &$vo) {
            $vo[$this->propertyName] = $data[$vo[$localKey]] ?? [];
        }
    }
}