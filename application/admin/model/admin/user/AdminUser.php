<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\user;

use BusyPHP\app\admin\setting\AdminSetting;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\TripleDesHelper;
use BusyPHP\model;
use BusyPHP\model\Entity;
use RuntimeException;
use think\Container;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Request;
use think\facade\Session;
use think\facade\Validate;
use think\helper\Str;
use think\validate\ValidateRule;
use Throwable;

/**
 * 管理员模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/30 下午5:57 下午 AdminUser.php $
 * @method AdminUserInfo getInfo(int $id, string $notFoundMessage = null)
 * @method AdminUserInfo|null findInfo(int $id = null, string $notFoundMessage = null)
 * @method AdminUserInfo[] selectList()
 * @method AdminUserInfo[] buildListWithField(array $values, string|Entity $key = null, string|Entity $field = null)
 * @method AdminUserInfo getInfoByUsername(string $username, string $notFoundMessage = null)
 * @method AdminUserInfo getInfoByEmail(string $email, string $notFoundMessage = null)
 * @method AdminUserInfo getInfoByPhone(string $phone, string $notFoundMessage = null)
 * @method AdminUserInfo|null findInfoByUsername(string $username, string $notFoundMessage = null)
 * @method AdminUserInfo|null findInfoByEmail(string $email, string $notFoundMessage = null)
 * @method AdminUserInfo|null findInfoByPhone(string $phone, string $notFoundMessage = null)
 * @method static static getClass()
 */
class AdminUser extends Model
{
    //+--------------------------------------
    //| 登录相关常量
    //+--------------------------------------
    const COOKIE_AUTH_KEY      = 'admin_auth_key';
    
    const COOKIE_USER_ID       = 'admin_user_id';
    
    const COOKIE_USER_THEME    = 'admin_user_theme';
    
    const SESSION_OPERATE_TIME = 'admin_operate_time';
    
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
    
    protected $dataNotFoundMessage = '管理员不存在';
    
    protected $listNotFoundMessage = '暂无管理员';
    
    protected $findInfoFilter      = 'intval';
    
