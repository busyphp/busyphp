<?php

namespace BusyPHP\app\admin\model\system\file\classes;

use BusyPHP\exception\VerifyException;
use BusyPHP\model\Field;
use BusyPHP\helper\util\Regex;
use BusyPHP\helper\util\Transform;
use BusyPHP\app\admin\model\system\file\SystemFile;


/**
 * 附件分类模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2018 busy^life <busy.life@qq.com>
 * @version $Id: 2018-01-18 下午12:53 SystemFileClassField.php busy^life $
 */
class SystemFileClassField extends Field
{
    /** @var int */
    public $id = null;
    
    /** @var string 分类名称 */
    public $name = null;
    
    /** @var string 分类标识 */
    public $var = null;
    
    /** @var string 附件类型 */
    public $type = null;
    
    /** @var int 前台显示 */
    public $homeShow = null;
    
    /** @var int 后台显示 */
    public $adminShow = null;
    
    /** @var int 自定义排序 */
    public $sort = null;
    
    /** @var string 允许的后缀 */
    public $suffix = null;
    
    /** @var int 允许的大小 -1 继承基本设置 0 不限 */
    public $size = null;
    
    /** @var int 允许前台上传 */
    public $homeUpload = null;
    
    /** @var int 前台必须登录上传 */
    public $homeLogin = null;
    
    /** @var string 允许的mimetype */
    public $mimetype = null;
    
    /** @var int 是否缩放图片 */
    public $isThumb = null;
    
    /** @var int 缩放方式 */
    public $thumbType = null;
    
    /** @var int 缩图宽度 */
    public $width = null;
    
    /** @var int 缩图高度 */
    public $height = null;
    
    /** @var int 缩图后是否删除原图 */
    public $deleteSource = null;
    
    /** @var int 是否加水印 */
    public $watermark = null;
    
    /** @var int 系统 */
    public $isSystem = null;
    
    
    /**
     * 设置
     * @param int $id
     * @return $this
     * @throws VerifyException
     */
    public function setId($id)
    {
        $this->id = floatval($id);
        if ($this->id < 1) {
            throw new VerifyException('缺少参数', 'id');
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
            throw new VerifyException('请输入分类名称', 'name');
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
            throw new VerifyException('请输入分类标识', 'var');
        }
        if (!Regex::account($this->var)) {
            throw new VerifyException('分类标识格式有误，只能包含英文、数字、下划线', 'var');
        }
        
        
        // 只能是英文开头
        $this->var = parse_name($this->var, 0, false);
        if (!Regex::english(substr($this->var, 0, 1))) {
            throw new VerifyException('分类标识不能为数字或下划线开头', 'var');
        }
        
        
        // 查重
        $model      = SystemFileClass::init();
        $where      = new self();
        $where->var = $this->var;
        if ($this->id > 0) {
            $where->id = array('neq', $this->id);
        }
        if ($model->whereof($where)->findData()) {
            throw new VerifyException('分类标识不能重复', 'var');
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
            throw new VerifyException('请选择附件类型', 'type');
        }
        if (!in_array($this->type, array_keys(SystemFile::getTypes()))) {
            throw new VerifyException('请选择有效的附件类型', 'type');
        }
        
        return $this;
    }
    
    
    /**
     * 设置前台显示
     * @param int $homeShow
     * @return $this
     */
    public function setHomeShow($homeShow)
    {
        $this->homeShow = Transform::dataToBool($homeShow);
        
        return $this;
    }
    
    
    /**
     * 设置后台显示
     * @param int $adminShow
     * @return $this
     */
    public function setAdminShow($adminShow)
    {
        $this->adminShow = Transform::dataToBool($adminShow);
        
        return $this;
    }
    
    
    /**
     * 设置自定义排序
     * @param int $sort
     * @return $this
     */
    public function setSort($sort)
    {
        $this->sort = floatval($sort);
        
        return $this;
    }
    
    
    /**
     * 设置允许的后缀
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix)
    {
        $this->suffix = trim($suffix);
        
        return $this;
    }
    
    
    /**
     * 设置允许的大小 -1 继承基本设置 0 不限
     * @param int $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = floatval($size);
        
        return $this;
    }
    
    
    /**
     * 设置允许前台上传
     * @param int $homeUpload
     * @return $this
     */
    public function setHomeUpload($homeUpload)
    {
        $this->homeUpload = Transform::dataToBool($homeUpload);
        
        return $this;
    }
    
    
    /**
     * 设置前台必须登录上传
     * @param int $homeLogin
     * @return $this
     */
    public function setHomeLogin($homeLogin)
    {
        $this->homeLogin = Transform::dataToBool($homeLogin);
        
        return $this;
    }
    
    
    /**
     * 设置允许的 mimetype
     * @param string $mimetype
     * @return $this
     */
    public function setMimetype($mimetype)
    {
        $this->mimetype = trim($mimetype);
        
        return $this;
    }
    
    
    /**
     * 设置是否缩放图片
     * @param int $isThumb
     * @return $this
     */
    public function setIsThumb($isThumb)
    {
        $this->isThumb = Transform::dataToBool($isThumb);
        
        return $this;
    }
    
    
    /**
     * 设置缩放方式
     * @param int $thumbType
     * @return $this
     */
    public function setThumbType($thumbType)
    {
        $this->thumbType = floatval($thumbType);
        
        return $this;
    }
    
    
    /**
     * 设置缩图宽度
     * @param int $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = floatval($width);
        
        return $this;
    }
    
    
    /**
     * 设置缩图高度
     * @param int $height
     * @return $this
     */
    public function setHeight($height)
    {
        $this->height = floatval($height);
        
        return $this;
    }
    
    
    /**
     * 设置缩图后是否删除原图
     * @param int $deleteSource
     * @return $this
     */
    public function setDeleteSource($deleteSource)
    {
        $this->deleteSource = Transform::dataToBool($deleteSource);
        
        return $this;
    }
    
    
    /**
     * 设置是否加水印
     * @param int $watermark
     * @return $this
     */
    public function setWatermark($watermark)
    {
        $this->watermark = Transform::dataToBool($watermark);
        
        return $this;
    }
    
    
    /**
     * 设置系统
     * @param int $isSystem
     * @return $this
     */
    public function setIsSystem($isSystem)
    {
        $this->isSystem = Transform::dataToBool($isSystem);
        
        return $this;
    }
}