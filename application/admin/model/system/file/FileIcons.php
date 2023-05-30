<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\file;

use BusyPHP\App;
use BusyPHP\helper\FileHelper;
use think\facade\Config;

/**
 * 文件图标解析器
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/25 21:26 FileIcons.php $
 */
class FileIcons
{
    protected static array $map = [
        'ai'    => 'ai',
        'apk'   => 'apk',
        'dmg'   => 'dmg',
        'ipa'   => 'dmg',
        'doc'   => 'docx',
        'docx'  => 'docx',
        'exe'   => 'exe',
        'mp3'   => 'mp3',
        'wav'   => 'mp3',
        'wma'   => 'mp3',
        'aac'   => 'mp3',
        'wave'  => 'mp3',
        'ogg'   => 'mp3',
        'mp4'   => 'mp4',
        'avi'   => 'mp4',
        'wmv'   => 'mp4',
        'mpeg'  => 'mp4',
        'm4v'   => 'mp4',
        'mov'   => 'mp4',
        'asf'   => 'mp4',
        'flv'   => 'mp4',
        'f4v'   => 'mp4',
        'rmvb'  => 'mp4',
        'rm'    => 'mp4',
        '3gp'   => 'mp4',
        'vob'   => 'mp4',
        'webm'  => 'mp4',
        'ogv'   => 'mp4',
        'pdf'   => 'pdf',
        'ppt'   => 'pptx',
        'pptx'  => 'pptx',
        'psd'   => 'psd',
        'psb'   => 'psd',
        'sh'    => 'sh',
        'php'   => 'sh',
        'java'  => 'sh',
        'js'    => 'sh',
        'html'  => 'sh',
        'htm'   => 'sh',
        'bat'   => 'sh',
        'jsp'   => 'sh',
        'asp'   => 'sh',
        'py'    => 'sh',
        'c'     => 'sh',
        'm'     => 'sh',
        'swift' => 'sh',
        'json'  => 'sh',
        'xml'   => 'sh',
        'txt'   => 'txt',
        'rft'   => 'txt',
        'md'    => 'txt',
        'xls'   => 'xlsx',
        'xlsx'  => 'xlsx',
        'zip'   => 'zip',
        'rar'   => 'zip',
        '7z'    => 'zip',
        'tar'   => 'zip',
        'gz'    => 'zip'
    ];
    
    public static string   $assetsUrl;
    
    
    /**
     * 获取地址
     * @return string
     */
    protected static function getAssetsUrl() : string
    {
        if (!isset(static::$assetsUrl)) {
            static::$assetsUrl = App::getInstance()->request->getAssetsUrl() . 'system/images/files/';
        }
        
        return static::$assetsUrl;
    }
    
    
    /**
     * 获取图标映射
     * @return array
     */
    public static function getMap() : array
    {
        static $state;
        if (!isset($state)) {
            static::$map = (array) Config::get('admin.model.system_file.file_icons', []) + static::$map;
            $state       = 1;
        }
        
        return static::$map;
    }
    
    
    /**
     * 匹配图标
     * @param string $url
     * @param string $extension
     * @return string
     */
    public static function match(string $url, string $extension = '') : string
    {
        $extension = $extension === '' ? pathinfo($url, PATHINFO_EXTENSION) : $extension;
        
        // 图片直接显示
        if (FileHelper::isCommonImageByPath($url)) {
            return $url;
        }
        
        $name = 'file';
        $map  = static::getMap();
        if (isset($map[$extension])) {
            $name = $map[$extension];
            if (str_starts_with($name, '/')) {
                return $name;
            }
        }
        
        return static::getAssetsUrl() . $name . '.png';
    }
}