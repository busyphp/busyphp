<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\user;

use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\group\AdminGroupField;
use BusyPHP\helper\TransHelper;
use BusyPHP\interfaces\FieldGetModelDataInterface;
use BusyPHP\interfaces\ModelValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\annotation\field\Filter;
use BusyPHP\model\annotation\field\Ignore;
use BusyPHP\model\annotation\field\Json;
use BusyPHP\model\annotation\field\Separate;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\annotation\field\Validator;
use BusyPHP\model\annotation\field\ValueBindField;
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
 * @method static Entity groupList($op = null, $value = null) 权限组数据
 * @method static Entity groupRulePaths($op = null, $value = null) 权限规则路径集合
 * @method static Entity groupRuleIds($op = null, $value = null) 权限规则ID集合
 * @method static Entity groupNames($op = null, $value = null) 权限名称集合
 * @method static Entity groupHasSystem($op = null, $value = null) 权限中是否包涵超级权限
 * @method static Entity formatCreateTime($op = null, $value = null) 格式化的创建时间
 * @method static Entity formatUpdateTime($op = null, $value = null) 格式化的更新时间
 * @method static Entity formatLastTime($op = null, $value = null) 格式化的上次登录时间
 * @method static Entity formatLoginTime($op = null, $value = null) 格式化的本次登录时间
 * @method static Entity isTempLock($op = null, $value = null) 是否临时锁定
 * @method static Entity formatErrorReleaseTime($op = null, $value = null) 格式化的锁定释放时间
 * @method static Entity defaultGroup($op = null, $value = null) 默认角色组信息
 * @method static Entity defaultMenu($op = null, $value = null) 默认菜单
 * @method static Entity skin($op = null, $value = null) 皮肤
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
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
class AdminUserField extends Field implements ModelValidateInterface, FieldGetModelDataInterface
{
    /**
     * ID
     * @var int
     */
    #[Validator(name: Validator::REQUIRE)]
    #[Validator(name: Validator::IS_NUMBER)]
    #[Validator(name: Validator::GT, rule: 0)]
    public $id;
    
    /**
     * 用户名
     * @var string
     */
    #[Validator(name: Validator::REQUIRE, msg: '请输入:attribute')]
    #[Validator(name: Validator::MIN, rule: 2, msg: ':attribute不能少于:rule个字符')]
    #[Validator(name: Validator::MAX, rule: 20, msg: ':attribute不能超过:rule个字符')]
    public $username;
    
    /**
     * 登录密码
     * @var string
     */
    #[Validator(name: Validator::REQUIRE, msg: '请输入:attribute')]
    #[Validator(name: Validator::MIN, rule: 6)]
    #[Validator(name: Validator::MAX, rule: 20)]
    public $password;
    
    /**
     * 确认密码
     * @var string
     */
    #[Validator(name: Validator::REQUIRE, msg: '输入:attribute以确认')]
    #[Validator(name: Validator::CONFIRM, rule: 'password', msg: ':attribute和登录密码不一致')]
    private $confirmPassword;
    
    /**
     * 邮箱
     * @var string
     */
    #[Validator(name: Validator::IS_EMAIL, msg: '请输入有效的:attribute')]
    #[Validator(name: Validator::UNIQUE, rule: AdminUser::class)]
    public $email;
    
    /**
     * 手机号
     * @var string
     */
    public $phone;
    
    /**
     * QQ号码
     * @var string
     */
    #[Validator(name: Validator::IS_NUMBER, msg: '请输入有效的:attribute')]
    #[Validator(name: Validator::MIN, rule: 5, msg: ':attribute至少需要:rule个数字')]
    #[Validator(name: Validator::MAX, rule: 13, msg: ':attribute最多允许:rule个数字')]
    public $qq;
    
    /**
     * 角色组
     * @var array
     */
    #[Separate(separator: ',', full: true)]
    #[Validator(name: Validator::REQUIRE, msg: '请选择:attribute')]
    #[Validator(name: Validator::IS_ARRAY)]
    #[Validator(name: Validator::MIN, rule: 1, msg: '请至少选择:rule个:attribute')]
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
     */
    #[Json]
    public $theme;
    
    /**
     * 权限组数据，以权限ID为下标
     * @var AdminGroupField[]
     */
    #[Ignore]
    public $groupList = [];
    
    /**
     * 权限规则路径集合，未去重复
     * @var string[]
     */
    #[Ignore]
    public $groupRulePaths = [];
    
    /**
     * 权限规则Id集合，未去重复
     * @var int[]
     */
    #[Ignore]
    public $groupRuleIds = [];
    
