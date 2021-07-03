<?php

namespace BusyPHP\app\admin\model\system\file;

use BusyPHP\App;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\util\Transform;

/**
 * 上传附件容器
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午3:24 SystemFileCreate.php $
 */
class SystemFileCreate extends SystemFileField
{
    //+--------------------------------------
    //| 私有
    //+--------------------------------------
    private $realFilePath = null;
    
    
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
     * 设置文件名
     * @param string $url
     * @return $this
     * @throws VerifyException
     */
    public function setUrl($url)
    {
        $this->url = trim($url);
        if (!$this->url) {
            throw new VerifyException('附件地址不能为空', 'url');
        }
        
        $this->realFilePath = App::urlToPath($this->url);
        if (!is_file($this->realFilePath)) {
            throw new VerifyException('附件地址无效', 'url');
        }
        
        return $this;
    }
    
    
    /**
     * 设置文件大小（bytes）
     * @param int $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = floatval($size);
        
        return $this;
    }
    
    
    /**
     * 设置文件MimeType
     * @param string $mimeType
     * @return $this
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = trim($mimeType);
        
        return $this;
    }
    
    
    /**
     * 设置文件后缀
     * @param string $extension
     * @return $this
     */
    public function setExtension($extension)
    {
        $this->extension = trim($extension);
        
        return $this;
    }
    
    
    /**
     * 设置文件原名
     * @param string $name
     * @return $this
     * @throws VerifyException
     */
    public function setName($name)
    {
        $this->name = trim($name);
        if (!$this->name) {
            throw new VerifyException('附件名称不能为空', 'name');
        }
        
        return $this;
    }
    
    
    /**
     * 设置标记类型
     * @param string $markType
     * @return $this
     */
    public function setMarkType($markType)
    {
        $this->markType = trim($markType);
        
        return $this;
    }
    
    
    /**
     * 设置标识值
     * @param string $markValue
     * @return $this
     */
    public function setMarkValue($markValue)
    {
        $this->markValue = trim($markValue);
        
        return $this;
    }
    
    
    /**
     * 设置文件的哈希验证字符串
     * @param string $hash
     * @return $this
     */
    public function setHash($hash)
    {
        $this->hash = trim($hash);
        
        return $this;
    }
    
    
    /**
     * 设置会员ID
     * @param int $userid
     * @return $this
     */
    public function setUserid($userid)
    {
        $this->userid = floatval($userid);
        
        return $this;
    }
    
    
    /**
     * 设置后台上传
     * @param int $isAdmin
     * @return $this
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = Transform::dataToBool($isAdmin);
        
        return $this;
    }
    
    
    /**
     * 设置文件分类
     * @param string $classify
     * @return $this
     */
    public function setClassify($classify)
    {
        $this->classify = trim($classify);
        
        return $this;
    }
    
    
    /**
     * 是否缩放资源
     * @param int $isThumb
     * @return $this
     */
    public function setIsThumb($isThumb)
    {
        $this->isThumb = Transform::dataToBool($isThumb);
        
        return $this;
    }
    
    
    /**
     * 设置缩放资源源文件ID
     * @param int $thumbId
     * @return $this
     * @throws VerifyException
     */
    public function setThumbId($thumbId)
    {
        $this->thumbId = floatval($thumbId);
        if ($this->thumbId < 1) {
            throw new VerifyException('源文件ID不能为空', 'thumb_id');
        }
        
        return $this;
    }
    
    
    /**
     * 设置缩放资源宽度
     * @param int $thumbWidth
     * @return $this
     */
    public function setThumbWidth($thumbWidth)
    {
        $this->thumbWidth = intval($thumbWidth);
        
        return $this;
    }
    
    
    /**
     * 设置缩放资源高度
     * @param int $thumbHeight
     * @return $this
     */
    public function setThumbHeight($thumbHeight)
    {
        $this->thumbHeight = intval($thumbHeight);
        
        return $this;
    }
    
    
    /**
     * @return array
     * @throws VerifyException
     */
    public function getDBData()
    {
        $this->setUrl($this->url);
        
        // 附件大小
        if (is_null($this->size) || $this->size <= 0) {
            $this->size = filesize($this->realFilePath);
            if ($this->size <= 0) {
                throw new VerifyException('附件无效', 'size');
            }
        }
        
        // 附件后缀
        if (!$this->extension) {
            $this->extension = pathinfo($this->realFilePath, PATHINFO_EXTENSION);
        }
        
        // hash
        if (!$this->hash) {
            $this->hash = md5_file($this->realFilePath);
        }
        
        
        // 附件类型
        if (!$this->classify) {
            $this->classify = SystemFile::FILE_TYPE_FILE;
        }
        
        return parent::getDBData();
    }
}