<?php

namespace BusyPHP\app\admin\setting;

use BusyPHP\model\Setting;
use BusyPHP\helper\util\Filter;
use BusyPHP\helper\util\Transform;
use BusyPHP\app\admin\model\admin\user\AdminUser;

/**
 * 后台安全配置
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2018 busy^life <busy.life@qq.com>
 * @version $Id: 2018-01-18 上午11:25 AdminSetting.php busy^life $
 */
class AdminSetting extends Setting
{
    protected function parseSet($data)
    {
        $data                    = Filter::trim($data);
        $data['verify']          = Transform::dataToBool($data['verify']);
        $data['multiple_client'] = Transform::dataToBool($data['multiple_client']);
        $data['often']           = intval($data['often']);
        
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
     * 获取保持登录时常分钟数
     * @return int|false
     */
    public function getOften()
    {
        $often = $this->get('often');
        
        return $often > 0 ? $often : false;
    }
}