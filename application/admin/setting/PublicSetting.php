<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\setting;

use BusyPHP\App;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\model\Setting;
use BusyPHP\helper\FilterHelper;

/**
 * 系统基本配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午6:02 下午 PublicSetting.php $
 */
class PublicSetting extends Setting implements ContainerInterface
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
        $data              = FilterHelper::trim($data);
        $data['copyright'] = htmlspecialchars($data['copyright']);
        
        return $data;
    }
    
    
    /**
     * 获取站点名称
     * @return string
     */
    public function getTitle() : string
    {
        return $this->get('title', '') ?: $this->app->getFrameworkName();
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
        
        return htmlspecialchars_decode($this->get('copyright', '')) ?: "© CopyRight 2015-{$year} <a href='https://www.harter.cn?form=BusyPHP' target='_blank'>{$this->app->getFrameworkName()}</a>  V{$this->app->getFrameworkVersion()}";
    }
    
    
    /**
     * 获取错误占位图
     * @param bool $isPath
     * @return string
     */
    public function getImgErrorPlaceholder(bool $isPath = false) : string
    {
        $image = $this->get('img_error_placeholder', '');
        if (!$image) {
            $image = $this->app->request->getAssetsUrl() . 'system/images/no_image.jpeg';
            
            if ($isPath) {
                // 资源真实存在于assets目录下，则返回该资源
                if (is_file($image = App::urlToPath($image))) {
                    return $image;
                }
                
                // 返回系统资源
                return __DIR__ . '/../../../assets/images/no_image.jpeg';
            }
        }
        
        return $isPath ? App::urlToPath($image) : $image;
    }
}