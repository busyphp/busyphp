<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\user;

use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\group\AdminGroupField;
use BusyPHP\helper\TransHelper;
use BusyPHP\interfaces\FieldGetModelDataInterface;
use BusyPHP\interfaces\ModelValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\annotation\field\AutoTimestamp;
use BusyPHP\model\annotation\field\Column;
use BusyPHP\model\annotation\field\Filter;
use BusyPHP\model\annotation\field\Ignore;
use BusyPHP\model\annotation\field\Json;
use BusyPHP\model\annotation\field\Separate;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\annotation\field\ToArrayHidden;
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
 * @method static Entity phone(mixed $op = null, mixed $condition = null) 手机号
 * @method static Entity tel(mixed $op = null, mixed $condition = null) 电话号
 * @method static Entity qq(mixed $op = null, mixed $condition = null) QQ号码
 * @method static Entity nickname(mixed $op = null, mixed $condition = null) 昵称
 * @method static Entity name(mixed $op = null, mixed $condition = null) 姓名
 * @method static Entity sex(mixed $op = null, mixed $condition = null) 性别
 * @method static Entity birthday(mixed $op = null, mixed $condition = null) 出生日期
 * @method static Entity cardNo(mixed $op = null, mixed $condition = null) 证件号码
 * @method static Entity avatar(mixed $op = null, mixed $condition = null) 头像
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
 * @method static Entity errorTotal(mixed $op = null, mixed $condition = null) 密码错误次数统计
 * @method static Entity errorTime(mixed $op = null, mixed $condition = null) 密码错误开始时间
 * @method static Entity errorRelease(mixed $op = null, mixed $condition = null) 密码错误锁定释放时间
 * @method static Entity theme(mixed $op = null, mixed $condition = null) 主题配置
 * @method static Entity remark(mixed $op = null, mixed $condition = null) 简介
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
 * @method $this setId(mixed $id, bool|ValidateRule[] $validate = false) 设置ID
 * @method $this setUsername(mixed $username, bool|ValidateRule[] $validate = false) 设置帐号
 * @method $this setPassword(mixed $password, bool|ValidateRule[] $validate = false) 设置密码
 * @method $this setConfirmPassword(string $confirmPassword, bool|ValidateRule[] $validate = false) 设置确认密码
 * @method $this setEmail(mixed $email, bool|ValidateRule[] $validate = false) 设置邮箱
 * @method $this setPhone(mixed $phone, bool|ValidateRule[] $validate = false) 设置手机号
 * @method $this setTel(mixed $phone, bool|ValidateRule[] $validate = false) 设置电话号
 * @method $this setQq(mixed $qq, bool|ValidateRule[] $validate = false) 设置QQ号码
 * @method $this setNickname(mixed $name, bool|ValidateRule[] $validate = false) 设置昵称
 * @method $this setName(mixed $name, bool|ValidateRule[] $validate = false) 设置姓名
 * @method $this setSex(mixed $sex, bool|ValidateRule[] $validate = false) 设置性别
 * @method $this setBirthday(mixed $birthday, bool|ValidateRule[] $validate = false) 设置生日
 * @method $this setCardNo(mixed $cardNo, bool|ValidateRule[] $validate = false) 设置身份证号
 * @method $this setAvatar(mixed $avatar, bool|ValidateRule[] $validate = false) 设置头像
 * @method $this setGroupIds(mixed $groupIds, bool|ValidateRule[] $validate = false) 设置权限组ID集合，英文逗号分割，左右要有逗号
 * @method $this setDefaultGroupId(mixed $defaultGroupId, bool|ValidateRule[] $validate = false) 设置默认角色组
 * @method $this setLastIp(mixed $lastIp, bool|ValidateRule[] $validate = false) 设置最后一次登录IP地址
 * @method $this setLastTime(mixed $lastTime, bool|ValidateRule[] $validate = false) 设置最后一次登录时间
 * @method $this setLoginIp(mixed $loginIp, bool|ValidateRule[] $validate = false) 设置本次登录IP
 * @method $this setLoginTime(mixed $loginTime, bool|ValidateRule[] $validate = false) 设置本次登录时间
 * @method $this setLoginTotal(mixed $loginTotal, bool|ValidateRule[] $validate = false) 设置登录次数
 * @method $this setCreateTime(mixed $createTime, bool|ValidateRule[] $validate = false) 设置创建时间
 * @method $this setUpdateTime(mixed $updateTime, bool|ValidateRule[] $validate = false) 设置更新时间
 * @method $this setChecked(mixed $checked, bool|ValidateRule[] $validate = false) 设置是否审核
 * @method $this setSystem(mixed $system, bool|ValidateRule[] $validate = false) 设置是否系统管理员
 * @method $this setErrorTotal(mixed $errorTotal, bool|ValidateRule[] $validate = false) 设置密码错误次数统计
 * @method $this setErrorTime(mixed $errorTime, bool|ValidateRule[] $validate = false) 设置密码错误开始时间
 * @method $this setErrorRelease(mixed $errorRelease, bool|ValidateRule[] $validate = false) 设置密码错误锁定释放时间
 * @method $this setTheme(mixed $theme, bool|ValidateRule[] $validate = false) 设置主题配置
 * @method $this setRemark(mixed $theme, bool|ValidateRule[] $validate = false) 设置简介
 */
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE), AutoTimestamp(type: AutoTimestamp::TYPE_INT)]
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
     * 登录账号
     * @var string
     */
    #[Validator(name: Validator::REQUIRE, msg: '请输入:attribute')]
    #[Validator(name: Validator::MIN, rule: 2, msg: ':attribute不能少于:rule个字符')]
    #[Validator(name: Validator::MAX, rule: 20, msg: ':attribute不能超过:rule个字符')]
    #[Validator(name: Validator::UNIQUE, rule: AdminUser::class, msg: '该:attribute已被他人使用，请换一个再试')]
    #[Filter(filter: 'trim')]
    public $username;
    
    /**
     * 登录密码
     * @var string
     */
    #[Validator(name: Validator::REQUIRE, msg: '请输入:attribute')]
    #[Validator(name: Validator::MIN, rule: 6)]
    #[Validator(name: Validator::MAX, rule: 20)]
    #[Filter(filter: 'trim')]
    #[ToArrayHidden]
    public $password;
    
    /**
     * 确认密码
     * @var string
     */
    #[Validator(name: Validator::REQUIRE, msg: '输入:attribute以确认')]
    #[Validator(name: Validator::CONFIRM, rule: 'password', msg: ':attribute和登录密码不一致')]
    #[Ignore]
    #[Filter(filter: 'trim')]
    #[ToArrayHidden]
    protected $confirmPassword;
    
    /**
     * 手机号
     * @var string
     */
    #[Filter(filter: 'trim')]
    #[Validator(name: Validator::UNIQUE, rule: AdminUser::class, msg: '该:attribute已被他人使用，请换一个再试')]
    public $phone;
    
    /**
     * 邮箱
     * @var string
     */
    #[Validator(name: Validator::IS_EMAIL, msg: '请输入有效的:attribute')]
    #[Validator(name: Validator::UNIQUE, rule: AdminUser::class, msg: '该:attribute已被他人使用，请换一个再试')]
    #[Filter(filter: 'trim')]
    public $email;
    
    /**
     * 电话号
     * @var string
     */
    #[Filter(filter: 'trim')]
    public $tel;
    
    /**
     * QQ号码
     * @var string
     */
    #[Validator(name: Validator::IS_NUMBER, msg: '请输入有效的:attribute')]
    #[Validator(name: Validator::MIN, rule: 5, msg: ':attribute至少需要:rule个数字')]
    #[Validator(name: Validator::MAX, rule: 13, msg: ':attribute最多允许:rule个数字')]
    #[Filter(filter: 'trim')]
    public $qq;
    
    /**
     * 昵称
     * @var string
     */
    #[Filter(filter: 'trim')]
    public $nickname;
    
    /**
     * 姓名
     * @var string
     */
    #[Filter(filter: 'trim')]
    public $name;
    
    /**
     * 性别
     * @var int
     */
    public $sex;
    
    /**
     * 出生日期
     * @var string
     */
    #[Validator(name: Validator::DATE_FORMAT, rule: 'Y-m-d')]
    #[Filter(filter: 'trim')]
    public $birthday;
    
    /**
     * 身份证号
     * @var string
     */
    #[Filter(filter: 'trim')]
    public $cardNo;
    
    /**
     * 头像
     * @var string
     */
    #[Filter(filter: 'trim')]
    public $avatar;
    
    /**
     * 角色组
     * @var array
     */
    #[Separate(separator: ',', full: true)]
    #[Validator(name: Validator::IS_ARRAY)]
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
    #[Filter(filter: 'trim')]
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
    #[Filter(filter: 'trim')]
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
    #[Column(feature: Column::FEATURE_CREATE_TIME)]
    public $createTime;
    
    /**
     * 更新时间
     * @var int
     */
    #[Column(feature: Column::FEATURE_UPDATE_TIME)]
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
     * 简介
     * @var string
     */
    #[Filter(filter: 'trim')]
    public $remark;
    
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
    
    /**
     * 性别名称
     * @var string
     */
    #[Ignore]
    #[ValueBindField(field: [self::class, 'sex'])]
    #[Filter(filter: [AdminUser::class, 'getSexMap'])]
    public $sexName;
    
    
    protected function onParseAfter()
    {
        $groupIdMap = AdminGroup::instance()->getIdMap();
        
        $this->theme              = $this->theme ?: [];
        $this->skin               = $this->theme['skin'] ?? '';
        $this->isTempLock         = $this->errorRelease > time();
        $this->formatErrorRelease = $this->errorRelease > 0 ? TransHelper::date($this->errorRelease) : '';
        $this->birthday           = $this->birthday === '0000-00-00' ? '' : $this->birthday;
        
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
     * 添加场景保留字段
     * @param AdminUser $model 用户模型
     * @return array
     */
    protected function createRetain(AdminUser $model) : array
    {
        return [
            $this::avatar(),
            $this::checked(),
            $this::username(),
            $this::nickname(),
            $this::password(),
            $this::confirmPassword(),
            $this::phone(),
            $this::email(),
            $this::groupIds(),
            $this::defaultGroupId(),
            $this::name(),
            $this::cardNo(),
            $this::sex(),
            $this::birthday(),
            $this::tel(),
            $this::qq(),
            $this::remark()
        ];
    }
    
    
    /**
     * 修改场景保留字段
     * @param AdminUser      $model 用户模型
     * @param AdminUserField $data 更新前用户数据
     * @param bool           $system 是否系统用户
     * @return array
     */
    protected function updateRetain(AdminUser $model, AdminUserField $data, bool $system) : array
    {
        if ($system) {
            return [
                $this::id(),
                $this::avatar(),
                $this::username(),
                $this::nickname(),
                $this::phone(),
                $this::email(),
                $this::name(),
                $this::cardNo(),
                $this::sex(),
                $this::birthday(),
                $this::tel(),
                $this::qq(),
                $this::remark(),
            ];
        } else {
            return [
                $this::id(),
                $this::avatar(),
                $this::checked(),
                $this::username(),
                $this::nickname(),
                $this::phone(),
                $this::email(),
                $this::groupIds(),
                $this::defaultGroupId(),
                $this::name(),
                $this::cardNo(),
                $this::sex(),
                $this::birthday(),
                $this::tel(),
                $this::qq(),
                $this::remark(),
            ];
        }
    }
    
    
    /**
     * 修改个人资料保留字段
     * @param AdminUser      $model 用户模型
     * @param AdminUserField $data 更新前的用户数据
     * @return array
     */
    protected function updateProfileRetain(AdminUser $model, AdminUserField $data) : array
    {
        return [
            $this::id(),
            $this::avatar(),
            $this::nickname(),
            $this::phone(),
            $this::email(),
            $this::name(),
            $this::cardNo(),
            $this::sex(),
            $this::birthday(),
            $this::tel(),
            $this::qq(),
            $this::remark(),
        ];
    }
    
    
    /**
     * 通用保留字段
     * @param AdminUser           $model 用户模型
     * @param string              $scene 场景值
     * @param AdminUserField|null $data 更新前的用户数据
     * @return array
     */
    protected function commonRetain(AdminUser $model, string $scene, ?AdminUserField $data) : array
    {
        return [];
    }
    
    
    /**
     * @inheritDoc
     * @param AdminUser      $model
     * @param AdminUserField $data
     */
    public function onModelValidate(Model $model, Validate $validate, string $scene, $data = null)
    {
        // 头像必填
        if ($model->getValidateConfig('avatar')) {
            $validate->append($this::avatar(), ValidateRule::init()->isRequire(msg: '请上传:attribute'));
        }
        
        // 昵称必填
        if ($model->getValidateConfig('nickname')) {
            $validate->append($this::nickname(), ValidateRule::init()->isRequire());
        }
        
        // 手机号必填
        if ($model->getValidateConfig('phone.required')) {
            $validate->append($this::phone(), ValidateRule::init()->isRequire());
        }
        
        // 手机号规则
        $validate->append($this::phone(), ValidateRule::init()->closure(function($value) use ($model) {
            if ($value === '') {
                return true;
            }
            
            return $model->checkPhone($value);
        }, '请输入有效的:attribute'));
        
        // 邮箱必填
        if ($model->getValidateConfig('email')) {
            $validate->append($this::email(), ValidateRule::init()->isRequire());
        }
        
        // 姓名必填
        if ($model->getValidateConfig('name')) {
            $validate->append($this::name(), ValidateRule::init()->isRequire());
        }
        
        // 身份证号必填
        if ($model->getValidateConfig('card_no.required')) {
            $validate->append($this::cardNo(), ValidateRule::init()->isRequire());
        }
        
        // 身份证号规则
        if ($model->getValidateConfig('card_no.identity', false)) {
            $validate->append($this::cardNo(), ValidateRule::init()->isIdCard(msg: ':attribute无效'));
        }
        
        // 身份证号去重
        if ($model->getValidateConfig('card_no.unique')) {
            $validate->append($this::cardNo(), ValidateRule::init()->unique(
                rule: [
                    AdminUser::class,
                    $this::cardNo()->field()
                ],
                msg : '该身份证号码已被他人使用'
            ));
        }
        
        // 性别必填
        if ($model->getValidateConfig('sex')) {
            $validate->append($this::sex(), ValidateRule::init()->gt(0, '请选择:attribute'));
        }
        
        // 性别范围
        $validate->append($this::sex(), ValidateRule::init()->in(
            rule: array_keys($model::getSexMap()),
            msg : '请选择有效的:attribute'
        ));
        
        // 生日必填
        if ($model->getValidateConfig('birthday')) {
            $validate->append($this::birthday(), ValidateRule::init()->isRequire());
        }
        
        // 电话必填
        if ($model->getValidateConfig('tel.required')) {
            $validate->append($this::tel(), ValidateRule::init()->isRequire());
        }
        
        // 电话规则
        if ($telRegex = $model->getValidateConfig('tel.regex')) {
            $validate->append($this::tel(), ValidateRule::init()->regex($telRegex, ':attribute无效'));
        }
        
        $commonRetain = $this->commonRetain($model, $scene, $data);
        switch ($scene) {
            // 添加
            case $model::SCENE_CREATE:
                $this->retain($validate, $this->createRetain($model), $commonRetain);
                
                return true;
            
            // 修改
            case $model::SCENE_UPDATE:
                if (!$this->groupIds) {
                    $this->groupIds = [];
                }
                
                $this->retain($validate, $this->updateRetain($model, $data, $data->system), $commonRetain);
                
                return true;
            
            // 修改个人资料
            case $model::SCENE_PROFILE:
                $this->retain($validate, $this->updateProfileRetain($model, $data), $commonRetain);
                
                return true;
            
            // 修改密码
            case $model::SCENE_PASSWORD:
                $validate->title($this::password(), '新密码');
                $this->retain($validate, [
                    $this::id(),
                    $this::password(),
                    $this::confirmPassword(),
                ]);
                
                return true;
        }
        
        return false;
    }
    
    
    /**
     * @inheritDoc
     */
    public function onGetModelData(string $field, string $property, mixed $value) : mixed
    {
        if ($field == $this::password()) {
            return AdminUser::class()::createPassword($value);
        }
        
        return $value;
    }
}