<?php


namespace BusyPHP\app\admin\model\system\menu\provide;


use BusyPHP\app\admin\model\system\menu\SystemMenuInfo;
use BusyPHP\model\Field;

/**
 * 管理员面板信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午7:26 AdminMenuStruct.php $
 */
class AdminMenuStruct extends Field
{
    /**
     * 主菜单
     * @var SystemMenuInfo[]
     */
    public $menuList = [];
    
    /**
     * 默认面板Path
     * @var string
     */
    public $defaultPath = '';
    
    /**
     * Path集合
     * @var string[]
     */
    public $paths = [];
}