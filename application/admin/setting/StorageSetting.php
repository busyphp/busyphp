<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\setting;

use BusyPHP\App;
use BusyPHP\app\admin\component\filesystem\Driver;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClassField;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\FilesystemHelper;
use BusyPHP\helper\FilterHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\helper\TransHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\model\Setting;
use think\facade\Filesystem;
use think\facade\Request;

/**
 * 存储设置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/20 下午上午11:20 StorageSetting.php $
 */
class StorageSetting extends Setting implements ContainerInterface
{
    /** @var array */
    protected static $disks;
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * 获取分类配置
     * @param string $classType
     * @return SystemFileClassField|null
     */
    protected function getClassConfig($classType) : ?SystemFileClassField
    {
        $list = SystemFileClass::instance()->getList();
        
        return $list[$classType] ?? null;
    }
    
    
    /**
     * @inheritDoc
     */
    protected function parseSet(array $data) : array
    {
        $data = FilterHelper::trim($data);
        
        $data['disk'] = $data['disk'] ?? '';
        $data['disk'] = $data['disk'] ?: FilesystemHelper::STORAGE_PUBLIC;
        
        // 客户端限制过滤
        $data['clients'] = $data['clients'] ?? [];
        foreach ($data['clients'] as $client => $item) {
            $item['allow_extensions'] = StringHelper::formatSplit($item['allow_extensions'] ?? '');
            $item['max_size']         = TransHelper::formatMoney(floatval($item['max_size'] ?? 0));
            $data['clients'][$client] = $item;
        }
        
        return $data;
    }
    
    
    /**
     * 获取磁盘配置
     * @return string
     */
    public function getDisk() : string
    {
        return $this->get('disk', '') ?: FilesystemHelper::STORAGE_PUBLIC;
    }
    
    
    /**
     * 获取磁盘独立配置
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function getDiskConfig(string $key, $default = null) : mixed
    {
        return ArrayHelper::get($this->get('disk_config', []), $key, $default);
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
     */
    public function getAllowExtensions(string $classType = '', string $client = '') : array
    {
        if ($config = $this->getClassConfig($classType)) {
            if ($config->extensions && $extensions = StringHelper::formatSplit($config->extensions, true)) {
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
     */
    public function getMimeType(string $classType = '') : array
    {
        if ($config = $this->getClassConfig($classType)) {
            return StringHelper::formatSplit($config->mimetype, true);
        }
        
        return [];
    }
    
    
    /**
     * 获取图片处理样式
     * @param string $classType 文件分类
     * @param string $disk 磁盘系统
     * @return string
     */
    public function getImageStyle(string $classType = '', string $disk = '') : string
    {
        if ($config = $this->getClassConfig($classType)) {
            return $config->style[$disk ?: $this->getDisk()] ?? '';
        }
        
        return '';
    }
    
    
    /**
     * 获取远程下载忽略域名
     * @return array
     */
    public function getRemoteIgnoreDomains() : array
    {
        $domains   = explode(',', str_replace(PHP_EOL, ',', $this->get('remote_ignore_domains', '')));
        $domains[] = Request::host(true);
        foreach (Filesystem::getConfig('disks') as $disk => $config) {
            $domains = array_merge($domains, Filesystem::disk($disk)->getDomains());
        }
        
        $domains = array_filter($domains, function($domain) {
            $domain = strtolower(trim($domain));
            if (!$domain || !str_contains($domain, '.')) {
                return false;
            }
            
            return $domain;
        });
        
        return FilterHelper::trimArray($domains, true);
    }
    
    
    /**
     * 获取支持的磁盘
     * @param string     $disk 磁盘名称
     * @param string     $key 索引名称
     * @param mixed|null $default 默认值
     * @return array<string, array{name: string, desc: string, type: string, checked: bool, form: array}>
     */
    public static function getDisks(string $disk = '', string $key = '', mixed $default = null) : mixed
    {
        if (!static::$disks) {
            // 磁盘信息
            $disks = [];
            foreach (Filesystem::getConfig('disks') as $type => $vo) {
                if (($vo['visibility'] ?? '') !== 'public') {
                    continue;
                }
                
                $manager      = Driver::getInstance($type);
                $form         = $manager->getForm();
                $disks[$type] = [
                    'name'    => $manager->getName(),
                    'desc'    => $manager->getDescription(),
                    'type'    => $type,
                    'checked' => $type == self::instance()->getDisk(),
                    'form'    => !empty($form),
                    'fields'  => $form['fields'] ?? [],
                    'alert'   => $form['alert'] ?? ''
                ];
            }
            
            static::$disks = $disks;
        }
        
        if ($disk) {
            if (!$key) {
                return ArrayHelper::get(static::$disks, $disk, $default);
            }
            
            return ArrayHelper::get(static::$disks, $disk . '.' . $key, $default);
        }
        
        return static::$disks;
    }
}