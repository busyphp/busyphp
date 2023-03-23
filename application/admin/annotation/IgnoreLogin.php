<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\annotation;

use Attribute;

/**
 * 忽略登录校验注解类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/10 14:03 IgnoreLogin.php $
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class IgnoreLogin
{
}