<?php

namespace BusyPHP\app\admin\model\system\menu;

use BusyPHP\helper\util\Filter;
use think\facade\Route;

/**
 * 系统菜单模型信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午6:13 SystemMenuInfo.php $
 * @method static mixed child();
 * @method static mixed hash();
 * @method static mixed parentHash();
 * @method static mixed paramList();
 * @method static mixed url();
 * @method static mixed hides();
 */
class SystemMenuInfo extends SystemMenuField
{
    /**
     * 下级菜单
     * @var SystemMenuInfo[]
     */
    public $child = [];
    
    /**
     * 隐藏的下级菜单，只有在前端菜单中用到
     * @var SystemMenuInfo[]
     */
    public $hides = [];
    
    /**
     * 菜单地址哈希值
     * @var string
     */
    public $hash;
    
    /**
     * 上级菜单地址哈西值
     * @var string
     */
    public $parentHash;
    
    /**
     * 菜单连接
     * @var string
     */
    public $url;
    
    /**
     * 参数列表
     * @var string[]
     */
    public $paramList;
    
    
    public function onParseAfter()
    {
        $this->hash       = md5($this->path);
        $this->parentHash = $this->parentPath ? md5($this->parentPath) : '';
        $this->hide       = $this->hide > 0;
        $this->disabled   = $this->disabled > 0;
        $this->system     = $this->system > 0;
        $this->paramList  = Filter::trimArray(explode(',', $this->params) ?: []);
        
        if (0 === strpos($this->path, '#')) {
            $this->url    = '';
            $this->target = SystemMenu::TARGET_SELF;
        } elseif (false !== strpos($this->path, '://')) {
            $this->url    = $this->path;
            $this->target = SystemMenu::TARGET_BLANK;
        } else {
            $this->url = Route::buildUrl('/' . ltrim($this->path, '/'))->build();
        }
    }
}