<?php

namespace BusyPHP\app\admin\model\system\file\image;

use BusyPHP\interfaces\ModelSceneValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use think\Validate;
use think\validate\ValidateRule;

/**
 * SystemImageStyleField
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/15 11:33 AM SystemFileImageStyleField.php $
 * @method static Entity id(mixed $op = null, mixed $condition = null) 样式名
 * @method static Entity content(mixed $op = null, mixed $condition = null) 样式内容
 * @method $this setId(mixed $id) 设置样式名
 * @method $this setContent(mixed $content) 设置样式内容
 */
class SystemFileImageStyleField extends Field implements ModelSceneValidateInterface
{
    /**
     * 样式名
     * @var string
     * @busy-validate require#请输入:attribute
     * @busy-filter trim
     */
    public $id;
    
    /**
     * 处理能力
     * @var array
     * @busy-array json
     * @busy-validate require#请选择:attribute
     * @busy-validate array
     */
    public $content;
    
    
    /**
     * @inheritDoc
     */
    public function onModelSceneValidate(Model $model, Validate $validate, string $name, $data = null)
    {
        $validate->append(
            $this::id(),
            ValidateRule::regex('/^[a-zA-Z]+[a-zA-Z0-9_]*$/', ':attribute必须是英文数字下划线组合，且必须是英文开头')
        );
    }
}