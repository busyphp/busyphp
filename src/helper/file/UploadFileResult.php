<?php

namespace BusyPHP\helper\file;


/**
 * 附件上传返回容器
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2018 busy^life <busy.life@qq.com>
 * @version $Id: 2018-01-22 下午7:51 UploadFileResult.php busy^life $
 */
class UploadFileResult
{
    /** @var string 附件URL */
    public $url;
    /** @var string 附件真实地址 */
    public $realUrl;
    /** @var int 附件大小（字节） */
    public $size;
    /** @var string 保存的基本目录 */
    public $rootPath;
    /** @var string 保存的子目录名称 */
    public $folderPath;
    /** @var string 保存的路径 */
    public $savePath;
    /** @var string 文件原名 */
    public $name;
    /** @var string 文件新名称 */
    public $filename;
    /** @var string 文件hash */
    public $hash;
    /** @var string 文件扩展名 */
    public $extension;
    /** @var string 文件mimeType */
    public $mimeType;
}