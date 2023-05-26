<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\interfaces;

/**
 * 前端上传接口类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/24 3:39 PM FrontInterface.php $
 */
interface FrontInterface
{
    /**
     * 获取临时安全令牌
     * @param string $path 文件路径
     * @param int    $expire 过期时间秒
     * @return array
     */
    public function getFrontTmpToken(string $path, int $expire = 1800) : array;
    
    
    /**
     * 获取前端上传URL
     * @param bool $ssl
     * @return string
     */
    public function getFrontServerUrl(bool $ssl = false) : string;
    
    
    /**
     * 准备上传
     * @param string $path 存储路径
     * @param string $basename 文件原名称(含扩展名)
     * @param string $md5 文件MD5值
     * @param int    $filesize 文件大小
     * @param string $mimetype mimetype
     * @param bool   $part 是否启用分块上传
     * @return string uploadId
     */
    public function frontPrepareUpload(string $path, string $basename, string $md5, int $filesize, string $mimetype = '', bool $part = false) : string;
    
    
    /**
     * 完成上传
     * @param string $path 存储路径
     * @param string $uploadId uploadId
     * @param array  $parts 分块数据
     * @return array{mimetype: string, filesize: int, width: int, height: int}
     */
    public function frontDoneUpload(string $path, string $uploadId, array $parts) : array;
}