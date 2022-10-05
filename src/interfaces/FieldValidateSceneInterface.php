<?php
declare(strict_types = 1);

namespace BusyPHP\interfaces;

use BusyPHP\Model;
use BusyPHP\model\Entity;
use think\Validate;

/**
 * Field类 场景验证接口类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/10/4 7:57 PM FieldValidateSceneInterface.php $
 */
interface FieldValidateSceneInterface
{
    /**
     * 执行场景验证时
     * @param Model    $model 模型对象
     * @param Validate $validate 验证对象
     * @param string   $name 场景名称
     * @return string[]|Entity[]|void 返回数组代表只执行该数组内的字段验证
     */
    public function onValidateScene(Model $model, Validate $validate, string $name);
}