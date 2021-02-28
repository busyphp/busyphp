<?php

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\controller\InsideController;

/**
 * 缓存管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/2 下午12:10 下午 Update.php $
 */
class UpdateController extends InsideController
{
    /**
     * 更新系统配置
     */
    public function index()
    {
        return $this->submit('post', function($data) {
            $this->updateCache();
            
            $this->log('更新系统配置');
            
            return '更新完成';
        }, function() {
            $this->setRedirectUrl(null);
        });
    }
    
    
    /**
     * 清空缓存
     */
    public function cache()
    {
        return $this->submit('post', function($data) {
            $this->clearCache($data['name']);
            
            $this->log('清空数据缓存');
            
            return '清空成功';
        }, function() {
            $this->assign('list', $this->getApps());
            $this->setRedirectUrl(null);
        });
    }
}