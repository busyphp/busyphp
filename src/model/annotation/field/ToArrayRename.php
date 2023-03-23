<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\field;

use Attribute;
use BusyPHP\model\Field;

/**
 * 字段输出重命名注解类，用于 {@see Field::toArray()} 对键名称重命名
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/12/14 15:57 ToArrayRename.php $
 * @see Field
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class ToArrayRename
{
    private string $name;
    
    private string $scene;
    
    
    /**
     * 构造函数
     * @param string $name 重命名名称
     * @param string $scene 重命名场景
     */
    public function __construct(string $name, string $scene = '')
    {
        $this->name  = $name;
        $this->scene = $scene;
    }
    
    
    /**
     * 获取名称
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    
    /**
     * 获取重命名场景
     * @return string
     */
    public function getScene() : string
    {
        return $this->scene;
    }
}