<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\field;

use Attribute;

/**
 * 数据格式化基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/12/15 17:05 Format.php $
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
abstract class Format
{
    /**
     * encode
     * @param mixed $data
     * @return string
     */
    public abstract function encode(mixed $data) : string;
    
    
    /**
     * decode
     * @param string $data
     * @return mixed
     */
    public abstract function decode(string $data) : mixed;
}