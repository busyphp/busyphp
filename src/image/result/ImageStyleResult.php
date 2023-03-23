<?php
declare(strict_types = 1);

namespace BusyPHP\image\result;

use BusyPHP\helper\StringHelper;
use BusyPHP\image\Driver as ImageDriver;
use BusyPHP\image\parameter\BaseParameter;
use BusyPHP\Image;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use ReflectionException;
use think\Container;
use think\exception\FuncNotFoundException;
use think\facade\Filesystem;
use think\filesystem\Driver as FilesystemDriver;

/**
 * 图片样式结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/19 10:03 AM ImageStyleResult.php $
 * @method static Entity id($op = null, $value = null) 样式名
 * @method static Entity content($op = null, $value = null) 样式内容
 * @method static Entity rule($op = null, $value = null) 样式规则
 */
class ImageStyleResult extends Field
{
    /**
     * 样式名
     * @var string
     */
    public $id;
    
    /**
     * 样式内容
     * @var array
     */
    public $content;
    
    /**
     * 样式规则
     * @var string
     */
    public $rule;
    
    
    /**
     * 获取新{@see Image}实例
     * @return Image
     */
    public function newImage() : Image
    {
        return self::convertContentToImage($this->content);
    }
    
    
    /**
     * 将传入的content填充为一个完整的content
     * @param array $content
     * @return array
     * @throws ReflectionException
     */
    public static function fillContent(array $content = []) : array
    {
        $array = [];
        foreach (Image::getParameterStruct() as $key => $item) {
            $item['status'] = $content[$key]['status'] ?? 0;
            $item['attr']   = array_merge($item['attr'], (array) ($content[$key]['attr'] ?? []));
            $array[$key]    = $item;
        }
        
        return $array;
    }
    
    
    /**
     * 根据图片系统过滤不支持的参数模版
     * @param ImageDriver|FilesystemDriver|string $disk 文件驱动或图片驱动或磁盘名称
     * @param array|ImageStyleResult              $content content
     * @return array|ImageStyleResult
     */
    public static function filterContext($disk, $content)
    {
        if ($disk instanceof ImageDriver) {
            $image = $disk;
        } elseif ($disk instanceof FilesystemDriver) {
            $image = $disk->image();
        } else {
            $image = Filesystem::disk($disk)->image();
        }
        $filter = [];
        foreach ($image->getNotSupportParameters() as $item) {
            if (is_subclass_of($item, BaseParameter::class)) {
                $filter[] = $item::getParameterKey();
            }
        }
        
        $isInfo = $content instanceof ImageStyleResult;
        if ($isInfo) {
            $data = $content->content;
        } else {
            $data = $content;
        }
        
        $array = [];
        foreach ($data as $key => $item) {
            if (in_array($key, $filter)) {
                continue;
            }
            $array[$key] = $item;
        }
        
        if ($isInfo) {
            $content->content = $array;
        }
        
        return $isInfo ? $content : $array;
    }
    
    
    /**
     * 将 content 转为 {@see Image}
     * @param array $content
     * @return Image
     */
    public static function convertContentToImage(array $content) : Image
    {
        $image = Container::getInstance()->make(Image::class, [], true);
        foreach ($content as $key => $item) {
            if (($item['status'] ?? false)) {
                $parameterClass = '\BusyPHP\image\parameter\\' . ucfirst(StringHelper::camel($key)) . 'Parameter';
                if (!class_exists($parameterClass)) {
                    continue;
                }
                
                $parameter = Container::getInstance()->make($parameterClass, [], true);
                foreach (($item['attr'] ?? []) as $method => $value) {
                    try {
                        Container::getInstance()->invokeMethod([
                            $parameter,
                            'set' . ucfirst(StringHelper::camel($method))
                        ], [$value]);
                    } catch (FuncNotFoundException $e) {
                    }
                }
                
                $image->add($parameter);
            }
        }
        
        return $image;
    }
    
    
    /**
     * 将 {@see Image} 转为 content
     * @param Image $image
     * @return array
     * @throws ReflectionException
     */
    public static function convertImageToContent(Image $image) : array
    {
        $array = [];
        foreach ($image->getParameters() as $item) {
            $array[$item::getParameterKey()] = [
                'status' => 1,
                'attr'   => $item::getParameterAttrs($item)
            ];
        }
        
        return $array;
    }
}