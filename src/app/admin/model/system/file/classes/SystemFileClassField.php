<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\file\classes;

use BusyPHP\app\admin\setting\UploadSetting;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\StringHelper;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use BusyPHP\helper\RegexHelper;
use BusyPHP\helper\TransHelper;
use BusyPHP\app\admin\model\system\file\SystemFile;

/**
 * 附件分类模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午4:32 SystemFileClassField.php $
 * @method static Entity id($op = null, $value = null) ID
 * @method static Entity name($op = null, $value = null) 分类名称
 * @method static Entity var($op = null, $value = null) 分类标识
 * @method static Entity type($op = null, $value = null) 附件类型
 * @method static Entity sort($op = null, $value = null) 自定义排序
 * @method static Entity allowExtensions($op = null, $value = null) 留空使用系统设置，多个用英文逗号隔开
 * @method static Entity maxSize($op = null, $value = null) 单位MB，0使用系统默认设置，0以上按照该设置
 * @method static Entity mimeType($op = null, $value = null) 允许的MimeType，多个用英文逗号分割
 * @method static Entity thumbType($op = null, $value = null) 缩图方式
 * @method static Entity thumbWidth($op = null, $value = null) 缩图宽度
 * @method static Entity thumbHeight($op = null, $value = null) 缩图高度
 * @method static Entity watermark($op = null, $value = null) 是否加水印
 * @method static Entity system($op = null, $value = null) 是否系统类型
 */
class SystemFileClassField extends Field
{
    /**
     * ID
     * @var int
     */
    public $id;
    
    /**
     * 分类名称
     * @var string
     */
    public $name;
    
    /**
     * 分类标识
     * @var string
     */
    public $var;
    
    /**
     * 附件类型
     * @var string
     */
    public $type;
    
    /**
     * 自定义排序
     * @var int
     */
    public $sort;
    
    /**
     * 留空使用系统设置，多个用英文逗号隔开
     * @var string
     */
    public $allowExtensions;
    
    /**
     * 单位MB，0使用系统默认设置，0以上按照该设置
     * @var int
     */
    public $maxSize;
    
    /**
     * 允许的MimeType，多个用英文逗号分割
     * @var string
     */
    public $mimeType;
    
    /**
     * 缩图方式
     * @var int
     */
    public $thumbType;
    
    /**
     * 缩图宽度
     * @var int
     */
    public $thumbWidth;
    
    /**
     * 缩图高度
     * @var int
     */
    public $thumbHeight;
    
    /**
     * 是否加水印
     * @var int
     */
    public $watermark;
    
    /**
     * 是否系统类型
     * @var int
     */
    public $system;
    
    
    /**
     * 设置
     * @param int $id
     * @return $this
     * @throws ParamInvalidException
     */
    public function setId($id)
    {
        $this->id = floatval($id);
        if ($this->id < 1) {
            throw new ParamInvalidException('id');
        }
        
        return $this;
    }
    
    
    /**
     * 设置分类名称
     * @param string $name
     * @return $this
     * @throws VerifyException
     */
    public function setName($name)
    {
        $this->name = trim($name);
        if (!$this->name) {
            throw new VerifyException('请输入文件分类名称', 'name');
        }
        
        return $this;
    }
    
    
    /**
     * 设置分类标识
     * @param string $var
     * @return $this
     * @throws VerifyException
     */
    public function setVar($var)
    {
        $this->var = trim($var);
        if (!$this->var) {
            throw new VerifyException('请输入文件分类标识', 'var');
        }
        if (!RegexHelper::account($this->var)) {
            throw new VerifyException('文件分类标识格式有误，只能包含英文、数字、下划线', 'var');
        }
        
        // 只能是英文开头
        $this->var = StringHelper::snake($this->var);
        if (!RegexHelper::english(substr($this->var, 0, 1))) {
            throw new VerifyException('文件分类标识不能为数字或下划线开头', 'var');
        }
        
        return $this;
    }
    
    
    /**
     * 设置附件类型
     * @param string $type
     * @return $this
     * @throws VerifyException
     */
    public function setType($type)
    {
        $this->type = trim($type);
        if (!$this->type) {
            throw new VerifyException('请选择文件类型', 'type');
        }
        if (!in_array($this->type, array_keys(SystemFile::getTypes()))) {
            throw new VerifyException('请选择有效的文件类型', 'type');
        }
        
        return $this;
    }
    
    
    /**
     * 设置系统
     * @param int $system
     * @return $this
     */
    public function setSystem($system)
    {
        $this->system = TransHelper::toBool($system);
        
        return $this;
    }
    
    
    /**
     * 设置留空使用系统设置，多个用英文逗号隔开
     * @param string $allowExtensions
     * @return $this
     */
    public function setAllowExtensions($allowExtensions)
    {
        $this->allowExtensions = UploadSetting::parseExtensions(trim($allowExtensions));
        
        return $this;
    }
    
    
    /**
     * 设置单位MB，0使用系统默认设置，0以上按照该设置
     * @param int $maxSize
     * @return $this
     */
    public function setMaxSize($maxSize)
    {
        $this->maxSize = floatval($maxSize);
        
        return $this;
    }
    
    
    /**
     * 设置允许的MimeType，多个用英文逗号分割
     * @param string $mimeType
     * @return $this
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = UploadSetting::parseExtensions(trim($mimeType));
        
        return $this;
    }
    
    
    /**
     * 设置缩图方式
     * @param int $thumbType
     * @return $this
     */
    public function setThumbType($thumbType)
    {
        $this->thumbType = intval($thumbType);
        
        return $this;
    }
    
    
    /**
     * 设置缩图宽度
     * @param int $thumbWidth
     * @return $this
     */
    public function setThumbWidth($thumbWidth)
    {
        $this->thumbWidth = intval($thumbWidth);
        
        return $this;
    }
    
    
    /**
     * 设置缩图高度
     * @param int $thumbHeight
     * @return $this
     */
    public function setThumbHeight($thumbHeight)
    {
        $this->thumbHeight = intval($thumbHeight);
        
        return $this;
    }
    
    
    /**
     * 设置是否加水印
     * @param int $watermark
     * @return $this
     */
    public function setWatermark($watermark)
    {
        $this->watermark = intval($watermark);
        
        return $this;
    }
}