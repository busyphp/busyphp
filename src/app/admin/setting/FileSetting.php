<?php

namespace BusyPHP\app\admin\setting;

use BusyPHP\App;
use BusyPHP\exception\VerifyException;
use BusyPHP\model\Setting;
use BusyPHP\helper\util\Filter;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use think\helper\Str;

/**
 * 附件配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/2 下午6:54 下午 FileSetting.php $
 */
class FileSetting extends Setting
{
    /** 默认上传地址 */
    const DEFAULT_SAVE_PATH = '/uploads/';
    
    //+--------------------------------------
    //| 分类配置相关
    //+--------------------------------------
    /** @var string 附件类型 */
    private $classify = null;
    
    /** @var array 附件类型数据 */
    private static $classList = [];
    
    
    /**
     * 设置附件类型
     * @param $classify
     * @return $this
     */
    public function setClassify($classify)
    {
        $this->classify = trim($classify);
        if (!self::$classList) {
            self::$classList = SystemFileClass::init()->getListByCache();
        }
        
        return $this;
    }
    
    
    /**
     * @param array $data
     * @return array
     * @throws VerifyException
     */
    protected function parseSet($data)
    {
        $data                       = Filter::trim($data);
        $data['save_path']          = $data['save_path'] ?: self::DEFAULT_SAVE_PATH;
        $data['token']              = $data['token'] ?: Str::random(32);
        $data['watermark_position'] = Filter::min($data['watermark_position']);
        $data['watermark_position'] = Filter::max($data['watermark_position'], 9);
        
        if ($data['watermark_file'] && !is_file(App::urlToPath($data['watermark_file']))) {
            throw new VerifyException('水印文件不存在', 'watermark_file');
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
     * 获取附件存放目录格式
     * @return string
     */
    public function getFormat()
    {
        return $this->get('format');
    }
    
    
    /**
     * 获取后台允许上传的附件后缀
     * @return array
     */
    public function getAdminType()
    {
        if ($config = $this->getClassConfig()) {
            // 不继承系统设置
            if (!$config['suffix_is_inherit']) {
                return Filter::trimArray(explode(',', $config['suffix']));
            }
        }
        
        return Filter::trimArray(explode(',', $this->get('admin_type')));
    }
    
    
    /**
     * 获取后台允许上传的附件大小
     * @return int
     */
    public function getAdminSize()
    {
        if ($config = $this->getClassConfig()) {
            // 不继承系统设置
            if (!$config['size_is_inherit']) {
                return $config['size'] * 1024;
            }
        }
        
        return $this->get('admin_size') * 1024;
    }
    
    
    /**
     * 获取前台允许上传的附件后缀
     * @return array
     */
    public function getHomeType()
    {
        if ($config = $this->getClassConfig()) {
            // 不继承系统设置
            if (!$config['suffix_is_inherit']) {
                return Filter::trimArray(explode(',', $config['suffix']));
            }
        }
        
        return Filter::trimArray(explode(',', $this->get('home_type')));
    }
    
    
    /**
     * 获取前台允许上传的附件大小
     * @return int
     */
    public function getHomeSize()
    {
        if ($config = $this->getClassConfig()) {
            // 不继承系统设置
            if (!$config['size_is_inherit']) {
                return $config['size'] * 1024;
            }
        }
        
        return $this->get('home_size') * 1024;
    }
    
    
    /**
     * 获取允许上传mime类型
     * @return array
     */
    public function getMimeType()
    {
        if ($config = $this->getClassConfig()) {
            return Filter::trimArray(explode(',', $config['mimetype']));
        }
        
        return [];
    }
    
    
    /**
     * 获取是否加水印
     * @return bool
     */
    public function isWatermark()
    {
        if ($config = $this->getClassConfig()) {
            return $config['watermark'];
        }
        
        return false;
    }
    
    
    /**
     * 是否允许前台上传
     * @return bool
     */
    public function homeCanUpload()
    {
        if ($config = $this->getClassConfig()) {
            return $config['home_upload'];
        }
        
        return false;
    }
    
    
    /**
     * 前台上传是否需要登录
     * @return bool
     */
    public function homeNeedLogin()
    {
        if ($config = $this->getClassConfig()) {
            return $config['home_login'];
        }
        
        return false;
    }
    
    
    /**
     * 获取是否进行缩放图片
     * @return bool
     */
    public function isThumb()
    {
        if ($config = $this->getClassConfig()) {
            return $config['is_thumb'] && $this->getThumbWidth() > 0 || $this->getThumbHeight() > 0;
        }
        
        return false;
    }
    
    
    /**
     * 缩图后是否删除原图
     * @return bool
     */
    public function isThumbDeleteSource()
    {
        if ($config = $this->getClassConfig()) {
            return $config['delete_source'];
        }
        
        return false;
    }
    
    
    /**
     * 获取缩图方式
     * @return int
     */
    public function getThumbType()
    {
        if ($config = $this->getClassConfig()) {
            return Filter::min(intval($config['thumb_type']));
        }
        
        return 0;
    }
    
    
    /**
     * 获取缩图宽度
     * @return int
     */
    public function getThumbWidth()
    {
        if ($config = $this->getClassConfig()) {
            return Filter::min(intval($config['width']));
        }
        
        return 0;
    }
    
    
    /**
     * 获取缩图高度
     * @return int
     */
    public function getThumbHeight()
    {
        if ($config = $this->getClassConfig()) {
            return Filter::min(intval($config['height']));
        }
        
        return 0;
    }
    
    
    /**
     * 获取附件上传密钥
     * @return string
     */
    public function getToken()
    {
        return $this->get('token');
    }
    
    
    /**
     * 获取水印文件路径
     * @return string
     */
    public function getWatermarkFile()
    {
        return $this->get('watermark_file');
    }
    
    
    /**
     * 获取水印位置
     * @return int
     */
    public function getWatermarkPosition()
    {
        return $this->get('watermark_position');
    }
    
    
    /**
     * 获取附件保存路径
     * @return string
     */
    public function getSavePath()
    {
        $savePath = $this->get('save_path');
        
        return $savePath ?: self::DEFAULT_SAVE_PATH;
    }
    
    
    /**
     * 获取分类配置
     * @param string|null $name
     * @return bool|array
     */
    public function getClassConfig($name = null)
    {
        if (!is_null($this->classify) && isset(self::$classList[$this->classify])) {
            if (!is_null($name)) {
                return self::$classList[$this->classify][$name];
            }
            
            return self::$classList[$this->classify];
        }
        
        return false;
    }
}