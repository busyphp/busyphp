<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\message;

use BusyPHP\model\Entity;

/**
 * 后台消息信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午2:31 AdminMessageInfo.php $
 * @method static Entity iconColor() 图标颜色
 * @method static Entity iconIsClass() 图标是否css类
 * @property bool $read
 */
class AdminMessageInfo extends AdminMessageField
{
    /**
     * 图标颜色
     * @var string
     */
    public $iconColor;
    
    /**
     * 图标是否css类
     * @var bool
     */
    public $iconIsClass;
    
    
    public function onParseAfter()
    {
        $this->read = $this->read > 0;
        
        $icons = json_decode($this->icon, true);
        $color = '';
        if (count($icons) > 1) {
            [$icon, $color] = $icons;
        } else {
            [$icon] = $icons;
        }
        
        $this->iconColor = $color;
        if (false !== strpos($icon, '/')) {
            $this->iconIsClass = false;
            $this->icon        = $icon;
        } else {
            $this->iconIsClass = true;
            $this->icon        = $icon;
        }
    }
}