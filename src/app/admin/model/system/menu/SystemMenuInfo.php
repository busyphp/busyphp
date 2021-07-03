<?php


namespace BusyPHP\app\admin\model\system\menu;

use BusyPHP\helper\util\Str;
use BusyPHP\model\Entity;
use think\facade\Route;

/**
 * 系统菜单模型信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午6:13 SystemMenuInfo.php $
 * @method static Entity child() 子菜单
 * @method static Entity path() 路径
 * @method static Entity url() 网址
 */
class SystemMenuInfo extends SystemMenuField
{
    /**
     * @var SystemMenuInfo[]
     */
    public $child = [];
    
    /**
     * URL PATH
     * @var string
     */
    public $path;
    
    /**
     * 网址
     * @var string
     */
    public $url;
    
    /**
     * 是否菜单
     * @var bool
     */
    public $isMenu;
    
    /**
     * 是否分组
     * @var bool
     */
    public $isGroup;
    
    /**
     * 是否面板
     * @var bool
     */
    public $isPanel;
    
    
    public function onParseAfter()
    {
        $this->module     = Str::studly($this->module);
        $this->control    = Str::studly($this->control);
        $this->isDefault  = $this->isDefault > 0;
        $this->isSystem   = $this->isSystem > 0;
        $this->isDisabled = $this->isDisabled > 0;
        $this->isHide     = $this->isHide > 0;
        $this->isPanel    = $this->parentId == 0;
        $this->isMenu     = !$this->isPanel && $this->type == SystemMenu::TYPE_NAV;
        $this->isGroup    = !$this->isPanel && $this->type == SystemMenu::TYPE_GROUP;
        $this->path       = SystemMenu::createUrlPath($this);
        
        if ($this->isMenu) {
            $this->url = Route::buildUrl($this->path)->build();
        }
        
        if ($this->link) {
            $this->url = $this->link;
        }
    }
}