<?php

namespace BusyPHP\app\admin\model\admin\user;

use BusyPHP\exception\ParamInvalidException;
use BusyPHP\helper\util\Regex;
use BusyPHP\model;
use BusyPHP\exception\SQLException;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\crypt\TripleDES;
use BusyPHP\app\admin\setting\AdminSetting;
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
     * 获取管理员信息缓存
     * @param int  $id 管理员ID
     * @param bool $must 是否强制获取
     * @return AdminUserInfo
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getInfoByCache($id, $must = false)
    {
        $key  = 'user_' . $id;
        $info = $this->getCache($key);
        if (!$info || $must) {
            $info = $this->getInfo($id);
            $this->setCache($key, $info);
        }
        
        return $info;
    }
    
    
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
     * @throws DbException
     */
    public function insertData($insert)
    {
        $insert->createTime = time();
        $insert->updateTime = time();
        
        return $this->addData($insert);
    }
    
    
    /**
     * 修改管理员
     * @param AdminUserField $update
     * @throws DbException
     * @throws ParamInvalidException
     */
    public function updateData($update)
    {
        if ($update->id < 1) {
            throw new ParamInvalidException('id');
        }
        
        $update->updateTime = time();
        $this->whereEntity(AdminUserField::id($update->id))->saveData($update);
    }
    
    
    /**
     * 修改管理员密码
     * @param int    $adminUserId
     * @param string $password
     * @param string $confirmPassword
     * @throws DbException
     * @throws ParamInvalidException
     * @throws VerifyException
     */
    public function updatePassword($adminUserId, $password, $confirmPassword)
    {
        $saveData           = AdminUserField::init();
        $saveData->id       = floatval($adminUserId);
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
        if ($info->isSystem) {
            throw new VerifyException('系统管理员禁止删除');
        }
        
        return parent::deleteInfo($info->id);
    }
    
    
    /**
     * 执行登录
     * @param string $username 账号
     * @param string $password 密码
     * @return AdminUserInfo
     * @throws DbException
     * @throws VerifyException
     */
    public function login($username, $password) : AdminUserInfo
    {
        $username = trim($username);
        $password = trim($password);
        if (!$username) {
            throw new VerifyException('请输入账号', 'username');
        }
        if (!$password) {
            throw new VerifyException('请输入密码', 'password');
        }
        
        // 进行回调其它参数验证
        $this->triggerCallback(self::CALLBACK_PROCESS, []);
        
        try {
            if (Regex::email($username)) {
                $user = $this->getInfoByEmail($username);
            } elseif (Regex::phone($username)) {
                $user = $this->getInfoByPhone($username);
            } else {
                $user = $this->getInfoByUsername($username);
            }
        } catch (DataNotFoundException $e) {
            throw new VerifyException('账号不存在或密码有误');
        }
        
        // 对比密码
        if ($user->password != self::createPassword($password)) {
            throw new VerifyException('账号不存在或密码错误', 'password', 1);
        }
        
        // 账号未审核
        if (!$user->checked) {
            throw new VerifyException('抱歉，您的账号被禁止登录，请联系管理员', 'checked');
        }
        
        return $this->setLoginSuccess($user);
    }
    
    
    /**
     * 校验是否登录
     * @return AdminUserInfo
     * @throws DataNotFoundException
     * @throws DbException
     * @throws VerifyException
     */
    public function checkLogin()
    {
        $cookieUserId  = floatval(Cookie::get(AdminUser::COOKIE_USER_ID));
        $cookieAuthKey = trim(Cookie::get(AdminUser::COOKIE_AUTH_KEY));
        if (!$cookieUserId || !$cookieAuthKey) {
            throw new VerifyException('缺少COOKIE', 'cookie');
        }
        
        $user          = $this->getInfoByCache($cookieUserId);
        $tDes          = new TripleDES($user['token']);
        $cookieAuthKey = $tDes->decrypt($cookieAuthKey);
        if (!$cookieAuthKey || $cookieAuthKey != AdminUser::createAuthKey($user, $user['token'])) {
            throw new VerifyException('通行密钥错误', 'auth');
        }
        
        // 验证登录时常
        if ($often = AdminSetting::init()->getOften()) {
            $operateTime = Session::get(self::SESSION_OPERATE_TIME);
            if ($operateTime > 0 && time() - ($often * 60) > $operateTime) {
                throw new VerifyException('登录超时', 'timeout');
            }
        }
        $this->setOperateTime();
        
        return $user;
    }
    
    
    /**
     * 设为登录成功
     * @param AdminUserInfo $userInfo
     * @return AdminUserInfo
     * @throws DbException
     */
    public function setLoginSuccess(AdminUserInfo $userInfo) : AdminUserInfo
    {
        // 生成密钥
        $token           = AdminSetting::init()->isMultipleClient() ? 'BusyPHPLoginToken' : Str::random();
        $userInfo->token = $token;
        
        $save             = AdminUserField::init();
        $save->id         = $userInfo->id;
        $save->token      = $token;
        $save->loginTime  = time();
        $save->loginIp    = request()->ip();
        $save->lastTime   = AdminUserField::loginTime();
        $save->lastIp     = AdminUserField::loginIp();
        $save->loginTotal = AdminUserField::loginTotal('+', 1);
        $this->saveData($save);
        
        // 加密数据
        $tDes          = new TripleDES($token);
        $cookieAuthKey = $tDes->encrypt(self::createAuthKey($userInfo, $token));
        $cookieUserId  = $userInfo['id'];
        
        // 设置COOKIE和SESSION
        Cookie::set(self::COOKIE_AUTH_KEY, $cookieAuthKey);
        Cookie::set(self::COOKIE_USER_ID, $cookieUserId);
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
     * 执行退出登录
     */
    public static function outLogin()
    {
        Cookie::delete(self::COOKIE_AUTH_KEY);
        Cookie::delete(self::COOKIE_USER_ID);
    }
    
    
    /**
     * 创建COOKIE密钥
     * @param $userInfo
     * @param $token
     * @return string
     */
    public static function createAuthKey($userInfo, $token)
    {
        return md5(implode('_', [
            $token,
            $userInfo['id'],
            $userInfo['checked'],
            $userInfo['username']
        ]));
    }
    
    
    /**
     * 生成密码
     * @param $password
     * @return string
     */
    public static function createPassword($password)
    {
        return md5(md5($password . 'Admin.BusyPHP'));
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
     * @param $method
     * @param $id
     * @param $options
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function onChanged($method, $id, $options)
    {
        $this->getInfoByCache($id, true);
    }
    
    
    /**
     * 清理用户登录密钥
     */
    public function clearToken()
    {
        $this->whereEntity(AdminUserField::id('>', 0))->setField(AdminUserField::token(), '');
        $this->clearCache();
    }
}