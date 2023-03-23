<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\field;

use Attribute;
use BusyPHP\model\Field;

/**
 * 将属性标记为非字段注解
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/9 15:10 Ignore.php $
 * @see Field
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Ignore
{
}