<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

use BusyPHP\exception\ParamInvalidException;
use BusyPHP\image\parameter\concern\DxDyConcern;
use BusyPHP\image\parameter\concern\WidthHeightConcern;

/**
 * 普通裁剪参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 2:29 PM CutParameter.php $
 */
class CutParameter extends BaseParameter
{
    use WidthHeightConcern;
    use DxDyConcern;
    
    protected static $parameterName = '普通裁剪';
    
    
    /**
     * @param int $width 宽
     * @param int $height 高
     * @param int $dx X轴偏移
     * @param int $dy Y轴偏移
     */
    public function __construct(int $width = 0, int $height = 0, int $dx = 0, int $dy = 0)
    {
        $this->setWidth($width);
        $this->setHeight($height);
        $this->setDx($dx);
        $this->setDy($dy);
    }
    
    
    /**
     * @inheritDoc
     */
    public function verification()
    {
        if ($this->width <= 0 && $this->height <= 0) {
            throw new ParamInvalidException('cut width or height');
        }
    }
}