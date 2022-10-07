<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\file;

use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\helper\AppHelper;
use BusyPHP\interfaces\ModelSceneValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use think\Validate;
use think\validate\ValidateRule;

/**
 * 文件管理模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/6 下午2:54 下午 SystemFileField.php $
 * @method static Entity id(mixed $op = null, mixed $condition = null) ID
 * @method static Entity createTime(mixed $op = null, mixed $condition = null) 上传时间
 * @method static Entity userId(mixed $op = null, mixed $condition = null) 会员ID
 * @method static Entity pending(mixed $op = null, mixed $condition = null) 是否上传中
 * @method static Entity fast(mixed $op = null, mixed $condition = null) 是否秒传
 * @method static Entity type(mixed $op = null, mixed $condition = null) 文件类型
 * @method static Entity classType(mixed $op = null, mixed $condition = null) 文件分类
 * @method static Entity classValue(mixed $op = null, mixed $condition = null) 文件分类对应的业务值
 * @method static Entity client(mixed $op = null, mixed $condition = null) 所属客户端
 * @method static Entity url(mixed $op = null, mixed $condition = null) 文件地址
 * @method static Entity urlHash(mixed $op = null, mixed $condition = null) URL HASH
 * @method static Entity path(mixed $op = null, mixed $condition = null) 文件路径
 * @method static Entity disk(mixed $op = null, mixed $condition = null) 磁盘名称
 * @method static Entity size(mixed $op = null, mixed $condition = null) 文件大小[字节]
 * @method static Entity mimeType(mixed $op = null, mixed $condition = null) 文件MimeType
 * @method static Entity extension(mixed $op = null, mixed $condition = null) 文件扩展名
 * @method static Entity name(mixed $op = null, mixed $condition = null) 文件名
 * @method static Entity hash(mixed $op = null, mixed $condition = null) 文件的哈希值
 * @method static Entity width(mixed $op = null, mixed $condition = null) 文件宽度[像素]
 * @method static Entity height(mixed $op = null, mixed $condition = null) 文件高度[像素]
 * @method $this setId(mixed $id) 设置ID
 * @method $this setCreateTime(mixed $createTime) 设置上传时间
 * @method $this setUserId(mixed $userId) 设置会员ID
 * @method $this setPending(mixed $pending) 设置是否上传中
 * @method $this setFast(mixed $fast) 设置是否秒传
 * @method $this setType(mixed $type) 设置文件类型
 * @method $this setClassType(mixed $classType) 设置文件分类
 * @method $this setClassValue(mixed $classValue) 设置文件分类对应的业务值
 * @method $this setClient(mixed $client) 设置所属客户端
 * @method $this setUrl(mixed $url) 设置文件地址
 * @method $this setUrlHash(mixed $urlHash) 设置URL HASH
 * @method $this setPath(mixed $path) 设置文件路径
 * @method $this setDisk(mixed $disk) 设置磁盘名称
 * @method $this setSize(mixed $size) 设置文件大小[字节]
 * @method $this setMimeType(mixed $mimeType) 设置文件MimeType
 * @method $this setExtension(mixed $extension) 设置文件扩展名
 * @method $this setName(mixed $name) 设置文件名
 * @method $this setHash(mixed $hash) 设置文件的哈希值
 * @method $this setWidth(mixed $width) 设置文件宽度[像素]
 * @method $this setHeight(mixed $height) 设置文件高度[像素]
 */
class SystemFileField extends Field implements ModelSceneValidateInterface
{
    /**
     * ID
     * @var int
     * @busy-validate require
     * @busy-validate gt:0
     */
    public $id;
    
    /**
     * 上传时间
     * @var int
     */
    public $createTime;
    
    /**
     * 会员ID
     * @var int
     */
    public $userId;
    
    /**
     * 是否上传中
     * @var bool
     */
    public $pending;
    
    /**
     * 是否秒传
     * @var bool
     */
    public $fast;
    
    /**
     * 文件类型
     * @var string
     */
    public $type;
    
    /**
     * 文件分类
     * @var string
     * @busy-validate require
     */
    public $classType;
    
    /**
     * 文件分类对应的业务值
     * @var string
     * @busy-filter trim
     */
    public $classValue;
    
    /**
     * 所属客户端
     * @var string
     */
    public $client;
    
    /**
     * URL
     * @var string
     * @busy-validate require
     * @busy-filter trim
     */
    public $url;
    
    /**
     * URL-MD5
     * @var string
     * @busy-validate require
     */
    public $urlHash;
    
    /**
     * 文件路径
     * @var string
     * @busy-validate require
     * @busy-filter trim
     */
    public $path;
    
    /**
     * 磁盘名称
     * @var string
     * @busy-validate require
     */
    public $disk;
    
    /**
     * 文件大小
     * @var int
     * @busy-validate egt:0
     */
    public $size;
    
    /**
     * 文件MimeType
     * @var string
     * @busy-filter trim
     */
    public $mimeType;
    
    /**
     * 文件扩展名
     * @var string
     * @busy-validate require
     * @busy-filter trim
     * @busy-filter strtolower
     */
    public $extension;
    
    /**
     * 文件名
     * @var string
     * @busy-validate require
     * @busy-filter trim
     */
    public $name;
    
    /**
     * 文件MD5
     * @var string
     * @busy-validate require
     * @busy-filter trim
     */
    public $hash;
    
    /**
     * 文件宽度
     * @var int
     * @busy-validate egt:0
     */
    public $width;
    
    /**
     * 文件高度
     * @var int
     * @busy-validate egt:0
     */
    public $height;
    
    
    /**
     * @inheritDoc
     * @throws
     */
    public function onModelSceneValidate(Model $model, Validate $validate, string $name, $data = null)
    {
        $classList = SystemFileClass::init()->getList();
        $validate->append($this::classType(), ValidateRule::in(array_keys($classList)));
        
        if ($name == SystemFile::SCENE_CREATE) {
            $this->setCreateTime(time());
            
            if ($this->classType) {
                $this->setType($classList[$this->classType]->type);
            }
            
            if (!$this->client) {
                $this->setClient(AppHelper::getClient());
            }
            
            if (!$this->extension && $this->path) {
                $this->setExtension(pathinfo($this->path, PATHINFO_EXTENSION));
            }
            
            if ($this->url) {
                $this->setUrlHash(md5($this->url));
            }
            
            $this->exclude($validate, $this::id());
            
            return true;
        }
        
        return false;
    }
}