<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

use BusyPHP\image\traits\Color;
use BusyPHP\image\traits\Rotate;

/**
 * 图片旋转参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 3:26 PM RotateParameter.php $
 * @method self setColor(string $color) 设置背景色
 * @method string getColor() 获取背景色
 */
class RotateParameter extends BaseParameter
{
    use Rotate;
    use Color;
    
    protected static $parameterName = '旋转';
    
    
    /**
     * @param int $rotate 旋转角度，范围0-360
     */
    public function __construct(int $rotate, string $color = '')
    {
        $this->setRotate($rotate);
        $this->setColor($color);
    }
    
    
    public static function __make()
    {
        return new self(0);
    }
}