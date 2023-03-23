<?php
namespace BusyPHP\facade;

use BusyPHP\Image\Driver;
use BusyPHP\image\parameter\BaseParameter;
use BusyPHP\image\parameter\ImageParameter;
use BusyPHP\image\parameter\QualityParameter;
use BusyPHP\image\parameter\TextParameter;
use BusyPHP\image\result\ProcessResult;
use BusyPHP\image\result\SaveResult;
use think\Facade;
use think\Response;
use think\route\Url;

/**
 * 图片处理工厂类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/22 10:48 Image.php $
 * @mixin \BusyPHP\Image
 * @see \BusyPHP\Image
 * @method static \BusyPHP\Image path(string $path) 设置图片路径
 * @method static \BusyPHP\Image add(BaseParameter $parameter) 添加参数模板
 * @method static \BusyPHP\Image crop(int $width, int $height) 指定尺寸进行缩放裁剪某一边可能会被裁掉
 * @method static \BusyPHP\Image cut(int $width, int $height, int $dx = 0, int $dy = 0) 指定尺寸进行普通裁剪
 * @method static \BusyPHP\Image zoom(int $width, int $height, bool $enlarge = false) 按照尺寸进行缩放，某一项为0则自动计算
 * @method static \BusyPHP\Image zoomLose(int $width, int $height) 不保持比例强制缩放
 * @method static \BusyPHP\Image zoomFill(int $width, int $height, string $color = BaseParameter::DEFAULT_COLOR) 缩放为指定宽高矩形内的最大图片
 * @method static \BusyPHP\Image radius(int $radius, bool $inside = false) 圆角裁剪
 * @method static \BusyPHP\Image radiusXY(int $rx, int $ry, bool $inside = false) 圆角裁剪
 * @method static \BusyPHP\Image rotate(int $rotate, string $color = '') 图像旋转
 * @method static \BusyPHP\Image autoOrient() 根据原图 EXIF 信息将图片自适应旋转回正
 * @method static \BusyPHP\Image format(string $format) 格式转换
 * @method static \BusyPHP\Image quality(int $quality, int $type = QualityParameter::TYPE_ABSOLUTE) 质量变换
 * @method static \BusyPHP\Image interlace() 渐进显示
 * @method static \BusyPHP\Image blur(int $radius, int $sigma) 高斯模糊
 * @method static \BusyPHP\Image gamma(int $gamma) 伽马校正，范围0-100
 * @method static \BusyPHP\Image pixelate(int $pixelate) 像素化，0以上
 * @method static \BusyPHP\Image invert() 翻转颜色
 * @method static \BusyPHP\Image flip(string $flip) 图片反转
 * @method static \BusyPHP\Image bright(int $bright) 亮度 范围-100至100
 * @method static \BusyPHP\Image contrast(int $contrast) 对比度 范围-100至100
 * @method static \BusyPHP\Image sharpen(int $sharpen) 锐化 范围0至100
 * @method static \BusyPHP\Image grayscale() 灰度图
 * @method static \BusyPHP\Image image(ImageParameter $parameter) 添加指定图片到被处理的图片中
 * @method static \BusyPHP\Image text(TextParameter $parameter) 添加指定文字到被处理的图片中
 * @method static \BusyPHP\Image stripMeta() 去除图片元信息[含EXIF信息]
 * @method static \BusyPHP\Image style(string $style) 设置图片处理样式
 * @method static \BusyPHP\Image download(string $filename = '') 设置是否下载 与 cache 互斥
 * @method static \BusyPHP\Image cache(int $lifetime) 设置缓存多少秒 与 download 互斥
 * @method static \BusyPHP\Image disk(string $disk) 指定磁盘系统
 * @method static Driver getDriver() 获取图片处理驱动
 * @method static ProcessResult process() 处理图片
 * @method static SaveResult save(string $destination = '') 处理并保存
 * @method static Response response() 处理并响应
 * @method static Url url() 生成在线处理URL
 */
class Image extends Facade
{
    protected static $alwaysNewInstance = true;
    
    
    protected static function getFacadeClass()
    {
        return \BusyPHP\Image::class;
    }
}