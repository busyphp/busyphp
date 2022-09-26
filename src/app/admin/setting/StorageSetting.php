<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\setting;

use BusyPHP\App;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClassInfo;
use BusyPHP\helper\TransHelper;
use BusyPHP\model\Setting;
use BusyPHP\helper\FilterHelper;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\facade\Filesystem;
use think\filesystem\Driver;

/**
 * 存储设置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/20 下午上午11:20 StorageSetting.php $
 */
class StorageSetting extends Setting
{
    /** @var string 本地系统文件磁盘标识 */
    const STORAGE_LOCAL = 'public';
    
    /** @var string 本地临时文件磁盘标识 */
    const STORAGE_TMP = 'local';
    
    
    /**
     * 解析文件扩展
     * @param string $extensions
     * @param bool   $returnArray
     * @return string|array
     */
    public static function parseExtensions($extensions, bool $returnArray = false)
    {
        $extensions = explode(',', $extensions);
        $extensions = FilterHelper::trimArray($extensions);
        
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
        $data = FilterHelper::trim($data);
        
        $data['disk'] = $data['disk'] ?? '';
        $data['disk'] = $data['disk'] ?: 'public';
        
        // 客户端限制过滤
        $data['clients'] = $data['clients'] ?? [];
        foreach ($data['clients'] as $client => $item) {
            $item['allow_extensions'] = self::parseExtensions($item['allow_extensions'] ?? '');
            $item['max_size']         = TransHelper::formatMoney(floatval($item['max_size'] ?? 0));
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
        $client  = $client ?: App::getInstance()->getDirName();
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
            if ($config->extensions && $extensions = self::parseExtensions($config->extensions, true)) {
                return $extensions;
            }
        }
        
        return FilterHelper::trimArray(explode(',', $this->getClientInfo($client)['allow_extensions'] ?? ''));
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
            if ($config->extensions && $config->maxSize > 0) {
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
            return self::parseExtensions($config->mimetype, true);
        }
        
        return [];
    }
    
    
    /**
     * 获取图片处理样式
     * @param string $classType 文件分类
     * @param string $disk 磁盘系统
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getImageStyle(string $classType = '', string $disk = '') : string
    {
        if ($config = $this->getClassConfig($classType)) {
            return $config->style[$disk ?: $this->getDisk()] ?? '';
        }
        
        return '';
    }
    
    
    /**
     * 获取磁盘文件操作系统
     * @return Driver
     */
    public function getDiskFileSystem() : Driver
    {
        return Filesystem::disk($this->getDisk());
    }
    
    
    /**
     * 获取本地文件操作系统
     * @return Driver
     */
    public function getLocalFileSystem() : Driver
    {
        return Filesystem::disk(self::STORAGE_LOCAL);
    }
    
    
    /**
     * 获取Runtime文件操作系统
     * @return Driver
     */
    public function getRuntimeFileSystem() : Driver
    {
        return Filesystem::disk(self::STORAGE_TMP);
    }
    
    
    /**
     * 获取支持的磁盘
     * @return array<int, array{name: string, desc: string, type: string, checked: bool}>
     */
    public function getDisks() : array
    {
        // 磁盘信息
        $disks = [];
        foreach (Filesystem::getConfig('disks') as $key => $disk) {
            if (($disk['visibility'] ?? '') !== 'public') {
                continue;
            }
            
            // 默认名称
            $name = $disk['name'] ?? '';
            if (!$name) {
                if (strtolower($disk['type'] ?? '') === 'local') {
                    $name = '本地服务器';
                } else {
                    $name = $key;
                }
            }
            
            // 默认描述
            $desc = $disk['description'] ?? '';
            if (!$desc && strtolower($disk['type'] ?? '') === 'local') {
                $root = $disk['root'] ?? '';
                $root = substr($root, strlen($this->app->getRootPath()));
                $desc = "文件直接上传到本地服务器的 <code>{$root}</code> 目录，占用服务器磁盘空间";
            }
            
            $disks[] = [
                'name'    => $name,
                'desc'    => $desc,
                'type'    => $key,
                'checked' => $key == $this->getDisk()
            ];
        }
        
        return $disks;
    }
}