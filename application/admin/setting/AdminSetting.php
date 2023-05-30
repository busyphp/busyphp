<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\setting;

use BusyPHP\App;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\model\Setting;
use BusyPHP\helper\FilterHelper;
use BusyPHP\helper\TransHelper;

/**
 * 后台配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/19 下午下午5:16 AdminSetting.php $
 */
class AdminSetting extends Setting implements ContainerInterface
{
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * @inheritDoc
     */
    protected function parseSet(array $data) : array
    {
        $data                            = FilterHelper::trim($data);
        $data['verify']                  = TransHelper::toBool($data['verify']);
        $data['multiple_client']         = TransHelper::toBool($data['multiple_client']);
        $data['often']                   = max(intval($data['often']), 0);
        $data['save_login']              = max(intval($data['save_login']), 0);
        $data['login_error_minute']      = max(intval($data['login_error_minute']), 0);
        $data['login_error_max']         = max(intval($data['login_error_max']), 0);
        $data['login_error_lock_minute'] = max(intval($data['login_error_lock_minute']), 0);
        
        return $data;
    }
    
    
    /**
     * 获取后台标题
     * @return string
     */
    public function getTitle() : string
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
        return $this->get('logo_icon', '') ?: App::getInstance()->request->getAssetsUrl() . 'admin/images/busy-php-icon.png';
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
    public function isVerify() : bool
    {
        return (bool) $this->get('verify');
    }
    
    
    /**
     * 获取是否只允许单台客户端登录
     * @return bool
     */
    public function isMultipleClient() : bool
    {
        return (bool) $this->get('multiple_client');
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
     * 获取记住登录的时长秒数
     * @return int
     */
    public function getSaveLogin() : int
    {
        return (int) $this->get('save_login', 0) * 86400;
    }
    
    
    /**
     * 获取保持登录分钟数
     * @return int
     */
    public function getOften() : int
    {
        return (int) $this->get('often', 0);
    }
    
    
    /**
     * 获取水印配置
     * @return array
     */
    public function getWatermark() : array
    {
        $watermark           = $this->get('watermark') ?: [];
        $watermark['status'] = (bool) ($watermark['status'] ?? 0);
        $watermark['txt']    = nl2br($watermark['txt'] ?? '');
        
        return $watermark;
    }
}