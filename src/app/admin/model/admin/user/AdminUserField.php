<?php

namespace BusyPHP\app\admin\model\admin\user;

use BusyPHP\exception\SQLException;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\util\Regex;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use BusyPHP\helper\util\Transform;
use think\Exception;

/**
 * 管理员表模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午2:47 AdminUserField.php $
 * @method static Entity id($op = null, $value = null) ID
 * @method static Entity username($op = null, $value = null) 帐号
 * @method static Entity password($op = null, $value = null) 密码
 * @method static Entity email($op = null, $value = null) 邮箱
 * @method static Entity phone($op = null, $value = null) 联系方式
 * @method static Entity qq($op = null, $value = null) QQ号码
 * @method static Entity loginIp($op = null, $value = null) 本次登录IP
 * @method static Entity loginTime($op = null, $value = null) 本次登录时间
 * @method static Entity lastIp($op = null, $value = null) 最后一次登录IP地址
 * @method static Entity lastTime($op = null, $value = null) 最后一次登录时间
 * @method static Entity createTime($op = null, $value = null) 创建时间
 * @method static Entity updateTime($op = null, $value = null) 更新时间
 * @method static Entity groupId($op = null, $value = null) 用户组权限ID
 * @method static Entity sectionId($op = null, $value = null) 所属部门ID
 * @method static Entity checked($op = null, $value = null) 是否审核
 * @method static Entity loginTotal($op = null, $value = null) 登录次数
 * @method static Entity isSystem($op = null, $value = null) 是否系统管理员
 * @method static Entity token($op = null, $value = null) 密钥
 */
class AdminUserField extends Field
{
    /**
     * ID
     * @var int
     */
    public $id;
    
    /**
     * 帐号
     * @var string
     */
    public $username;
    
    /**
     * 密码
     * @var string
     */
    public $password;
    
    /**
     * 邮箱
     * @var string
     */
    public $email;
    
    /**
     * 联系方式
     * @var string
     */
    public $phone;
    
    /**
     * QQ号码
     * @var string
     */
    public $qq;
    
    /**
     * 本次登录IP
     * @var string
     */
    public $loginIp;
    
    /**
     * 本次登录时间
     * @var int
     */
    public $loginTime;
    
    /**
     * 最后一次登录IP地址
     * @var string
     */
    public $lastIp;
    
    /**
     * 最后一次登录时间
     * @var int
     */
    public $lastTime;
    
    /**
     * 创建时间
     * @var int
     */
    public $createTime;
    
    /**
     * 更新时间
     * @var int
     */
    public $updateTime;
    
    /**
     * 用户组权限ID
     * @var int
     */
    public $groupId;
    
    /**
     * 所属部门ID
     * @var int
     */
    public $sectionId;
    
    /**
     * 是否审核
     * @var int
     */
    public $checked;
    
    /**
     * 登录次数
     * @var int
     */
    public $loginTotal;
    
    /**
     * 是否系统管理员
     * @var int
     */
    public $isSystem;
    
    /**
     * 密钥
     * @var string
     */
    public $token;
    
    
    /**
     * 设置ID
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
     * 设置帐号
     * @param string $username
     * @return $this
     * @throws Exception
     */
    public function setUsername($username)
    {
        $this->username = trim($username);
        if (!$this->username) {
            throw new VerifyException('请输入用户名', 'username');
        }
        
        // 查重
        try {
            try {
                $model = AdminUser::init();
                if ($this->id > 0) {
                    $model->where('id', '<>', $this->id);
                }
                $model->getInfoByUsername($username);
                throw new Exception();
            } catch (SQLException $e) {
            }
        } catch (Exception $e) {
            throw new VerifyException('用户已存在', 'username');
        }
        
        
        return $this;
    }
    
    
    /**
     * 设置密码
     * @param string $password
     * @param string $confirmPassword
     * @return $this
     * @throws VerifyException
     */
    public function setPassword($password, $confirmPassword)
    {
        $this->password = AdminUser::checkPassword($password, $confirmPassword);
        
        return $this;
    }
    
    
    /**
     * 设置邮箱
     * @param string $email
     * @return $this
     * @throws VerifyException
     */
    public function setEmail($email)
    {
        $this->email = trim($email);
        if ($this->email) {
            $model = AdminUser::init();
            if (!Regex::email($this->email)) {
                throw new VerifyException('请输入有效的邮箱地址', 'email');
            }
            
            // 查重
            try {
                try {
                    if ($this->id > 0) {
                        $model->where('id', '<>', $this->id);
                    }
                    $model->getInfoByEmail($email);
                    throw new Exception();
                } catch (SQLException $e) {
                }
            } catch (Exception $e) {
                throw new VerifyException('该邮箱已存在', 'email');
            }
        }
        
        return $this;
    }
    
    
    /**
     * 设置联系方式
     * @param string $phone
     * @return $this
     * @throws VerifyException
     */
    public function setPhone($phone)
    {
        $this->phone = trim($phone);
        if ($this->phone) {
            $model = AdminUser::init();
            if (!Regex::phone($phone)) {
                throw new VerifyException('请输入有效的手机号', 'phone');
            }
            
            // 查重
            try {
                try {
                    if ($this->id > 0) {
                        $model->where('id', '<>', $this->id);
                    }
                    $model->getInfoByPhone($phone);
                    throw new Exception();
                } catch (SQLException $e) {
                }
            } catch (Exception $e) {
                throw new VerifyException('该手机号已存在', 'phone');
            }
        }
        
        return $this;
    }
    
    
    /**
     * 设置QQ号码
     * @param string $qq
     * @return $this
     */
    public function setQq($qq)
    {
        $this->qq = trim($qq);
        
        return $this;
    }
    
    
    /**
     * 设置是否系统管理员
     * @param int $isSystem
     * @return $this
     */
    public function setIsSystem($isSystem)
    {
        $this->isSystem = Transform::dataToBool($isSystem);
        
        return $this;
    }
    
    
    /**
     * 设为是否审核
     * @param int $checked
     * @return $this
     */
    public function setChecked($checked)
    {
        $this->checked = Transform::dataToBool($checked);
        
        return $this;
    }
    
    
    /**
     * 设置用户组
     * @param int $groupId
     * @return $this
     * @throws VerifyException
     */
    public function setGroupId($groupId)
    {
        $this->groupId = floatval($groupId);
        if ($this->groupId < 1) {
            throw new VerifyException('请选择所属用户组', 'group_id');
        }
        
        return $this;
    }
    
    
    /**
     * 设置部门ID
     * @param int $sectionId
     * @return $this
     * @throws VerifyException
     */
    public function setSectionId($sectionId)
    {
        $this->sectionId = floatval($sectionId);
        if ($this->sectionId < 1) {
            throw new VerifyException('请选择所属部门', 'section');
        }
        
        return $this;
    }
}