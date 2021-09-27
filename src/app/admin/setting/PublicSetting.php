<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\setting;

use BusyPHP\model\Setting;
use BusyPHP\helper\util\Filter;

/**
 * 系统基本配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午6:02 下午 PublicSetting.php $
 */
class PublicSetting extends Setting
{
    protected function parseSet($data)
    {
        return Filter::trim($data);
    }
    
    
    protected function parseGet($data)
    {
        return $data;
    }
    
    
    /**
     * 获取站点名称
     * @return string
     */
    public function getTitle() : string
    {
        return $this->get('title', '') ?: $this->app->getBusyName();
    }
    
    
    /**
     * 获取公网安备号
     * @return string
     */
    public function getPoliceNo() : string
    {
        return $this->get('police_no', '') ?: '';
    }
    
    
    /**
     * 获取域名备案号
     * @return string
     */
    public function getIcpNo() : string
    {
        return $this->get('icp_no', '') ?: '';
    }
    
    
    /**
     * 获取网站图标
     * @return string
     */
    public function getFavicon() : string
    {
        return $this->get('favicon', '') ?: '';
    }
    
    
    /**
     * 获取站点名称
     * @return string
     */
    public function getCopyright() : string
    {
        $year = date('Y');
        
        return $this->get('copyright', '') ?: "© CopyRight 2015-{$year} <a href='https://www.harter.cn?form=BusyPHP'>{$this->app->getBusyName()}</a>  V{$this->app->getBusyVersion()}";
    }
}