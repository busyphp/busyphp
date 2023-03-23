<?php
declare(strict_types = 1);

namespace BusyPHP\image;

use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\CacheHelper;
use BusyPHP\image\parameter\BaseParameter;
use BusyPHP\Image;
use BusyPHP\image\result\ExifResult;
use BusyPHP\image\result\ImageStyleResult;
use BusyPHP\image\result\InfoResult;
use BusyPHP\image\result\PrimaryColorResult;
use BusyPHP\image\result\ProcessResult;
use BusyPHP\image\result\SaveResult;
use RuntimeException;
use think\file\UploadedFile;
use think\Response;
use think\route\Url;

/**
 * 图片系统驱动类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/9 2:24 PM Driver.php $
 */
abstract class Driver
{
    /**
     * 获取支持的字体
     * @return array
     */
    abstract public function getFontList() : array;
    
    
    /**
     * 获取默认字体
     * @return string
     */
    abstract public function getDefaultFontPath() : string;
    
    
    /**
     * 返回不支持的参数模板
     * @return class-string<BaseParameter>[]
     */
    abstract public function getNotSupportParameters() : array;
    
    
    /**
     * 处理图片
     * @param Image $image Image
     * @return ProcessResult
     */
    abstract public function process(Image $image) : ProcessResult;
    
    
    /**
     * 处理并保存
     * @param Image  $image Image
     * @param string $destination 保存的图片路径，留空覆盖原图
     * @return SaveResult
     */
    abstract public function save(Image $image, string $destination = '') : SaveResult;
    
    
    /**
     * 处理并响应
     * @param Image $image
     * @return Response
     */
    abstract public function response(Image $image) : Response;
    
    
    /**
     * 生成在线处理URL
     * @param Image $image
     * @return Url
     */
    abstract public function url(Image $image) : Url;
    
    
    /**
     * 获取图片信息
     * @param string $path 图片路径
     * @return InfoResult
     */
    abstract public function getInfo(string $path) : InfoResult;
    
    
    /**
     * 获取图片EXIF
     * @param string $path 图片路径
     * @return ExifResult
     */
    abstract public function getExif(string $path) : ExifResult;
    
    
    /**
     * 获取图片主色调
     * @param string $path 图片路径
     * @return PrimaryColorResult
     */
    abstract public function getPrimaryColor(string $path) : PrimaryColorResult;
    
    
    /**
     * 上传水印图片
     * @param UploadedFile $file
     * @return string 水印图片URL
     */
    abstract public function uploadWatermark(UploadedFile $file) : string;
    
    
    /**
     * 添加图片样式
     * @param string $name 样式名称
     * @param array  $content 样式规则
     */
    abstract public function createStyle(string $name, array $content);
    
    
    /**
     * 更新图片样式
     * @param string $name 样式名称
     * @param array  $content 样式规则
     */
    abstract public function updateStyle(string $name, array $content);
    
    
    /**
     * 删除图片样式
     * @param string $name 样式名称
     */
    abstract public function deleteStyle(string $name);
    
    
    /**
     * 获取图片样式
     * @param string $name
     * @return ImageStyleResult
     */
    abstract public function getStyle(string $name) : ImageStyleResult;
    
    
    /**
     * 查询图片样式
     * @return ImageStyleResult[]
     */
    abstract public function selectStyle() : array;
    
    
    /**
     * 通过缓存获取图片样式
     * @param string $name
     * @return ImageStyleResult
     */
    public function getStyleByCache(string $name) : ImageStyleResult
    {
        $list = $this->selectStyleByCache();
        if (!$info = ($list[$name] ?? null)) {
            throw new RuntimeException(sprintf('图片样式%s不存在', $name));
        }
        
        return $info;
    }
    
    
    /**
     * 通过缓存查询图片样式
     * @return ImageStyleResult[]
     */
    public function selectStyleByCache() : array
    {
        $key = "stylelist";
        if (!$list = CacheHelper::get(static::class, $key)) {
            $list = $this->selectStyle();
            $list = ArrayHelper::listByKey($list, ImageStyleResult::id()->name());
            CacheHelper::set(static::class, $key, $list, 10 * 60);
        }
        
        return $list;
    }
    
    
    /**
     * 删除图片样式缓存
     */
    protected function clearSelectStyleCache()
    {
        CacheHelper::delete(static::class, 'stylelist');
    }
}