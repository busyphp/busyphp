<?php

namespace BusyPHP\app\admin\model\system\file;

use BusyPHP\model\Field;

/**
 * 文件管理模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/6 下午2:54 下午 SystemFileField.php $
 */
class SystemFileField extends Field
{
    /** @var int */
    public $id = null;
    
    /** @var int 上传时间 */
    public $createTime = null;
    
    /** @var string 文件名 */
    public $url = null;
    
    /** @var string URL HASH */
    public $urlHash = null;
    
    /** @var int 文件大小（bytes） */
    public $size = null;
    
    /** @var string */
    public $mimeType = null;
    
    /** @var string 文件类型 */
    public $extension = null;
    
    /** @var string 文件原名 */
    public $name = null;
    
    /** @var string 标记类型 */
    public $markType = null;
    
    /** @var string 标识值 */
    public $markValue = null;
    
    /** @var string 文件的哈希验证字符串 */
    public $hash = null;
    
    /** @var int 会员ID */
    public $userid = null;
    
    /** @var int 后台上传 */
    public $isAdmin = null;
    
    /** @var string 文件分类 */
    public $classify = null;
    
    /** @var int 是否缩放资源 */
    public $isThumb = null;
    
    /** @var int 缩放资源源文件ID */
    public $thumbId = null;
    
    /** @var int 缩放资源宽度 */
    public $thumbWidth = null;
    
    /** @var int 缩放资源高度 */
    public $thumbHeight = null;
}