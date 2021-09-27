<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\user;

use BusyPHP\exception\VerifyException;
use BusyPHP\helper\util\Filter;
use BusyPHP\helper\util\Regex;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use BusyPHP\helper\util\Transform;

/**
 * 管理员表模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午2:47 AdminUserField.php $
 * @method static Entity id($op = null, $value = null) ID
 * @method static Entity username($op = null, $value = null) 帐号
 * @method static Entity password($op = null, $value = null) 密码
 * @method static Entity email($op = null, $value = null) 邮箱
 * @method static Entity phone($op = null, $value = null) 联系方式
 * @method static Entity qq($op = null, $value = null) QQ号码
 * @method static Entity groupIds($op = null, $value = null) 权限组ID集合，英文逗号分割，左右要有逗号
 * @method static Entity lastIp($op = null, $value = null) 最后一次登录IP地址
 * @method static Entity lastTime($op = null, $value = null) 最后一次登录时间
 * @method static Entity loginIp($op = null, $value = null) 本次登录IP
 * @method static Entity loginTime($op = null, $value = null) 本次登录时间
 * @method static Entity loginTotal($op = null, $value = null) 登录次数
 * @method static Entity createTime($op = null, $value = null) 创建时间
 * @method static Entity updateTime($op = null, $value = null) 更新时间
 * @method static Entity checked($op = null, $value = null) 是否审核
 * @method static Entity system($op = null, $value = null) 是否系统管理员
 * @method static Entity token($op = null, $value = null) 密钥
 * @method static Entity errorTotal($op = null, $value = null) 密码错误次数统计
 * @method static Entity errorTime($op = null, $value = null) 密码错误开始时间
 * @method static Entity errorRelease($op = null, $value = null) 密码错误锁定释放时间
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
     * 权限组ID集合，英文逗号分割，左右要有逗号
     * @var string|array
     */
    public $groupIds;
    
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
     * 登录次数
     * @var int
     */
    public $loginTotal;
    
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
     * 是否审核
     * @var int
     */
    public $checked;
    
    /**
     * 是否系统管理员
     * @var int
     */
    public $system;
    
    /**
     * 密钥
     * @var string
     */
    public $token;
    
    /**
     * 密码错误次数统计
     * @var int
     */
    public $errorTotal;
    
    /**
     * 密码错误开始时间
     * @var int
     */
    public $errorTime;
    
    /**
     * 密码错误锁定释放时间
     * @var int
     */
    public $errorRelease;
    
    
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
     * @throws VerifyException
     */
    public function setUsername($username)
    {
        $this->username = trim($username);
        if (!$this->username) {
            throw new VerifyException('请输入用户名', 'username');
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
            if (!Regex::email($this->email)) {
                throw new VerifyException('请输入有效的邮箱地址', 'email');
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
            if (!Regex::phone($phone)) {
                throw new VerifyException('请输入有效的手机号', 'phone');
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
     * 设置权限ID集合
     * @param array $groupIds
     * @throws VerifyException
     */
    public function setGroupIds(array $groupIds) : void
    {
        $groupIds = array_map('intval', $groupIds);
        $groupIds = Filter::trimArray($groupIds);
        if (!$groupIds) {
            throw new VerifyException('请选择权限', 'group_ids');
        }
        
        $this->groupIds = ',' . implode(',', $groupIds) . ',';
    }
}