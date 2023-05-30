<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\user;

use BusyPHP\app\admin\model\system\token\SystemToken;
use BusyPHP\app\admin\model\system\token\SystemTokenField;
use BusyPHP\app\admin\setting\AdminSetting;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\ClassHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use Closure;
use LogicException;
use RuntimeException;
use think\Container;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\facade\Config;
use think\facade\Request;
use think\facade\Validate;
use think\validate\ValidateRule;
use Throwable;

/**
 * 管理员模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/30 下午5:57 下午 AdminUser.php $
 * @method AdminUserField getInfo(int $id, string $notFoundMessage = null)
 * @method AdminUserField|null findInfo(int $id = null)
 * @method AdminUserField[] selectList()
 * @method AdminUserField[] indexList(string|Entity $key = '')
 * @method AdminUserField[] indexListIn(array $range, string|Entity $key = '', string|Entity $field = '')
 * @method AdminUserField getInfoByUsername(string $username, string $notFoundMessage = null)
 * @method AdminUserField getInfoByEmail(string $email, string $notFoundMessage = null)
 * @method AdminUserField getInfoByPhone(string $phone, string $notFoundMessage = null)
 * @method AdminUserField|null findInfoByUsername(string $username)
 * @method AdminUserField|null findInfoByEmail(string $email)
 * @method AdminUserField|null findInfoByPhone(string $phone)
 */
class AdminUser extends Model implements ContainerInterface
{
    // +----------------------------------------------------
    // + 操作场景
    // +----------------------------------------------------
    /** @var string 操作场景-更新密码 */
    const SCENE_PASSWORD = 'password';
    
    /** @var string 操作场景-更新状态 */
    const SCENE_CHECKED = 'checked';
    
    /** @var string 操作场景-解锁 */
    const SCENE_UNLOCK = 'unlock';
    
    /** @var string 操作场景-切换主题 */
    const SCENE_THEME = 'theme';
    
    /** @var string 操作场景-登录 */
    const SCENE_LOGIN_SUCCESS = 'login_success';
    
    /** @var string 操作场景-登录错误 */
    const SCENE_LOGIN_ERROR = 'login_error';
    
    /** @var string 操作场景-修改个人资料 */
    const SCENE_PROFILE = 'profile';
    
    /** @var string 操作场景-自定义验证 */
    const SCENE_CUSTOM = 'custom';
    
    // +----------------------------------------------------
    // + 性别
    // +----------------------------------------------------
    /** @var int 男 */
    const SEX_MAN = 1;
    
    /** @var int 女 */
    const SEX_WOMEN = 2;
    
    protected string $dataNotFoundMessage = '管理员不存在';
    
    protected string $listNotFoundMessage = '暂无管理员';
    
    protected string $fieldClass          = AdminUserField::class;
    
