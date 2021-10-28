<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\setting;

use BusyPHP\App;
use BusyPHP\model\Setting;
use BusyPHP\helper\FilterHelper;
use BusyPHP\helper\TransHelper;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use think\db\exception\DbException;

/**
 * 后台配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/19 下午下午5:16 AdminSetting.php $
 */
class AdminSetting extends Setting
{
    /**
     * @param mixed $data
     * @return array
     * @throws DbException
     */
    protected function parseSet($data)
    {
        $data                            = FilterHelper::trim($data);
        $data['verify']                  = TransHelper::toBool($data['verify']);
        $data['multiple_client']         = TransHelper::toBool($data['multiple_client']);
        $data['often']                   = FilterHelper::min(intval($data['often']), 0);
        $data['save_login']              = FilterHelper::min(intval($data['save_login']), 0);
        $data['login_error_minute']      = FilterHelper::min(intval($data['login_error_minute']), 0);
        $data['login_error_max']         = FilterHelper::min(intval($data['login_error_max']), 0);
        $data['login_error_lock_minute'] = FilterHelper::min(intval($data['login_error_lock_minute']), 0);
        
        // 切换多设备登录，则清理
        if ($data['multiple_client'] !== $this->get('multiple_client')) {
            AdminUser::init()->clearToken();
        }
        
        return $data;
    }
    
    
    /**
     * 获取数据解析器
     * @param mixed $data
     * @return mixed
     */
    protected function parseGet($data)
    {
        return $data;
    }
    
    
    /**
     * 获取后台标题
     * @return string
     */
    public function getTitle()
    {
        return $this->get('title', '') ?: 'BusyPHP后台管理系统';
    }
    
    
    /**
     * 获取后台横向LOGO
     * @return string
     */
    public function getLogoHorizontal() : string
    {
        return $this->get('logo_horizontal', '') ?: '';
    }
    
    
    /**
     * 获取后台图标
     * @return string
     */
    public function getLogoIcon() : string
    {
        return $this->get('logo_icon', '') ?: App::init()->request->getAssetsUrl() . 'admin/images/busy-php-icon.png';
    }
    
    
    /**
     * 获取登录页背景图
     * @return string
     */
    public function getLoginBg() : string
    {
        return $this->get('login_bg', '') ?: '';
    }
    
    
    /**
     * 获取登录是否需要验证码
     * @return bool
     */
    public function isVerify()
    {
        return $this->get('verify');
    }
    
    
    /**
     * 获取是否只允许单台客户端登录
     * @return bool
     */
    public function isMultipleClient()
    {
        return $this->get('multiple_client');
    }
    
    
    /**
     * 获取登录错误显示分钟数
     * @return int
     */
    public function getLoginErrorMinute() : int
    {
        return (int) $this->get('login_error_minute', 0);
    }
    
    
    /**
     * 获取登录错误最大次数
     * @return int
     */
    public function getLoginErrorMax() : int
    {
        return (int) $this->get('login_error_max', 0);
    }
    
    
    /**
     * 获取登录错误锁定分钟数
     * @return int
     */
    public function getLoginErrorLockMinute() : int
    {
        return (int) $this->get('login_error_lock_minute', 0);
    }
    
    
    /**
     * 获取记住登录的时长天数
     * @return int
     */
    public function getSaveLogin() : int
    {
        return (int) $this->get('save_login', 0);
    }
    
    
    /**
     * 获取保持登录分钟数
     * @return int
     */
    public function getOften() : int
    {
        return intval($this->get('often', 0));
    }
}