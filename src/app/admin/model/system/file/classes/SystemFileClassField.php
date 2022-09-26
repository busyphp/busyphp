<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\file\classes;

use BusyPHP\app\admin\setting\StorageSetting;
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
 * @method static Entity extensions($op = null, $value = null) 限制文件格式
 * @method static Entity maxSize($op = null, $value = null) 限制文件大小
 * @method static Entity mimetype($op = null, $value = null) 限制文件mimetype
 * @method static Entity style($op = null, $value = null) 样式
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
     * 限制文件格式
     * @var string
     */
    public $extensions;
    
    /**
     * 限制文件大小
     * @var int
     */
    public $maxSize;
    
    /**
     * 限制文件mimetype
     * @var string
     */
    public $mimetype;
    
    /**
     * 样式
     * @var string
     */
    public $style;
    
    /**
     * 是否系统类型
     * @var int
     */
    public $system;
    
    
    /**
     * 设置
     * @param int $id
     * @throws ParamInvalidException
     */
    public function setId(int $id)
    {
        $this->id = $id;
        if ($this->id < 1) {
            throw new ParamInvalidException('id');
        }
    }
    
    
    /**
     * 设置分类名称
     * @param string $name
     * @throws VerifyException
     */
    public function setName(string $name)
    {
        $this->name = trim($name);
        if (!$this->name) {
            throw new VerifyException('请输入文件分类名称', 'name');
        }
    }
    
    
    /**
     * 设置分类标识
     * @param string $var
     */
    public function setVar(string $var)
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
    }
    
    
    /**
     * 设置附件类型
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = trim($type);
        if (!$this->type) {
            throw new VerifyException('请选择文件类型', 'type');
        }
        if (!in_array($this->type, array_keys(SystemFile::getTypes()))) {
            throw new VerifyException('请选择有效的文件类型', 'type');
        }
    }
    
    
    /**
     * 设置系统
     * @param int $system
     */
    public function setSystem(int $system)
    {
        $this->system = TransHelper::toBool($system);
    }
    
    
    /**
     * 设置限制文件格式
     * @param string $extensions
     */
    public function setExtensions(string $extensions)
    {
        $this->extensions = StorageSetting::parseExtensions(trim($extensions));
    }
    
    
    /**
     * 设置限制文件大小
     * @param int $maxSize
     */
    public function setMaxSize(int $maxSize)
    {
        $this->maxSize = floatval($maxSize);
    }
    
    
    /**
     * 设置限制文件mimetype
     * @param string $mimetype
     */
    public function setMimetype(string $mimetype)
    {
        $this->mimetype = StorageSetting::parseExtensions(trim($mimetype));
    }
    
    
    /**
     * 设置图片样式
     * @param array $style
     */
    public function setStyle(array $style)
    {
        $this->style = json_encode($style, JSON_UNESCAPED_UNICODE);
    }
}