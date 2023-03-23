<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\field;

use Attribute;
use BusyPHP\model\Field;

/**
 * 定义启用软删除注解
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/17 15:48 SoftDelete.php $
 * @see Field
 */
#[Attribute(Attribute::TARGET_CLASS)]
class SoftDelete
{
    private ?int $default;
    
    
    /**
     * 构造函数
     * @param int|null $default 软删除字段默认值
     */
    public function __construct(?int $default = 0)
    {
        $this->default = $default;
    }
    
    
    /**
     * @return int|null
     */
    public function getDefault() : ?int
    {
        return $this->default;
    }
}