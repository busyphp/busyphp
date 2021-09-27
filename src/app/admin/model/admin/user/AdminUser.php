<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\user;

use BusyPHP\exception\ParamInvalidException;
use BusyPHP\helper\util\Regex;
use BusyPHP\model;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\crypt\TripleDES;
use BusyPHP\app\admin\setting\AdminSetting;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\facade\Cookie;
use think\facade\Session;
use think\helper\Str;

/**
 * 管理员模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/30 下午5:57 下午 AdminUser.php $
 * @method AdminUserInfo findInfo($data = null, $notFoundMessage = null)
 * @method AdminUserInfo getInfo($data, $notFoundMessage = null)
 * @method AdminUserInfo[] selectList()
 */
class AdminUser extends Model
{
    //+--------------------------------------
    //| 登录相关常量
    //+--------------------------------------
    const COOKIE_AUTH_KEY      = 'admin_auth_key';
    
    const COOKIE_USER_ID       = 'admin_user_id';
    
    const SESSION_OPERATE_TIME = 'admin_operate_time';
    
    protected $dataNotFoundMessage = '管理员不存在';
    
    protected $listNotFoundMessage = '暂无管理员';
    
    protected $findInfoFilter      = 'intval';
    
    protected $bindParseClass      = AdminUserInfo::class;
    
    
    /**
     * 通过管理员账号获取管理员信息
     * @param string $username 账号
     * @return AdminUserInfo
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getInfoByUsername($username)
    {
        return $this->whereEntity(AdminUserField::username(trim($username)))->failException(true)->findInfo();
    }
    
    
    /**
     * 通过邮箱账号获取管理员信息
     * @param string $email 账号
     * @return AdminUserInfo
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getInfoByEmail($email)
    {
        return $this->whereEntity(AdminUserField::email(trim($email)))->failException(true)->findInfo();
    }
    
    
    /**
     * 通过邮箱账号获取管理员信息
     * @param string $phone 账号
     * @return AdminUserInfo
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getInfoByPhone($phone)
    {
        return $this->whereEntity(AdminUserField::phone(trim($phone)))->failException(true)->findInfo();
    }
    
    
    /**
     * 添加管理员
     * @param AdminUserField $insert
     * @return int
     * @throws Exception
     */
    public function insertData(AdminUserField $insert)
    {
        if (!$insert->username || !$insert->password || !$insert->groupIds) {
            throw new ParamInvalidException('username,password,group_ids');
        }
        
        $this->startTrans();
        try {
            $this->checkRepeat($insert);
            $insert->createTime = time();
            $insert->updateTime = time();
            $insert->password   = password_hash($insert->password, PASSWORD_DEFAULT);
            
            $id = $this->addData($insert);
            
            $this->commit();
            
            return $id;
        } catch (Exception $e) {
            $this->rollback();
            
            throw $e;
        }
    }
    
    
    /**
     * 修改管理员
     * @param AdminUserField $update
     * @throws ParamInvalidException
     * @throws Exception
     */
    public function updateData($update)
    {
        if ($update->id < 1) {
            throw new ParamInvalidException('id');
        }
        
        $this->startTrans();
        try {
            $this->lock(true)->getInfo($update->id);
            $this->checkRepeat($update, $update->id);
            
            // 密码
            if ($update->password) {
                $update->password = password_hash($update->password, PASSWORD_DEFAULT);
            }
            
            $update->updateTime = time();
            $this->whereEntity(AdminUserField::id($update->id))->saveData($update);
            
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            
            throw $e;
        }
    }
    
    
    /**
     * 查重
     * @param AdminUserField $data
     * @param int            $id
     * @throws VerifyException
     */
    protected function checkRepeat(AdminUserField $data, $id = 0)
    {
        if ($data->username) {
            $this->whereEntity(AdminUserField::username($data->username));
            if ($id > 0) {
                $this->whereEntity(AdminUserField::id('<>', $id));
            }
            if ($this->count() > 0) {
                throw new VerifyException('该用户名已存在', 'username');
            }
        }
        
        if ($data->phone) {
            $this->whereEntity(AdminUserField::phone($data->phone));
            if ($id > 0) {
                $this->whereEntity(AdminUserField::id('<>', $id));
            }
            if ($this->count() > 0) {
                throw new VerifyException('该手机号已存在', 'phone');
            }
        }
        
        if ($data->email) {
            $this->whereEntity(AdminUserField::email($data->email));
            if ($id > 0) {
                $this->whereEntity(AdminUserField::id('<>', $id));
            }
            if ($this->count() > 0) {
                throw new VerifyException('该邮箱地址已存在', 'phone');
            }
        }
    }
    
    
    /**
     * 修改管理员密码
     * @param int    $id
     * @param string $password
     * @param string $confirmPassword
     * @throws ParamInvalidException
     * @throws VerifyException
     * @throws Exception
     */
    public function updatePassword($id, $password, $confirmPassword)
    {
        $saveData           = AdminUserField::init();
        $saveData->id       = floatval($id);
        $saveData->password = self::checkPassword($password, $confirmPassword);
        $this->updateData($saveData);
    }
    
    
    /**
     * 删除管理员
     * @param int $data
     * @return int
     * @throws DataNotFoundException
     * @throws DbException
     * @throws VerifyException
     */
    public function deleteInfo($data) : int
    {
        $info = $this->getInfo($data);
        if ($info->system) {
            throw new VerifyException('系统管理员禁止删除');
        }
        
        return parent::deleteInfo($info->id);
    }
    
    
    /**
     * 执行登录
     * @param string $username 账号
     * @param string $password 密码
     * @param bool   $saveLogin 是否记住登录
     * @return AdminUserInfo
     * @throws VerifyException
     */
    public function login(string $username, string $password, bool $saveLogin = false) : AdminUserInfo
    {
        $username = trim($username);
        $password = trim($password);
        $setting  = AdminSetting::init();
        if (!$username) {
            throw new VerifyException('请输入账号', 'username');
        }
        if (!$password) {
            throw new VerifyException('请输入密码', 'password');
        }
        
        // 进行回调其它参数验证
        $this->triggerCallback(self::CALLBACK_PROCESS, []);
        
        $this->startTrans();
        try {
            // 查询账户
            if (Regex::email($username)) {
                $this->whereEntity(AdminUserField::email($username));
            } elseif (Regex::phone($username)) {
                $this->whereEntity(AdminUserField::phone($username));
            } else {
                $this->whereEntity(AdminUserField::username($username));
            }
            $info = $this->failException(true)->findInfo(null, '账号不存在或密码有误');
            
            // 账号被禁用
            if (!$info->checked) {
                throw new VerifyException('您的账号被禁用，请联系管理员', 'checked');
            }
            
            // 错误限制
            $errorMinute     = $setting->getLoginErrorMinute();
            $errorLockMinute = $setting->getLoginErrorLockMinute();
            $errorMax        = $setting->getLoginErrorMax();
            $checkError      = $errorMinute > 0 && $errorLockMinute > 0 && $errorMax > 0;
            
            // 是否已经锁定
            if ($checkError && $info->isTempLock) {
                $time = date('Y-m-d H:i:s', $info->errorRelease);
                throw new VerifyException("连续密码错误超过{$errorMax}次，已被系统锁定至{$time}");
            }
            
            // 检测密码
            if (!self::verifyPassword($password, $info->password)) {
                // 记录密码错误次数
                $errorMsg  = '账号不存在或密码错误';
                $errorCode = 1;
                if ($checkError) {
                    $errorCode = 2;
                    $save      = AdminUserField::init();
                    
                    // 1. 从未出错
                    // 2. 已锁定并过期
                    // 3. 已出错且连续时间不满足
                    // 则清理锁定
                    if (!$info->errorTime || ($info->errorRelease > 0 && $info->errorRelease < time()) || ($info->errorTime > 0 && time() - $info->errorTime >= $errorMinute * 60)) {
                        $save->errorTime    = time();
                        $save->errorRelease = 0;
                        $save->errorTotal   = 1;
                    } else {
                        $save->errorTotal = $info->errorTotal + 1;
                    }
                    
                    // 超过错误次数则锁定
                    if ($save->errorTotal >= $errorMax) {
                        $save->errorRelease = time() + $errorLockMinute * 60;
                        $time               = date('Y-m-d H:i:s', $save->errorRelease);
                        $errorMsg           = "连续密码错误超过{$errorMax}次，已被系统锁定至{$time}";
                    } else {
                        $errorMsg = "密码错误，超过{$errorMax}次将锁定账户，累计第{$save->errorTotal}次";
                    }
                    
                    $this->whereEntity(AdminUserField::id($info->id))->saveData($save);
                }
                
                throw new VerifyException($errorMsg, 'password', $errorCode);
            }
            
            $result = $this->setLoginSuccess($info, $saveLogin);
            
            $this->commit();
            
            return $result;
        } catch (Exception $e) {
            if ($e instanceof VerifyException && $e->getCode() === 2) {
                $this->commit();
            } else {
                $this->rollback();
            }
            
            throw $e;
        }
    }
    
    
    /**
     * 校验是否登录
     * @param bool $saveOperateTime 是否记录操作时间
     * @return AdminUserInfo
     * @throws DataNotFoundException
     * @throws DbException
     * @throws VerifyException
     */
    public function checkLogin($saveOperateTime = false)
    {
        $cookieUserId  = floatval(Cookie::get(AdminUser::COOKIE_USER_ID, 0));
        $cookieAuthKey = trim(Cookie::get(AdminUser::COOKIE_AUTH_KEY, ''));
        if (!$cookieUserId || !$cookieAuthKey) {
            throw new VerifyException('缺少COOKIE', 'cookie');
        }
        
        $user          = $this->getInfo($cookieUserId);
        $tDes          = new TripleDES($user->token);
        $cookieAuthKey = $tDes->decrypt($cookieAuthKey);
        if (!$cookieAuthKey || $cookieAuthKey != AdminUser::createAuthKey($user, $user->token)) {
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
     * @throws DbException
     */
    public function setLoginSuccess(AdminUserInfo $userInfo, bool $saveLogin = false) : AdminUserInfo
    {
        // 生成密钥
        $token           = AdminSetting::init()->isMultipleClient() ? 'BusyPHPLoginToken' : Str::random();
        $userInfo->token = $token;
        
        $save               = AdminUserField::init();
        $save->id           = $userInfo->id;
        $save->token        = $token;
        $save->loginTime    = time();
        $save->loginIp      = request()->ip();
        $save->lastTime     = AdminUserField::loginTime();
        $save->lastIp       = AdminUserField::loginIp();
        $save->loginTotal   = AdminUserField::loginTotal('+', 1);
        $save->errorRelease = 0;
        $save->errorTotal   = 0;
        $save->errorTime    = 0;
        $this->saveData($save);
        
        // 加密数据
        $tDes          = new TripleDES($token);
        $cookieAuthKey = $tDes->encrypt(self::createAuthKey($userInfo, $token));
        $cookieUserId  = $userInfo->id;
        
        // 设置COOKIE
        $expire       = null;
        $saveLoginDay = AdminSetting::init()->getSaveLogin();
        if ($saveLoginDay > 0 && $saveLogin) {
            $expire = 86400 * $saveLoginDay;
        }
        
        Cookie::set(self::COOKIE_AUTH_KEY, $cookieAuthKey, $expire);
        Cookie::set(self::COOKIE_USER_ID, $cookieUserId, $expire);
        $this->setOperateTime();
        
        return $userInfo;
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
     * @throws Exception
     */
    public function changeChecked($id, bool $checked)
    {
        $update = AdminUserField::init();
        $update->setId($id);
        $update->checked = $checked;
        $this->updateData($update);
    }
    
    
    /**
     * 解锁
     * @param $id
     * @throws Exception
     */
    public function unlock($id)
    {
        $update = AdminUserField::init();
        $update->setId($id);
        $update->errorRelease = 0;
        $update->errorTime    = 0;
        $update->errorTotal   = 0;
        $this->updateData($update);
    }
    
    
    /**
     * 执行退出登录
     */
    public static function outLogin()
    {
        Cookie::delete(self::COOKIE_AUTH_KEY);
        Cookie::delete(self::COOKIE_USER_ID);
    }
    
    
    /**
     * 创建COOKIE密钥
     * @param AdminUserInfo $userInfo
     * @param string        $token
     * @return string
     */
    public static function createAuthKey(AdminUserInfo $userInfo, $token)
    {
        return md5(implode('_', [
            $token,
            $userInfo->id,
            $userInfo->checked ? 1 : 0,
            $userInfo->username
        ]));
    }
    
    
    /**
     * 生成密码
     * @param $password
     * @return string
     */
    public static function createPassword($password) : string
    {
        return md5(md5($password) . 'Admin.BusyPHP');
    }
    
    
    /**
     * 校验密码
     * @param $inputPassword
     * @param $dbPassword
     * @return bool
     */
    public static function verifyPassword($inputPassword, $dbPassword)
    {
        return password_verify(self::createPassword($inputPassword), $dbPassword);
    }
    
    
    /**
     * 校验密码
     * @param $password
     * @param $confirmPassword
     * @return string
     * @throws VerifyException
     */
    public static function checkPassword($password, $confirmPassword)
    {
        $password        = trim($password);
        $confirmPassword = trim($confirmPassword);
        if (!$password) {
            throw new VerifyException('请输入密码', 'password');
        }
        
        if (strlen($password) < 6) {
            throw new VerifyException('密码不能小余6位字符', 'password');
        }
        if (strlen($password) > 20) {
            throw new VerifyException('密码不能大于20位字符', 'password');
        }
        if (!$confirmPassword) {
            throw new VerifyException('请输入确认密码', 'confirm_password');
        }
        if ($confirmPassword != $password) {
            throw new VerifyException('两次输入的密码不一致', 'confirm_password');
        }
        
        return self::createPassword($password);
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
}