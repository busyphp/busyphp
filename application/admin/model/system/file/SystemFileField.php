<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\file;

use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClassField;
use BusyPHP\app\admin\setting\StorageSetting;
use BusyPHP\helper\AppHelper;
use BusyPHP\helper\TransHelper;
use BusyPHP\interfaces\ModelValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\annotation\field\Filter;
use BusyPHP\model\annotation\field\Ignore;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\annotation\field\Validator;
use BusyPHP\model\annotation\field\ValueBindField;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use think\facade\Filesystem;
use think\filesystem\Driver;
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
 * @method static Entity typeName($op = null, $value = null) 附件类型名称;
 * @method static Entity clientName($op = null, $value = null) 客户端名称;
 * @method static Entity formatCreateTime($op = null, $value = null) 格式化的创建时间;
 * @method static Entity sizeUnit($op = null, $value = null) 附件大小单位;
 * @method static Entity sizeNum($op = null, $value = null) 附件大小;
 * @method static Entity formatSize($op = null, $value = null) 格式化的附件大小;
 * @method static Entity filename($op = null, $value = null) 附件名称;
 * @method static Entity classInfo($op = null, $value = null) 分类信息;
 * @method static Entity className($op = null, $value = null) 分类名称;
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
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
class SystemFileField extends Field implements ModelValidateInterface
{
    /**
     * ID
     * @var int
     */
    #[Validator(name: Validator::REQUIRE)]
    #[Validator(name: Validator::GT, rule: 0)]
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
     */
    #[Validator(name: Validator::REQUIRE)]
    public $classType;
    
    /**
     * 文件分类对应的业务值
     * @var string
     */
    #[Filter(filter: 'trim')]
    public $classValue;
    
    /**
     * 所属客户端
     * @var string
     */
    public $client;
    
    /**
     * URL
     * @var string
     */
    #[Validator(name: Validator::REQUIRE)]
    #[Filter(filter: 'trim')]
    public $url;
    
    /**
     * URL-MD5
     * @var string
     */
    #[Validator(name: Validator::REQUIRE)]
    public $urlHash;
    
    /**
     * 文件路径
     * @var string
     */
    #[Validator(name: Validator::REQUIRE)]
    #[Filter(filter: 'trim')]
    public $path;
    
    /**
     * 磁盘名称
     * @var string
     */
    #[Validator(name: Validator::REQUIRE)]
    #[Filter(filter: 'trim')]
    public $disk;
    
    /**
     * 文件大小
     * @var int
     */
    #[Validator(name: Validator::EGT, rule: 0)]
    public $size;
    
    /**
     * 文件MimeType
     * @var string
     */
    #[Filter(filter: 'trim')]
    public $mimeType;
    
    /**
     * 文件扩展名
     * @var string
     */
    #[Validator(name: Validator::REQUIRE)]
    #[Filter(filter: 'trim')]
    #[Filter(filter: 'strtolower')]
    public $extension;
    
    /**
     * 文件名
     * @var string
     */
    #[Validator(name: Validator::REQUIRE)]
    #[Filter(filter: 'trim')]
    public $name;
    
    /**
     * 文件MD5
     * @var string
     */
    #[Validator(name: Validator::REQUIRE)]
    #[Filter(filter: 'trim')]
    public $hash;
    
    /**
     * 文件宽度
     * @var int
     */
    #[Validator(name: Validator::EGT, rule: 0)]
    public $width;
    
    /**
     * 文件高度
     * @var int
     */
    #[Validator(name: Validator::EGT, rule: 0)]
    public $height;
    
    /**
     * 附件类型名称
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'type'])]
    #[Filter([SystemFile::class, 'getTypes'])]
    public $typeName;
    
    /**
     * 格式化的创建时间
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'createTime'])]
    #[Filter([TransHelper::class, 'date'])]
    public $formatCreateTime;
    
    /**
     * 附件大小单位
     * @var string
     */
    #[Ignore]
    public $sizeUnit;
    
    /**
     * 附件大小
     * @var int
     */
    #[Ignore]
    public $sizeNum;
    
    /**
     * 格式化的附件大小
     * @var string
     */
    #[Ignore]
    public $formatSize;
    
    /**
     * 附件名称
     * @var string
     */
    #[Ignore]
    public $filename;
    
    /**
     * 客户端名称
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'client'])]
    #[Filter([AppHelper::class, 'getName'])]
    public $clientName;
    
    /**
     * 分类信息
     * @var SystemFileClassField
     */
    #[Ignore]
    public $classInfo;
    
    /**
     * 文件分类名称
     * @var string
     */
    #[Ignore]
    public $className;
    
    /**
     * 存储引擎
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'disk'])]
    #[Filter([StorageSetting::class, 'getDisks'], 'name')]
    public $diskName;
    
    
    protected function onParseAfter()
    {
        $this->classInfo = SystemFileClass::instance()->getList()[$this->classType] ?? null;
        $this->className = $this->classInfo->name ?? '';
        
        $sizes            = TransHelper::formatBytes($this->size, true);
        $this->sizeUnit   = $sizes['unit'];
        $this->sizeNum    = $sizes['number'];
        $this->formatSize = "$this->sizeNum $this->sizeUnit";
        $this->filename   = pathinfo($this->url, PATHINFO_BASENAME);
        $this->diskName   = $this->diskName ?: $this->disk;
    }
    
    
    /**
     * @return Driver
     */
    public function filesystem() : Driver
    {
        return Filesystem::disk($this->disk);
    }
    
    
    /**
     * @inheritDoc
     * @throws
     */
    public function onModelValidate(Model $model, Validate $validate, string $scene, $data = null)
    {
        $classList = SystemFileClass::instance()->getList();
        $validate->append($this::classType(), ValidateRule::init()->in(array_keys($classList)));
        
        if ($scene == SystemFile::SCENE_CREATE) {
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