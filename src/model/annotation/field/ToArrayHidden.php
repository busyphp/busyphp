<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\field;

use Attribute;
use BusyPHP\model\Field;

/**
 * 字段输出隐藏注解类，用于 {@see Field::toArray()} 时隐藏某个属性
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/12/14 15:56 ToArrayHidden.php $
 * @see Field
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class ToArrayHidden
{
    private string $scene;
    
    
    /**
     * 构造函数
     * @param string $scene 忽略的场景
     */
    public function __construct(string $scene = '')
    {
        $this->scene = $scene;
    }
    
    
    /**
     * 获取忽略场景
     * @return string
     */
    public function getScene() : string
    {
        return $this->scene;
    }
}