    /**
     * 权限名称集合
     * @var string[]
     */
    #[Ignore]
    public $groupNames = [];
    
    /**
     * 权限是否包涵超级权限
     * @var bool
     */
    #[Ignore]
    public $groupHasSystem;
    
    /**
     * 格式化的创建时间
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'createTime'])]
    #[Filter([TransHelper::class, 'date'])]
    public $formatCreateTime;
    
    /**
     * 格式化的更新时间
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'updateTime'])]
    #[Filter([TransHelper::class, 'date'])]
    public $formatUpdateTime;
    
    /**
     * 格式化的上次登录时间
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'lastTime'])]
    #[Filter([TransHelper::class, 'date'])]
    public $formatLastTime;
    
    /**
     * 格式化的本次登录时间
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'loginTime'])]
    #[Filter([TransHelper::class, 'date'])]
    public $formatLoginTime;
    
    /**
     * 是否已经临时锁定
     * @var bool
     */
    #[Ignore]
    public $isTempLock;
    
    /**
     * 格式化的锁定释放时间
     * @var string
     */
    #[Ignore]
    public $formatErrorRelease;
    
    /**
     * 默认角色组信息
     * @var AdminGroupField
     */
    #[Ignore]
    public $defaultGroup;
    
    /**
     * 默认菜单
     * @var string
     */
    #[Ignore]
    public $defaultMenu;
    
    /**
     * 皮肤
     * @var string
     */
    #[Ignore]
    public $skin;
    
    
    protected function onParseAfter()
    {
        $groupIdMap = AdminGroup::instance()->getIdMap();
        
        $this->skin               = $this->theme['skin'] ?? '';
        $this->isTempLock         = $this->errorRelease > time();
        $this->formatErrorRelease = $this->errorRelease > 0 ? TransHelper::date($this->errorRelease) : '';
        
        $groupIds             = $this->groupIds;
        $this->groupIds       = [];
        $this->groupList      = [];
        $this->groupRuleIds   = [];
        $this->groupRulePaths = [];
        $this->groupNames     = [];
        $this->groupHasSystem = false;
        foreach ($groupIds as $groupId) {
            $groupId = intval($groupId);
            if (!$groupId || !isset($groupIdMap[$groupId])) {
                continue;
            }
            
            $groupInfo = $groupIdMap[$groupId];
            if (!$groupInfo->status) {
                continue;
            }
            
            $this->groupNames[]        = $groupInfo->name;
            $this->groupIds[]          = $groupId;
            $this->groupList[$groupId] = $groupInfo;
            $this->groupRuleIds        = array_merge($this->groupRuleIds, $groupInfo->ruleIds);
            $this->groupRulePaths      = array_merge($this->groupRulePaths, $groupInfo->rulePaths);
            if ($groupInfo->system) {
                $this->groupHasSystem = true;
            }
        }
        
        if (!$this->defaultGroupId || !isset($this->groupList[$this->defaultGroupId])) {
            $this->defaultGroupId = end($this->groupIds);
        }
        
        $this->defaultGroup = $this->groupList[$this->defaultGroupId] ?? null;
        $this->defaultMenu  = $this->defaultGroup->defaultMenu->path ?? '';
    }
    
    
    /**
     * @inheritDoc
     * @param AdminUserField $data
     */
    public function onModelValidate(Model $model, Validate $validate, string $scene, $data = null)
    {
        $validate
            ->rule($this::phone(), ValidateRule::init()->closure(function($value) {
                // 必填验证
                if (AdminUser::class()::isRequirePhone() && !$value) {
                    return '请输入:attribute';
                } elseif (!$value) {
                    return true;
                }
                
                return AdminUser::class()::checkPhone($value);
            }, '请输入有效的:attribute')->unique($model));
        
        $this->setCreateTime(time());
        $this->setUpdateTime(time());
        
        switch ($scene) {
            // 添加场景
            case AdminUser::SCENE_CREATE:
                $validate->append($this::username(), ValidateRule::init()->unique($model));
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
                if ($data->system) {
                    $this->retain($validate, [
                        $this::id(),
                        $this::defaultGroupId(),
                        $this::username(),
                        $this::phone(),
                        $this::email(),
                        $this::qq(),
                        $this->updateTime()
                    ]);
                } else {
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
                }
                
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
    public function onGetModelData(string $field, string $property, array $attrs, $value)
    {
        if ($field == $this::password()) {
            return AdminUser::class()::createPassword($value);
        }
        
        return $value;
    }
}