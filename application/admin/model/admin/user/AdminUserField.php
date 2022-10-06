<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\user;

use BusyPHP\interfaces\FieldObtainDataInterface;
use BusyPHP\interfaces\ModelSceneValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use think\Validate;
use think\validate\ValidateRule;

/**
 * 管理员表模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午2:47 AdminUserField.php $
 * @method static Entity id(mixed $op = null, mixed $condition = null) ID
 * @method static Entity username(mixed $op = null, mixed $condition = null) 帐号
 * @method static Entity password(mixed $op = null, mixed $condition = null) 密码
 * @method static Entity confirmPassword() 密码
 * @method static Entity email(mixed $op = null, mixed $condition = null) 邮箱
 * @method static Entity phone(mixed $op = null, mixed $condition = null) 联系方式
 * @method static Entity qq(mixed $op = null, mixed $condition = null) QQ号码
 * @method static Entity groupIds(mixed $op = null, mixed $condition = null) 权限组ID集合，英文逗号分割，左右要有逗号
 * @method static Entity defaultGroupId(mixed $op = null, mixed $condition = null) 默认角色组
 * @method static Entity lastIp(mixed $op = null, mixed $condition = null) 最后一次登录IP地址
 * @method static Entity lastTime(mixed $op = null, mixed $condition = null) 最后一次登录时间
 * @method static Entity loginIp(mixed $op = null, mixed $condition = null) 本次登录IP
 * @method static Entity loginTime(mixed $op = null, mixed $condition = null) 本次登录时间
 * @method static Entity loginTotal(mixed $op = null, mixed $condition = null) 登录次数
 * @method static Entity createTime(mixed $op = null, mixed $condition = null) 创建时间
 * @method static Entity updateTime(mixed $op = null, mixed $condition = null) 更新时间
 * @method static Entity checked(mixed $op = null, mixed $condition = null) 是否审核
 * @method static Entity system(mixed $op = null, mixed $condition = null) 是否系统管理员
 * @method static Entity token(mixed $op = null, mixed $condition = null) 密钥
 * @method static Entity errorTotal(mixed $op = null, mixed $condition = null) 密码错误次数统计
 * @method static Entity errorTime(mixed $op = null, mixed $condition = null) 密码错误开始时间
 * @method static Entity errorRelease(mixed $op = null, mixed $condition = null) 密码错误锁定释放时间
 * @method static Entity theme(mixed $op = null, mixed $condition = null) 主题配置
 * @method $this setId(mixed $id) 设置ID
 * @method $this setUsername(mixed $username) 设置帐号
 * @method $this setPassword(mixed $password) 设置密码
 * @method $this setConfirmPassword(string $confirmPassword) 设置确认密码
 * @method $this setEmail(mixed $email) 设置邮箱
 * @method $this setPhone(mixed $phone) 设置联系方式
 * @method $this setQq(mixed $qq) 设置QQ号码
 * @method $this setGroupIds(mixed $groupIds) 设置权限组ID集合，英文逗号分割，左右要有逗号
 * @method $this setDefaultGroupId(mixed $defaultGroupId) 设置默认角色组
 * @method $this setLastIp(mixed $lastIp) 设置最后一次登录IP地址
 * @method $this setLastTime(mixed $lastTime) 设置最后一次登录时间
 * @method $this setLoginIp(mixed $loginIp) 设置本次登录IP
 * @method $this setLoginTime(mixed $loginTime) 设置本次登录时间
 * @method $this setLoginTotal(mixed $loginTotal) 设置登录次数
 * @method $this setCreateTime(mixed $createTime) 设置创建时间
 * @method $this setUpdateTime(mixed $updateTime) 设置更新时间
 * @method $this setChecked(mixed $checked) 设置是否审核
 * @method $this setSystem(mixed $system) 设置是否系统管理员
 * @method $this setToken(mixed $token) 设置密钥
 * @method $this setErrorTotal(mixed $errorTotal) 设置密码错误次数统计
 * @method $this setErrorTime(mixed $errorTime) 设置密码错误开始时间
 * @method $this setErrorRelease(mixed $errorRelease) 设置密码错误锁定释放时间
 * @method $this setTheme(mixed $theme) 设置主题配置
 */
