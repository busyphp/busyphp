<?php

namespace BusyPHP\app\admin\model\admin\user;

use BusyPHP\helper\util\Regex;
use BusyPHP\model;
use BusyPHP\exception\SQLException;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\crypt\TripleDES;
use BusyPHP\helper\util\Transform;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\setting\AdminSetting;
use think\helper\Str;

/**
 * 管理员模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/30 下午5:57 下午 AdminUser.php $
 */
class AdminUser extends Model
{
    //+--------------------------------------
    //| 登录相关常量
    //+--------------------------------------
    const COOKIE_AUTH_KEY      = 'admin_auth_key';
    
    const COOKIE_USER_ID       = 'admin_user_id';
    
    const SESSION_OPERATE_TIME = 'admin_operate_time';
    
    
    /**
     * 通过ID获取管理员
     * @param mixed $id
     * @return array
     * @throws SQLException
     */
    public function getInfo($id)
    {
        return parent::getInfo(floatval($id), '管理员不存在');
    }
    
    
    /**
     * 获取管理员信息缓存
     * @param int  $id
     * @param bool $must
     * @return array
     */
    public function getInfoByCache($id, $must = false)
    {
        $key  = 'user_' . $id;
        $info = $this->getCache($key);
        if (!$info || $must) {
            try {
                $info = $this->getInfo($id);
                $this->setCache($key, $info);
            } catch (SQLException $e) {
                $this->deleteCache($key);
                $info = [];
            }
        }
        
        return $info;
    }
    
    
    /**
     * 通过管理员账号获取管理员信息
     * @param string $username 账号
     * @return array
     * @throws SQLException
     */
    public function getInfoByUsername($username)
    {
        $where           = AdminUserField::init();
        $where->username = trim($username);
        $info            = $this->whereof($where)->findData();
        if (!$info) {
            throw new SQLException('管理员不存在', $this);
        }
        
        return static::parseInfo($info);
    }
    
    
    /**
     * 通过邮箱账号获取管理员信息
     * @param string $email 账号
     * @return array
     * @throws SQLException
     */
    public function getInfoByEmail($email)
    {
        $where        = AdminUserField::init();
        $where->email = trim($email);
        $info         = $this->whereof($where)->findData();
        if (!$info) {
            throw new SQLException('管理员不存在', $this);
        }
        
        return static::parseInfo($info);
    }
    
    
    /**
     * 通过邮箱账号获取管理员信息
     * @param string $phone 账号
     * @return array
     * @throws SQLException
     */
    public function getInfoByPhone($phone)
    {
        $where        = AdminUserField::init();
        $where->phone = trim($phone);
        $info         = $this->whereof($where)->findData();
        if (!$info) {
            throw new SQLException('管理员不存在', $this);
        }
        
        return static::parseInfo($info);
    }
    
    
    /**
     * 添加管理员
     * @param AdminUserField $insert
     * @return int
     * @throws SQLException
     */
    public function insertData($insert)
    {
        $insert->createTime = time();
        $insert->updateTime = time();
        if (!$insertId = $this->addData($insert)) {
            throw new SQLException('添加管理员失败', $this);
        }
        
        return $insertId;
    }
    
    
    /**
     * 修改管理员
     * @param AdminUserField $update
     * @throws SQLException
     */
    public function updateData($update)
    {
        $update->updateTime = time();
        if (false === $this->saveData($update)) {
            throw new SQLException('修改管理员失败', $this);
        }
    }
    
    
    /**
     * 修改管理员密码
     * @param int    $adminUserId
     * @param string $password
     * @param string $confirmPassword
     * @throws VerifyException
     * @throws SQLException
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
     * @param int $id
     * @return int
     * @throws SQLException
     * @throws VerifyException
     */
    public function del($id)
    {
        $info = $this->getInfo($id);
        if ($info['is_system']) {
            throw new VerifyException('系统管理员禁止删除');
        }
        
        return parent::del($id, '删除管理员失败');
    }
    
    
    /**
     * 执行登录
     * @param string $username 账号
     * @param string $password 密码
     * @return array
     * @throws VerifyException
     * @throws SQLException
     */
    public function login($username, $password)
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
        } catch (SQLException $e) {
            throw new VerifyException('账号不存在或密码有误');
        }
        
        // 对比密码
        if ($user['password'] != self::createPassword($password)) {
            throw new VerifyException('账号不存在或密码错误', 'password', 1);
        }
        
        // 账号未审核
        if (!$user['checked']) {
            throw new VerifyException('抱歉，您的账号被禁止登录，请联系管理员', 'checked');
        }
        
        return $this->setLoginSuccess($user);
    }
    
    
    /**
     * 校验是否登录
     * @return array
     * @throws VerifyException
     */
    public function checkLogin()
    {
        $cookieUserId  = floatval(cookie(AdminUser::COOKIE_USER_ID));
        $cookieAuthKey = trim(cookie(AdminUser::COOKIE_AUTH_KEY));
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
            $operateTime = session(self::SESSION_OPERATE_TIME);
            if ($operateTime > 0 && time() - ($often * 60) > $operateTime) {
                throw new VerifyException('登录超时', 'timeout');
            }
        }
        $this->setOperateTime();
        
        return $user;
    }
    
    
    /**
     * 设为登录成功
     * @param $userInfo
     * @return array
     * @throws SQLException
     */
    public function setLoginSuccess($userInfo)
    {
        // 生成密钥
        $token                = AdminSetting::init()->isMultipleClient() ? 'BusyPHPLoginToken' : Str::random();
        $userInfo['token']    = $token;
        $saveData             = AdminUserField::init();
        $saveData->id         = floatval($userInfo['id']);
        $saveData->token      = $token;
        $saveData->loginTime  = time();
        $saveData->loginIp    = request()->ip();
        $saveData->lastTime   = ['exp', 'login_time'];
        $saveData->lastIp     = ['exp', 'login_ip'];
        $saveData->loginTotal = ['exp', 'login_total+1'];
        if (false === $this->saveData($saveData)) {
            throw new SQLException('登录失败，请稍候再试', $this);
        }
        
        // 加密数据
        $tDes          = new TripleDES($token);
        $cookieAuthKey = $tDes->encrypt(self::createAuthKey($userInfo, $token));
        $cookieUserId  = $userInfo['id'];
        
        // 设置COOKIE和SESSION
        cookie(self::COOKIE_AUTH_KEY, $cookieAuthKey);
        cookie(self::COOKIE_USER_ID, $cookieUserId);
        $this->setOperateTime();
        
        return $userInfo;
    }
    
    
    /**
     * 设置操作时间
     */
    private function setOperateTime()
    {
        if (AdminSetting::init()->getOften()) {
            session(self::SESSION_OPERATE_TIME, time());
        }
    }
    
    
    /**
     * 执行退出登录
     */
    public static function outLogin()
    {
        cookie(self::COOKIE_AUTH_KEY, null);
        cookie(self::COOKIE_USER_ID, null);
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
     * 解析数据列表
     * @param array $list
     * @return array
     */
    public static function parseList($list)
    {
        $groupList = AdminGroup::init()->getList();
        foreach ($list as $i => $r) {
            $r['is_system']  = Transform::dataToBool($r['is_system']);
            $r['is_checked'] = Transform::dataToBool($r['checked']);
            $r['group']      = $groupList[$r['group_id']];
            $list[$i]        = $r;
        }
        
        return parent::parseList($list);
    }
    
    
    protected function onChanged($method, $id, $options)
    {
        $this->getInfoByCache($id, true);
    }
    
    
    /**
     * 清理用户登录密钥
     */
    public function clearToken()
    {
        $this->where('id', '>', 0)->setField('token', '');
        $this->clearCache();
    }
}