    protected array  $config;
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * 获取性别
     * @param int|null $sex
     * @return string|array
     */
    public static function getSexMap(int $sex = null) : string|array
    {
        return ArrayHelper::getValueOrSelf(ClassHelper::getConstAttrs(self::class, 'SEX_', ClassHelper::ATTR_NAME), $sex);
    }
    
    
    public function __construct(string $connect = '', bool $force = false)
    {
        $this->config = (array) (Config::get('admin.model.admin_user') ?: []);
        
        parent::__construct($connect, $force);
    }
    
    
    /**
     * 添加管理员
     * @param AdminUserField $data
     * @return AdminUserField
     * @throws Throwable
     */
    public function create(AdminUserField $data) : AdminUserField
    {
        $prepare = $this->trigger(new AdminUserEventCreatePrepare($this, $data), true);
        
        return $this->transaction(function() use ($data, $prepare) {
            $this->validate($data, static::SCENE_CREATE);
            $this->trigger(new AdminUserEventCreateBefore($this, $data, $prepare));
            $info = $this->getInfo($this->insert($data));
            $this->trigger(new AdminUserEventCreateAfter($this, $data, $prepare, $info));
            
            return $info;
        });
    }
    
    
    /**
     * 修改管理员
     * @param AdminUserField $data 数据
     * @param string|Closure $scene 场景或验证回调
     * @return AdminUserField
     * @throws Throwable
     */
    public function modify(AdminUserField $data, string|Closure $scene = self::SCENE_UPDATE) : AdminUserField
    {
        $sceneName = $scene;
        if ($scene instanceof Closure) {
            $sceneName = static::SCENE_CUSTOM;
        }
        $prepare = $this->trigger(new AdminUserEventUpdatePrepare($this, $data, $sceneName), true);
        
        return $this->transaction(function() use ($data, $scene, $sceneName, $prepare) {
            $info = $this->lock(true)->getInfo($data->id);
            $this->validate($data, $scene, $info);
            $this->trigger(new AdminUserEventUpdateBefore($this, $data, $sceneName, $prepare, $info));
            $this->update($data);
            $this->trigger(new AdminUserEventUpdateAfter($this, $data, $sceneName, $prepare, $info, $info = $this->getInfo($info->id)));
            
            return $info;
        });
    }
    
    
    /**
     * 删除管理员
     * @param int $id
     * @return int
     * @throws Throwable
     */
    public function remove(int $id) : int
    {
        $prepare = $this->trigger(new AdminUserEventDeletePrepare($this, $id), true);
        
        return $this->transaction(function() use ($id, $prepare) {
            $info = $this->lock(true)->getInfo($id);
            if ($info->system) {
                throw new RuntimeException('系统管理员禁止删除');
            }
            
            $this->trigger(new AdminUserEventDeleteBefore($this, $info->id, $info, $prepare));
            $result = $this->delete($info->id);
            $this->trigger(new AdminUserEventDeleteAfter($this, $info->id, $info, $prepare));
            
            return $result;
        });
    }
    
    
    /**
     * 执行登录
     * @param string $username 账号
     * @param string $password 密码
     * @param int    $type 登录类型
     * @return SystemTokenField
     * @throws Throwable
     */
    public function login(string $username, string $password, int $type) : SystemTokenField
    {
        $username = trim($username);
        $password = trim($password);
        $setting  = AdminSetting::instance();
        if (!$username) {
            throw new VerifyException('请输入账号', 'username_empty');
        }
        if (!$password) {
            throw new VerifyException('请输入密码', 'password_empty');
        }
        
        $this->trigger(new AdminUserEventLoginPrepare());
        
        // 查询账户
        if (Validate::checkRule($username, ValidateRule::init()->isEmail())) {
            $info = $this->findInfoByEmail($username);
        } elseif ($this->checkPhone($username)) {
            $info = $this->findInfoByPhone($username);
        } else {
            $info = $this->findInfoByUsername($username);
        }
        if (!$info) {
            throw new VerifyException('账号不存在或密码有误', 'username_error');
        }
        
        // 账号被禁用
        if (!$info->checked) {
            throw new VerifyException('该账号被禁用，请联系管理员', 'disabled');
        }
        
        // 错误限制
        $errorMinute     = $setting->getLoginErrorMinute();
        $errorLockMinute = $setting->getLoginErrorLockMinute();
        $errorMax        = $setting->getLoginErrorMax();
        $checkError      = $errorMinute > 0 && $errorLockMinute > 0 && $errorMax > 0;
        $localError      = "该账户于 `%s` 分钟内，密码错误超过 `%s` 次\n被锁定至 `%s`";
        
        // 是否已经锁定
        if ($checkError && $info->isTempLock) {
            throw new VerifyException(sprintf($localError, $errorMinute, $errorMax, $info->formatErrorRelease), 'locked');
        }
        
        // 检测密码
        if (!static::verifyPassword($password, $info->password)) {
            // 记录密码错误次数
            $errorMsg = '账号不存在或密码错误';
            if ($checkError) {
                $data = AdminUserField::init();
                
                // 1. 从未出错
                // 2. 已锁定并过期
                // 3. 已出错且连续时间不满足
                // 则清理锁定
                if (!$info->errorTime || ($info->errorRelease > 0 && $info->errorRelease < time()) || ($info->errorTime > 0 && time() - $info->errorTime >= $errorMinute * 60)) {
                    $data->setErrorTime(time());
                    $data->setErrorRelease(0);
                    $data->setErrorTotal(1);
                } else {
                    $data->setErrorTotal($info->errorTotal + 1);
                }
                
                // 超过错误次数则锁定
                if ($data->errorTotal >= $errorMax) {
                    $data->setErrorRelease(time() + $errorLockMinute * 60);
                    $errorMsg = sprintf($localError, $errorMinute, $errorMax, date('Y-m-d H:i:s', $data->errorRelease));
                } else {
                    $errorMsg = sprintf("密码错误，超过%s次将锁定账户，累计第%s次", $errorMax, $data->errorTotal);
                }
                
                $data->setId($info->id);
                $this->modify($data, static::SCENE_LOGIN_ERROR);
            }
            
            throw new VerifyException($errorMsg, 'password_error');
        }
        
        return $this->setLoginSuccess($info, $type);
    }
    
    
    /**
     * 校验是否登录
     * @param string $authKey 通行秘钥
     * @param int    $type 登录类型
     * @return AdminUserField
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function checkLogin(string $authKey, int $type) : AdminUserField
    {
        try {
            $decode    = SystemToken::class()::check($this->getSecret(), $authKey);
            $tokenInfo = $decode['info'];
            $extend    = $decode['extend'];
            if ($tokenInfo->type !== $type) {
                throw new LogicException('登录类型不匹配');
            }
        } catch (Throwable $e) {
            throw new VerifyException('请登录', 'decode', 0, $e);
        }
        
        $user = $this->getInfo($tokenInfo->userId);
        if (!$user->checked) {
            throw new VerifyException('您的账户被禁用', 'disabled');
        }
        
        $callback = ArrayHelper::get($this->config, 'login.auth_extend.check');
        if ($callback instanceof Closure) {
            if (false === Container::getInstance()->invokeFunction($callback, [$extend, $tokenInfo, $user])) {
                throw new VerifyException('请登录', 'extend');
            }
        }
        
        return $user;
    }
    
    
    /**
     * 生成通行秘钥
     * @param SystemTokenField $token 系统用户通行token信息
     * @param AdminUserField   $user 用户信息
     * @param bool             $saveLogin 是否保存登录
     * @return string
     */
    public function createAuthKey(SystemTokenField $token, AdminUserField $user, bool $saveLogin = false) : string
    {
        $callback = ArrayHelper::get($this->config, 'login.auth_extend.create');
        $extend   = '';
        if ($callback instanceof Closure) {
            $extend = Container::getInstance()->invokeFunction($callback, [$user, $token]);
        }
        
        return SystemToken::class()::encode(
            $this->getSecret(),
            $token,
            $saveLogin ? AdminSetting::instance()->getSaveLogin() : 0,
            $extend
        );
    }
    
    
    /**
     * 设为登录成功
     * @param AdminUserField $userInfo 用户信息
     * @param int            $type 登录类型
     * @return SystemTokenField
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    public function setLoginSuccess(AdminUserField $userInfo, int $type) : SystemTokenField
    {
        $data = AdminUserField::init();
        $data->setId($userInfo->id);
        $data->setLoginTime(time());
        $data->setLoginIp(Request::ip());
        $data->setLastTime(AdminUserField::loginTime());
        $data->setLastIp(AdminUserField::loginIp());
        $data->setLoginTotal(AdminUserField::loginTotal('+', 1));
        $data->setErrorRelease(0);
        $data->setErrorTotal(0);
        $data->setErrorTime(0);
        
        $tokenInfo = null;
        $this
            ->listen(AdminUserEventUpdateAfter::class, function(AdminUserEventUpdateAfter $event) use ($type, &$tokenInfo) {
                $token = '';
                if (AdminSetting::instance()->isMultipleClient()) {
                    $extend = ArrayHelper::get($this->config, 'login.multiple_client_token');
                    if ($extend instanceof Closure) {
                        $extend = (string) Container::getInstance()->invokeFunction($extend, [$event->finalInfo]);
                    }
                    
                    $token = md5(implode(',', [
                        'BusyPHP',
                        $event->finalInfo->id,
                        $type,
                        $extend
                    ]));
                }
                
                $tokenInfo = SystemToken::init()
                    ->updateToken($type, SystemToken::class()::DEFAULT_USER_TYPE, $event->finalInfo->id, $token);
            })
            ->modify($data, static::SCENE_LOGIN_SUCCESS);
        
        return $tokenInfo;
    }
    
    
    /**
     * 设置启用/禁用
     * @param int  $id
     * @param bool $checked
     * @throws Throwable
     */
    public function changeChecked($id, bool $checked)
    {
        $update = AdminUserField::init();
        $update->setId($id);
        $update->setChecked($checked);
        $this->modify($update, static::SCENE_CHECKED);
    }
    
    
    /**
     * 设置主题
     * @param int   $id
     * @param array $theme
     * @throws Throwable
     */
    public function setTheme($id, array $theme)
    {
        $update = AdminUserField::init();
        $update->setId($id);
        $update->setTheme($theme);
        $this->modify($update, static::SCENE_THEME);
    }
    
    
    /**
     * 解锁
     * @param int $id
     * @throws Throwable
     */
    public function unlock(int $id)
    {
        $update = AdminUserField::init();
        $update->setId($id);
        $update->setErrorRelease(0);
        $update->setErrorTime(0);
        $update->setErrorTotal(0);
        
        $this->modify($update, static::SCENE_UNLOCK);
    }
    
    
    /**
     * 获取解密秘钥
     * @return string
     */
    protected function getSecret() : string
    {
        return ArrayHelper::get($this->config, 'login.auth_secret') ?: 'Pe78mUtfomfhHqSHGpQ3jAlI';
    }
    
    
    /**
     * hash密码
     * @param string $password
     * @return string
     */
    protected static function hashPassword(string $password) : string
    {
        return md5(md5($password) . 'Admin.BusyPHP');
    }
    
    
    /**
     * 生成密码
     * @param string $password
     * @return string
     */
    public static function createPassword(string $password) : string
    {
        return password_hash(static::hashPassword($password), PASSWORD_DEFAULT);
    }
    
    
    /**
     * 校验密码
     * @param string $inputPassword
     * @param string $dbPassword
     * @return bool
     */
    public static function verifyPassword(string $inputPassword, string $dbPassword) : bool
    {
        return password_verify(static::hashPassword($inputPassword), $dbPassword);
    }
    
    
    /**
     * 获取验证配置
     * @param string $name 配置名称
     * @param null   $default 默认值
     * @return mixed
     */
    public function getValidateConfig(string $name, $default = null) : mixed
    {
        return ArrayHelper::get($this->config, 'validate.' . $name, $default);
    }
    
    
    /**
     * 获取模版校验规则
     * @return array
     */
    public function getViewValidateRule() : array
    {
        return [
            'avatar'   => $this->getValidateConfig('avatar', false),
            'nickname' => $this->getValidateConfig('nickname', false),
            'phone'    => [
                'required' => $this->getValidateConfig('phone.required', false),
                'regex'    => $this->getValidateConfig('phone.js_regex', '^1[3-9]\d{9}$'),
            ],
            'email'    => $this->getValidateConfig('email', false),
            'name'     => $this->getValidateConfig('name', false),
            'card_no'  => [
                'required' => $this->getValidateConfig('card_no.required', false),
                'unique'   => $this->getValidateConfig('card_no.unique', false),
                'identity' => $this->getValidateConfig('card_no.identity', true)
            ],
            'sex'      => $this->getValidateConfig('sex', false),
            'birthday' => $this->getValidateConfig('birthday', false),
            'tel'      => [
                'required' => $this->getValidateConfig('tel.required', false),
                'regex'    => $this->getValidateConfig('tel.js_regex', null)
            ]
        ];
    }
    
    
    /**
     * 校验手机号
     * @param string $phone 手机号
     * @return bool|string 返回true代表验证成功，返回字符串代表验证失败的消息，返回false则使用内置错误消息文案
     */
    public function checkPhone(string $phone) : bool|string
    {
        if ($regex = $this->getValidateConfig('phone.regex')) {
            if ($regex instanceof Closure) {
                $res = Container::getInstance()->invoke($regex, [$phone]);
                if ($res === false) {
                    return false;
                }
                
                return $res ?: true;
            } else {
                return Validate::checkRule($phone, ValidateRule::init()->regex($regex));
            }
        }
        
        return Validate::checkRule($phone, ValidateRule::init()->isMobile());
    }
}