class AdminUserField extends Field implements ModelSceneValidateInterface, FieldObtainDataInterface
{
    /**
     * ID
     * @var int
     * @busy-validate require
     * @busy-validate number
     * @busy-validate gt:0
     */
    public $id;
    
    /**
     * 用户名
     * @var string
     * @busy-validate require#请输入:attribute
     * @busy-validate min:2#:attribute不能少于:rule个字符
     * @busy-validate max:20#:attribute不能超过:rule个字符
     */
    public $username;
    
    /**
     * 登录密码
     * @var string
     * @busy-validate require#请输入:attribute
     * @busy-validate min:6
     * @busy-validate max:20
     */
    public $password;
    
    /**
     * 确认密码
     * @var string
     * @busy-validate require#输入:attribute以确认
     * @busy-validate confirm:password#:attribute和登录密码不一致
     * @busy-ignore true
     */
    private $confirmPassword;
    
    /**
     * 邮箱
     * @var string
     * @busy-validate email#请输入有效的:attribute
     */
    public $email;
    
    /**
     * 手机号
     * @var string
     */
    public $phone;
    
    /**
     * QQ号码
     * @var string
     * @busy-validate number#请输入有效的:attribute
     * @busy-validate min:5#:attribute至少需要:rule个数字
     * @busy-validate max:13#:attribute最多允许:rule个数字
     */
    public $qq;
    
    /**
     * 角色组
     * @var array
     * @busy-validate require#请选择:attribute
     * @busy-validate array
     * @busy-validate min:1#请至少选择:rule个:attribute
     * @busy-array "," true
     */
    public $groupIds;
    
    /**
     * 默认角色组
     * @var int
     */
    public $defaultGroupId;
    
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
     * @var bool
     */
    public $checked;
    
    /**
     * 是否系统管理员
     * @var bool
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
     * 主题配置
     * @var array
     * @busy-array json
     */
    public $theme;
    
    
    /**
     * @inheritDoc
     */
    public function onModelSceneValidate(Model $model, Validate $validate, string $name, $data = null)
    {
        $validate
            ->rule($this::phone(), ValidateRule::closure(function($value) {
                // 必填验证
                if (AdminUser::getDefine('require_phone', false) && !$value) {
                    return '请输入:attribute';
                } elseif (!$value) {
                    return true;
                }
                
                return AdminUser::getClass()::checkPhone($value);
            }, '请输入有效的:attribute')->unique($model))
            ->append($this::email(), ValidateRule::unique($model));
        
        $this->setCreateTime(time());
        $this->setUpdateTime(time());
        
        switch ($name) {
            // 添加场景
            case AdminUser::SCENE_CREATE:
                $validate->append($this::username(), ValidateRule::unique($model));
                $this->retain($validate, [
                    $this::groupIds(),
                    $this::defaultGroupId(),
                    $this::username(),
                    $this::password(),
                    $this::confirmPassword(),
                    $this::phone(),
                    $this::email(),
                    $this::qq(),
                    $this::checked(),
                    $this::createTime(),
                    $this::updateTime()
                ]);
                
                return true;
            
            // 修改
            case AdminUser::SCENE_UPDATE:
                $this->retain($validate, [
                    $this::id(),
                    $this::groupIds(),
                    $this::defaultGroupId(),
                    $this::username(),
                    $this::phone(),
                    $this::email(),
                    $this::qq(),
                    $this::checked(),
                    $this->updateTime()
                ]);
                
                return true;
            
            // 修改个人资料
            case AdminUser::SCENE_PROFILE:
                $this->retain($validate, [
                    $this::id(),
                    $this::phone(),
                    $this::email(),
                    $this::qq(),
                    $this->updateTime()
                ]);
                
                return true;
            
            // 修改密码
            case AdminUser::SCENE_PASSWORD:
                $validate->title($this::password(), '新密码');
                $this->retain($validate, [
                    $this::id(),
                    $this::password(),
                    $this::confirmPassword(),
                    $this->updateTime()
                ]);
                
                return true;
        }
        
        return false;
    }
    
    
    /**
     * @inheritDoc
     */
    public function onObtainData(string $field, string $property, array $attrs, $value)
    {
        if ($field == $this::password()) {
            return AdminUser::getClass()::createPassword($value);
        }
        
        return $value;
    }
}