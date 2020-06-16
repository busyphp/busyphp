<?php

namespace BusyPHP\app\admin\model\admin\user;

use BusyPHP\exception\SQLException;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\util\Regex;
use BusyPHP\model\Field;
use BusyPHP\helper\util\Transform;
use think\Exception;

/**
 * 管理员表字段
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2018 busy^life <busy.life@qq.com>
 * @version $Id: 2018-01-17 上午11:54 AdminUserField.php busy^life $
 */
class AdminUserField extends Field
{
    /** @var int ID */
    public $id = null;
    
    /** @var string 帐号 */
    public $username = null;
    
    /** @var string 密码 */
    public $password = null;
    
    /** @var string 邮箱 */
    public $email = null;
    
    /** @var string 联系方式 */
    public $phone = null;
    
    /** @var string QQ号码 */
    public $qq = null;
    
    /** @var string 本次登录IP */
    public $loginIp = null;
    
    /** @var int 本次登录时间 */
    public $loginTime = null;
    
    /** @var string 最后一次登录IP地址 */
    public $lastIp = null;
    
    /** @var int 最后一次登录时间 */
    public $lastTime = null;
    
    /** @var int 创建时间 */
    public $createTime = null;
    
    /** @var int 更新时间 */
    public $updateTime = null;
    
    /** @var int 用户组权限ID */
    public $groupId = null;
    
    /** @var int 所属部门ID */
    public $sectionId = null;
    
    /** @var int 是否审核 */
    public $checked = null;
    
    /** @var int 登录次数 */
    public $loginTotal = null;
    
    /** @var int 是否系统管理员 */
    public $isSystem = null;
    
    /** @var string 密钥 */
    public $token = null;
    
    
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