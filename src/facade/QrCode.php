<?php
declare(strict_types = 1);

namespace BusyPHP\facade;

use think\Facade;
use think\Response;
use think\route\Url;

/**
 * 二维码工厂类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/25 16:41 QrCode.php $
 * @mixin \BusyPHP\QrCode
 * @see \BusyPHP\QrCode
 * @method static \BusyPHP\QrCode text(string $text) 设置二维码内容
 * @method static \BusyPHP\QrCode level(string $level) 设置二维码容错率
 * @method static \BusyPHP\QrCode margin(int $margin) 设置二维码空白间距
 * @method static \BusyPHP\QrCode size(int $size) 设置二维码尺寸
 * @method static \BusyPHP\QrCode logo(string $logo, int $width = 0, int $height = 0) 设置二维码LOGO
 * @method static \BusyPHP\QrCode format(string $format) 设置二维码输出格式
 * @method static \BusyPHP\QrCode download(string $filename) 设置response的时候执行下载
 * @method static \BusyPHP\QrCode cache(int $lifetime) 设置response的时候进行缓存的秒数
 * @method static \BusyPHP\QrCode http() 通过HTTP参数执行response，以配合 Captcha::url() 生成的在线二维码链接
 * @method static Response response() 输出到浏览器
 * @method static string build(bool $dataUri = false) 构建数据
 * @method static string save(string $path) 保存到指定路径
 * @method static Url url() 生成在线二维码链接
 */
class QrCode extends Facade
{
    // +----------------------------------------------------
    // + 识别率级别
    // +----------------------------------------------------
    /** @var string 还原率7％ */
    const LEVEL_LOW = \BusyPHP\QrCode::LEVEL_LOW;
    
    /** @var string 还原率15% */
    const LEVEL_MEDIUM = \BusyPHP\QrCode::LEVEL_MEDIUM;
    
    /** @var string 还原率25% */
    const LEVEL_QUARTILE = \BusyPHP\QrCode::LEVEL_QUARTILE;
    
    /** @var string 还原率30% */
    const LEVEL_HIGH = \BusyPHP\QrCode::LEVEL_HIGH;
    
    // +----------------------------------------------------
    // + 二维码格式
    // +----------------------------------------------------
    /** @var string PNG */
    const FORMAT_PNG = \BusyPHP\QrCode::FORMAT_PNG;
    
    /** @var string SVG */
    const FORMAT_SVG = \BusyPHP\QrCode::FORMAT_SVG;
    
    /** @var string BINARY */
    const FORMAT_BINARY = \BusyPHP\QrCode::FORMAT_BINARY;
    
    /** @var string ESP */
    const FORMAT_EPS = \BusyPHP\QrCode::FORMAT_EPS;
    
    /** @var string PDF */
    const FORMAT_PDF = \BusyPHP\QrCode::FORMAT_PDF;
    
    protected static $alwaysNewInstance = true;
    
    
    protected static function getFacadeClass()
    {
        return \BusyPHP\QrCode::class;
    }
}