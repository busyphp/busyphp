<?php

namespace BusyPHP\app\admin\model\system\file\image;

use BusyPHP\image\result\ImageStyleResult;
use BusyPHP\interfaces\ModelValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\annotation\field\Filter;
use BusyPHP\model\annotation\field\Json;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\annotation\field\Validator;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use ReflectionException;
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
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
class SystemFileImageStyleField extends Field implements ModelValidateInterface
{
    /**
     * 样式名
     * @var string
     */
    #[Validator(name: Validator::REQUIRE, msg: '请输入:attribute')]
    #[Filter(filter: 'trim')]
    public $id;
    
    /**
     * 处理能力
     * @var array
     */
    #[Json]
    #[Validator(name: Validator::REQUIRE, msg: '请选择:attribute')]
    #[Validator(name: Validator::IS_ARRAY)]
    public $content;
    
    
    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    protected function onParseAfter() : void
    {
        $this->content = $this->content ?: [];
        $this->content = ImageStyleResult::fillContent($this->content);
    }
    
    
    /**
     * @inheritDoc
     */
    public function onModelValidate(Model $model, Validate $validate, string $scene, $data = null)
    {
        $validate->append($this::id(), ValidateRule::init()->isFirstAlphaNumDash());
    }
}