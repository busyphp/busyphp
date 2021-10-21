<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\setting;

use BusyPHP\App;
use BusyPHP\file\QRCode;
use BusyPHP\helper\FilterHelper;
use BusyPHP\model\Setting;

/**
 * 二维码生成配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/19 下午下午3:44 QrcodeSetting.php $
 */
class QrcodeSetting extends Setting
{
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
     * 设置数据解析器
     * @param mixed $data
     * @return mixed
     */
    protected function parseSet($data)
    {
        $data = FilterHelper::trim($data);
        
        return $data;
    }
    
    
    /**
     * 获取识别率等级
     * @return string
     */
    public function getLevel() : string
    {
        return $this->get('level', '') ?: QRCode::LEVEL_M;
    }
    
    
    /**
     * 获取二维码尺寸
     * @return int
     */
    public function getSize() : int
    {
        return (int) ($this->get('size', 0) ?: 10);
    }
    
    
    /**
     * 获取二维码间距
     * @return int
     */
    public function getMargin() : int
    {
        return (int) ($this->get('margin', 0) ?: 1);
    }
    
    
    /**
     * 获取二维码质量
     * @return int
     */
    public function getQuality() : int
    {
        return (int) ($this->get('quality', 0) ?: 80);
    }
    
    
    /**
     * 是否保存到本地
     * @return bool
     */
    public function isSaveLocal() : bool
    {
        return (bool) $this->get('save_local', false);
    }
    
    
    /**
     * 是否加LOGO
     * @return bool
     */
    public function isLogoStatus() : bool
    {
        return (bool) $this->get('logo_status', false);
    }
    
    
    /**
     * 获取Logo尺寸
     * @return int
     */
    public function getLogoSize() : int
    {
        return (int) ($this->get('logo_size', 0) ?: 5);
    }
    
    
    /**
     * 获取Logo路径
     * @param bool $isPath
     * @return string
     */
    public function getLogoPath(bool $isPath = false) : string
    {
        $logo = $this->get('logo_path', '');
        
        return $isPath ? App::urlToPath($logo) : $logo;
    }
    
    
    /**
     * 获取绑定域名
     * @return string
     */
    public function getDomain() : string
    {
        return (string) $this->get('domain', '');
    }
}