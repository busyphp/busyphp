<?php

namespace BusyPHP\helper\file;

/**
 * 文件操作类
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2018 busy^life <busy.life@qq.com>
 * @version $Id: 2018-02-03 上午10:14 File.php busy^life $
 */
class File
{
    /**
     * 获取文件mimeType
     * @param string $filename 附件路径
     * @return string|false
     */
    public static function getMimeType($filename)
    {
        if (false === $fInfo = finfo_open(FILEINFO_MIME)) {
            return false;
        }
        if (false === $mimeType = finfo_file($fInfo, $filename)) {
            finfo_close($fInfo);
            
            return false;
        }
        finfo_close($fInfo);
        
        if (preg_match('/([^;]+);?.*$/', $mimeType, $match)) {
            return $match[1];
        }
        
        return $mimeType;
    }
    
    
    /**
     * 解析文件路径
     * @param string   $path 文件路径
     * @param null|int $options <pre>
     * PATHINFO_DIRNAME,
     * PATHINFO_BASENAME,
     * PATHINFO_EXTENSION and
     * PATHINFO_FILENAME
     * </pre>
     * @return string|array
     */
    public static function pathInfo($path, $options = null)
    {
        // 不包含中文走系统
        $match = preg_match('/[\x{4e00}-\x{9fa5}]/u', $path);
        if (false === $match || $match <= 0) {
            if (isset($options)) {
                return pathinfo($path, $options);
            }
            
            return pathinfo($path);
        }
        
        $pathParts              = array();
        $pathParts['dirname']   = rtrim(substr($path, 0, strrpos($path, '/')), "/") . "/";
        $pathParts['basename']  = ltrim(substr($path, strrpos($path, '/')), "/");
        $pathParts['extension'] = substr(strrchr($path, '.'), 1);
        $pathParts['filename']  = ltrim(substr($pathParts ['basename'], 0, strrpos($pathParts ['basename'], '.')), "/");
        
        switch ($options) {
            case PATHINFO_EXTENSION:
                return $pathParts['extension'];
            break;
            case PATHINFO_BASENAME:
                return $pathParts['basename'];
            break;
            case PATHINFO_DIRNAME:
                return $pathParts['dirname'];
            break;
            case PATHINFO_FILENAME:
                return $pathParts['filename'];
            break;
        }
        
        return $pathParts;
    }
    
    
    /**
     * 获取附件扩展名称
     * @param $filename
     * @return string
     */
    public static function getExtension($filename)
    {
        return self::pathInfo($filename, PATHINFO_EXTENSION);
    }
    
    
    /**
     * 文件写入
     * @param string $filename 文件地址，不存在会自动创建
     * @param string $string 写入的内容
     * @return bool
     */
    public static function write(string $filename, string $string = '') : bool
    {
        $string  = $string ?? '';
        $dirPath = dirname($filename);
        if (!is_dir($dirPath)) {
            if (!mkdir($dirPath, 0775, true)) {
                return false;
            }
        }
        
        if (false === $handle = fopen($filename, "w")) {
            return false;
        }
        
        if (false === $result = fwrite($handle, $string)) {
            fclose($handle);
            
            return false;
        }
        fclose($handle);
        
        return chmod($filename, 0777);
    }
    
    
    /**
     * 创建文件夹
     * @param string $dirPath 文件夹路径，支持传入包含文件名的路径
     * @param bool   $hasFilename 传入的$dirPath是否包含文件名
     * @return bool
     */
    public static function createDir(string $dirPath, bool $hasFilename = false) : bool
    {
        $dirPath = $hasFilename ? dirname($dirPath) : $dirPath;
        if (!is_dir($dirPath)) {
            return mkdir($dirPath, 0777, true);
        }
        
        return true;
    }
    
    
    /**
     * 删除文件夹
     * @param string $dirPath 文件夹路径
     * @param bool   $isRetainDir 是否保留文件夹，true 保留，false 不保留，默认不保留
     * @return bool
     */
    public static function deleteDir(string $dirPath, bool $isRetainDir = false) : bool
    {
        $handle = opendir($dirPath);
        while ($file = readdir($handle)) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $filePath = $dirPath . '/' . $file;
            if (!is_dir($filePath)) {
                unlink($filePath);
            } else {
                self::deleteDir($filePath, false);
            }
        }
        closedir($handle);
        
        if ($isRetainDir) {
            return true;
        }
        
        return rmdir($dirPath);
    }
}