    protected $bindParseClass      = AdminUserInfo::class;
    
    
    /**
     * @inheritDoc
     */
    final protected static function defineClass() : string
    {
        return self::class;
    }
    
    
    /**
     * 添加管理员
     * @param AdminUserField $data
     * @return AdminUserInfo
     * @throws Throwable
     */
    public function createInfo(AdminUserField $data) : AdminUserInfo
    {
        $prepare = $this->trigger(new AdminUserEventCreatePrepare($this, $data), true);
        
        return $this->transaction(function() use ($data, $prepare) {
            $this->validate($data, self::SCENE_ADD);
            $this->trigger(new AdminUserEventCreateBefore($this, $data, $prepare));
            $this->trigger(new AdminUserEventCreateAfter($this, $data, $prepare, $info = $this->getInfo($this->addData($data))));
            
            return $info;
        });
    }
    
    
    /**
     * 修改管理员
     * @param AdminUserField $data 数据
     * @param string         $scene 场景
     * @throws Throwable
     */
    public function updateInfo(AdminUserField $data, string $scene = self::SCENE_EDIT) : AdminUserInfo
    {
        $prepare = $this->trigger(new AdminUserEventUpdatePrepare($this, $data, $scene), true);
        
        return $this->transaction(function() use ($data, $scene, $prepare) {
            $info = $this->lock(true)->getInfo($data->id);
            $this->validate($data, $scene);
            $this->trigger(new AdminUserEventUpdateBefore($this, $data, $scene, $prepare, $info));
            $this->saveData($data);
            $this->trigger(new AdminUserEventUpdateAfter($this, $data, $scene, $prepare, $info, $info = $this->getInfo($info->id)));
            
            return $info;
        });
    }
    
    
    /**
     * 删除管理员
     * @param int $data
     * @return int
     * @throws Throwable
     */
    public function deleteInfo($data) : int
    {
        $id      = (int) $data;
        $prepare = $this->trigger(new AdminUserEventDeletePrepare($this, $id), true);
        
        return $this->transaction(function() use ($id, $prepare) {
            $info = $this->lock(true)->getInfo($id);
            if ($info->system) {
                throw new RuntimeException('系统管理员禁止删除');
            }
            
            $this->trigger(new AdminUserEventDeleteBefore($this, $info->id, $info, $prepare));
            $result = parent::deleteInfo($info->id);
            $this->trigger(new AdminUserEventDeleteAfter($this, $info->id, $info, $prepare));
            
            return $result;
        });
    }
    
    
    /**
     * 执行登录
     * @param string $username 账号
     * @param string $password 密码
     * @param bool   $saveLogin 是否记住登录
     * @return AdminUserInfo
     * @throws Throwable
     */
    public function login(string $username, string $password, bool $saveLogin = false) : AdminUserInfo
    {
        $username = trim($username);
        $password = trim($password);
        $setting  = AdminSetting::init();
        if (!$username) {
            throw new VerifyException('请输入账号', 'username_empty');
        }
        if (!$password) {
            throw new VerifyException('请输入密码', 'password_empty');
        }
        
        $this->trigger(new AdminUserEventLoginPrepare());
        
        // 查询账户
        if (Validate::checkRule($username, ValidateRule::isEmail())) {
            $info = $this->findInfoByEmail($username);
        } elseif (self::getClass()::checkPhone($username)) {
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
        if (!self::getClass()::verifyPassword($password, $info->password)) {
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
                $this->updateInfo($data, self::SCENE_LOGIN_ERROR);
            }
            
            throw new VerifyException($errorMsg, 'password_error');
        }
        
        return $this->setLoginSuccess($info, $saveLogin);
    }
    
    
    /**
     * 校验是否登录
     * @param bool $saveOperateTime 是否记录操作时间
     * @return AdminUserInfo
     * @throws DataNotFoundException
     * @throws DbException
     * @throws VerifyException
     */
    public function checkLogin($saveOperateTime = false) : AdminUserInfo
    {
        $cookieUserId  = intval(Cookie::get(AdminUser::COOKIE_USER_ID, '0'));
        $cookieAuthKey = trim(Cookie::get(AdminUser::COOKIE_AUTH_KEY, ''));
        if (!$cookieUserId || !$cookieAuthKey) {
            throw new VerifyException('缺少COOKIE', 'cookie');
        }
        
        $user          = $this->getInfo($cookieUserId);
        $cookieAuthKey = TripleDesHelper::decrypt($cookieAuthKey, $user->token);
        if (!$cookieAuthKey || $cookieAuthKey != self::getClass()::createAuthKey($user, $user->token)) {
            throw new VerifyException('通行密钥错误', 'auth');
        }
        
        // 验证登录时常
        if ($often = AdminSetting::init()->getOften()) {
            $operateTime = Session::get(self::SESSION_OPERATE_TIME);
            if ($operateTime > 0 && time() - ($often * 60) > $operateTime) {
                throw new VerifyException('登录超时', 'timeout');
            }
        }
        
        // 记录操作时间
        if ($saveOperateTime) {
            $this->setOperateTime();
        }
        
        return $user;
    }
    
    
    /**
     * 设为登录成功
     * @param AdminUserInfo $userInfo
     * @param bool          $saveLogin 是否记住登录
     * @return AdminUserInfo
     * @throws Throwable
     */
    public function setLoginSuccess(AdminUserInfo $userInfo, bool $saveLogin = false) : AdminUserInfo
    {
        $token = AdminSetting::init()->isMultipleClient() ? 'BusyPHPLoginToken' : Str::random();
        $data  = AdminUserField::init();
        $data->setId($userInfo->id);
        $data->setToken($token);
        $data->setLoginTime(time());
        $data->setLoginIp(Request::ip());
        $data->setLastTime(AdminUserField::loginTime());
        $data->setLastIp(AdminUserField::loginIp());
        $data->setLoginTotal(AdminUserField::loginTotal('+', 1));
        $data->setErrorRelease(0);
        $data->setErrorTotal(0);
        $data->setErrorTime(0);
        $this->updateInfo($data, self::SCENE_LOGIN_SUCCESS);
        
        // 设置COOKIE
        $expire       = null;
        $saveLoginDay = AdminSetting::init()->getSaveLogin();
        if ($saveLoginDay > 0 && $saveLogin) {
            $expire = 86400 * $saveLoginDay;
        }
        
        Cookie::set(self::COOKIE_AUTH_KEY, TripleDesHelper::encrypt(self::getClass()::createAuthKey($userInfo, $token), $token), $expire);
        Cookie::set(self::COOKIE_USER_ID, (string) $userInfo->id, 86400 * 365);
        $this->saveThemeToCookie($userInfo->id, $userInfo->theme);
        $this->setOperateTime();
        
        return $this->getInfo($userInfo->id);
    }
    
    
    /**
     * 设置操作时间
     */
    private function setOperateTime()
    {
        if (AdminSetting::init()->getOften()) {
            Session::set(self::SESSION_OPERATE_TIME, time());
        }
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
        $this->updateInfo($update, self::SCENE_CHECKED);
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
        $this->updateInfo($update, self::SCENE_THEME);
        $this->saveThemeToCookie($id, $update->theme);
    }
    
    
    /**
     * 保存主题到cookie
     * @param $id
     * @param $theme
     */
    protected function saveThemeToCookie($id, $theme)
    {
        Cookie::set(self::COOKIE_USER_THEME . $id, is_array($theme) ? json_encode($theme, JSON_UNESCAPED_UNICODE) : $theme, 86400 * 365);
    }
    
    
    /**
     * 获取主题
     * @param AdminUserInfo|null $userInfo
     * @return array{skin: string, nav_mode: bool, nav_single_hold: bool}
     */
    public function getTheme(?AdminUserInfo $userInfo = null) : array
    {
        if ($userInfo) {
            $theme = $userInfo->theme;
        } else {
            $userId = Cookie::get(self::COOKIE_USER_ID);
            $theme  = Cookie::get(self::COOKIE_USER_THEME . $userId);
            $theme  = json_decode((string) $theme, true) ?: [];
        }
        
        $theme['skin']            = trim($theme['skin'] ?? '');
        $theme['skin']            = $theme['skin'] ?: Config::get('app.admin.theme_skin', 'default');
        $theme['nav_mode']        = isset($theme['nav_mode']) ? (intval($theme['nav_mode']) > 0) : Config::get('app.admin.theme_nav_mode', false);
        $theme['nav_single_hold'] = isset($theme['nav_single_hold']) ? (intval($theme['nav_single_hold']) > 0) : Config::get('app.admin.theme_nav_single_hold', false);
        
        return $theme;
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
        
        $this->updateInfo($update, self::SCENE_UNLOCK);
    }
    
    
    /**
     * 执行退出登录
     */
    public function outLogin()
    {
        Cookie::delete(self::COOKIE_AUTH_KEY);
    }
    
    
    /**
     * 清理用户登录密钥
     * @throws DbException
     */
    public function clearToken()
    {
        $this->whereEntity(AdminUserField::id('>', 0))->setField(AdminUserField::token(), '');
        $this->clearCache();
    }
    
    
    /**
     * 创建COOKIE密钥
     * @param AdminUserInfo $userInfo
     * @param string        $token
     * @return string
     */
    public static function createAuthKey(AdminUserInfo $userInfo, string $token) : string
    {
        return md5(implode('_', [
            $token,
            $userInfo->id,
            $userInfo->checked ? 1 : 0,
            $userInfo->username
        ]));
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
     * 校验手机号
     * @param string $phone
     * @return false|string
     */
    public static function checkPhone(string $phone)
    {
        if ($regex = self::getClass()::getCheckPhoneMatch()) {
            if (is_callable($regex)) {
                $res = Container::getInstance()->invoke($regex, [$phone]);
                if ($res === false) {
                    return false;
                }
                
                return $res ?: true;
            } else {
                return Validate::checkRule($phone, ValidateRule::regex($regex));
            }
        }
        
        return Validate::checkRule($phone, ValidateRule::isMobile());
    }
    
    
    /**
     * 获取检测手机号配置
     * @return string|callable
     */
    public static function getCheckPhoneMatch()
    {
        return self::getDefine('check_phone_match', '');
    }
}