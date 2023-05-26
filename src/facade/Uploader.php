<?php
declare(strict_types = 1);

namespace BusyPHP\facade;

use BusyPHP\uploader\Driver;
use BusyPHP\uploader\interfaces\DataInterface;
use BusyPHP\uploader\result\UploadResult;
use Closure;
use think\Facade;
use think\filesystem\Driver as FilesystemDriver;

/**
 * 文件上传工厂类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/5/25 23:52 Uploader.php $
 * @mixin \BusyPHP\Uploader
 * @mixin \BusyPHP\uploader\Driver
 * @method static Driver driver(string $name = null) 获取上传驱动实例
 * @method static mixed getConfig(string $name = null, mixed $default = null) 获取配置
 * @method static mixed getDriverConfig(string $driver, string $name = null, mixed $default = null) 获取驱动配置
 * @method static Driver path(string|Closure $path) 设置保存路径
 * @method static Driver disk(string|FilesystemDriver $disk) 指定上传磁盘
 * @method static Driver limitMaxsize(int $maxsize) 限制文件大小
 * @method static Driver limitExtensions(array $extensions) 限制文件扩展名
 * @method static Driver limitMimetypes(array $mimetypes) 限制文件mimetype
 * @method static UploadResult upload(DataInterface $data) 执行上传
 */
class Uploader extends Facade
{
    protected static function getFacadeClass()
    {
        return \BusyPHP\Uploader::class;
    }
}