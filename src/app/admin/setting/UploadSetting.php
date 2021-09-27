<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\setting;

use BusyPHP\app\admin\model\system\file\classes\SystemFileClassInfo;
use BusyPHP\helper\AppHelper;
use BusyPHP\helper\util\Transform;
use BusyPHP\model\Setting;
use BusyPHP\helper\util\Filter;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 上传设置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/20 下午上午11:20 UploadSetting.php $
 */
class UploadSetting extends Setting
{
    /**
     * 解析文件扩展
     * @param string $extensions
     * @param bool   $returnArray
     * @return string|array
     */
    public static function parseExtensions($extensions, bool $returnArray = false)
    {
        $extensions = explode(',', $extensions);
        $extensions = Filter::trimArray($extensions);
        
        if ($returnArray) {
            return $extensions;
        }
        
        return implode(', ', $extensions);
    }
    
    
    /**
     * 获取分类配置
     * @param string $classType
     * @return SystemFileClassInfo
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function getClassConfig($classType) : ?SystemFileClassInfo
    {
        $list = SystemFileClass::init()->getList();
        
        return $list[$classType] ?? null;
    }
    
    
    /**
     * @param array $data
     * @return array
     */
    protected function parseSet($data)
    {
        $data = Filter::trim($data);
        
        $data['disk'] = $data['disk'] ?? '';
        $data['disk'] = $data['disk'] ?: 'public';
        
        // 客户端限制过滤
        $data['clients'] = $data['clients'] ?? [];
        foreach ($data['clients'] as $client => $item) {
            $item['allow_extensions'] = self::parseExtensions($item['allow_extensions'] ?? '');
            $item['max_size']         = Transform::formatMoney(floatval($item['max_size'] ?? 0));
            $data['clients'][$client] = $item;
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
     * 获取磁盘配置
     * @return string
     */
    public function getDisk() : string
    {
        return $this->get('disk', '') ?: 'public';
    }
    
    
    /**
     * 获取目录生成方式
     * @return string
     */
    public function getDirGenerateType() : string
    {
        return trim($this->get('dir_generate_type', '')) ?: '';
    }
    
    
    /**
     * 获取客户端配置
     * @param string $client 为空自动获取
     * @return array|null
     */
    public function getClientInfo(string $client = '') : ?array
    {
        $client  = $client ?: AppHelper::getDirName();
        $clients = $this->get('clients', []);
        
        return $clients[$client] ?? null;
    }
    
    
    /**
     * 获取允许上传的文件扩展名
     * @param string $classType 文件分类
     * @param string $client 客户端类型，留空自动获取当前客户端
     * @return string[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getAllowExtensions(string $classType = '', string $client = '') : array
    {
        if ($config = $this->getClassConfig($classType)) {
            if ($config->allowExtensions && $extensions = self::parseExtensions($config->allowExtensions, true)) {
                return $extensions;
            }
        }
        
        return Filter::trimArray(explode(',', $this->getClientInfo($client)['allow_extensions'] ?? ''));
    }
    
    
    /**
     * 获取允许上传的文件大小
     * @param string $classType 文件类型
     * @param string $client 客户端类型，留空自动获取当前客户端
     * @return int
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getMaxSize(string $classType = '', string $client = '') : int
    {
        if ($config = $this->getClassConfig($classType)) {
            if ($config->allowExtensions && $config->maxSize > 0) {
                return $config->maxSize * 1024 * 1024;
            }
        }
        
        return (int) ($this->getClientInfo($client)['max_size'] ?? 0) * 1024 * 1024;
    }
    
    
    /**
     * 获取允许上传mime类型
     * @param string $classType
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getMimeType(string $classType = '') : array
    {
        if ($config = $this->getClassConfig($classType)) {
            return self::parseExtensions($config->mimeType, true);
        }
        
        return [];
    }
    
    
    /**
     * 获取是否加水印
     * @param string $classType
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function isWatermark(string $classType = '') : bool
    {
        $watermark = false;
        if ($config = $this->getClassConfig($classType)) {
            $watermark = $config->watermark;
        }
        
        return $watermark && WatermarkSetting::init()->status();
    }
    
    
    /**
     * 获取缩图方式
     * @param string $classType
     * @return int
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getThumbType(string $classType = '') : int
    {
        if ($config = $this->getClassConfig($classType)) {
            return (int) $config->thumbType;
        }
        
        return 0;
    }
    
    
    /**
     * 获取缩图宽度
     * @param string $classType
     * @return int
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getThumbWidth(string $classType = '') : int
    {
        if ($config = $this->getClassConfig($classType)) {
            return (int) $config->thumbWidth;
        }
        
        return 0;
    }
    
    
    /**
     * 获取缩图高度
     * @param string $classType
     * @return int
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getThumbHeight(string $classType = '') : int
    {
        if ($config = $this->getClassConfig($classType)) {
            return (int) $config->thumbHeight;
        }
        
        return 0;
    }
}