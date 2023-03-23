<?php
declare(strict_types = 1);

namespace BusyPHP\facade;

use think\Facade;
use think\Response;
use think\route\Url;

/**
 * 验证码工厂类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/25 20:27 Captcha.php $
 * @mixin \BusyPHP\Captcha
 * @see \BusyPHP\Captcha
 * @method static \BusyPHP\Captcha token(string $token) 设置混淆码
 * @method static \BusyPHP\Captcha chars(string $chars) 设置英文数字字符
 * @method static \BusyPHP\Captcha zhChars(string $zhChars) 设置中文字符
 * @method static \BusyPHP\Captcha expire(int $expire) 设置过期时间
 * @method static \BusyPHP\Captcha zh(bool $zh) 设置是否使用中文字符
 * @method static \BusyPHP\Captcha bgImage(bool $bgImage) 设置是否使用背景图
 * @method static \BusyPHP\Captcha fontSize(int $fontSize) 设置字体大小
 * @method static \BusyPHP\Captcha curve(bool $curve) 设置是否绘制线条
 * @method static \BusyPHP\Captcha noise(bool $noise) 设置是否添加杂点
 * @method static \BusyPHP\Captcha height(int $height) 设置验证码高度
 * @method static \BusyPHP\Captcha width(int $width) 设置验证码宽度
 * @method static \BusyPHP\Captcha length(int $length) 设置验证码长度
 * @method static \BusyPHP\Captcha fontFile(string $fontFile) 设置验证码字体文件
 * @method static \BusyPHP\Captcha bg(array $bg) 设置背景颜色
 * @method static \BusyPHP\Captcha bgColor(string $color) 设置背景颜色16进制格式
 * @method static \BusyPHP\Captcha id(string $id) 设置验证码标识
 * @method static \BusyPHP\Captcha reset(bool $reset) 设置验证成功后是否清理验证码
 * @method static \BusyPHP\Captcha http() 解析HTTP参数
 * @method static string build(bool $dataUri = false) 构建验证码数据并把验证码的值保存的session中
 * @method static Response response() 输出到浏览器
 * @method static Url url(string $appName = '') 生成在线验证码URL，支持前置方法：Captcha::id()->width()->height()->url()
 * @method static void check(string $code, bool $errorReset = false, bool $successReset = true) 校验验证码，失败抛出异常
 * @method static void clear() 清理验证码
 */
class Captcha extends Facade
{
    /** @var string 未填写验证码 */
    const VERIFY_EMPTY_CODE = \BusyPHP\Captcha::VERIFY_EMPTY_CODE;
    
    /** @var string 验证码错误 */
    const VERIFY_ERROR = \BusyPHP\Captcha::VERIFY_ERROR;
    
    /** @var string 验证码过期 */
    const VERIFY_EXPIRE = \BusyPHP\Captcha::VERIFY_EXPIRE;
    
    protected static $alwaysNewInstance = true;
    
    
    protected static function getFacadeClass()
    {
        return \BusyPHP\Captcha::class;
    }
}