<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\ArrayHelper;
use Exception;
use think\Response;
use Throwable;

/**
 * 用户通用
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/3 下午下午8:30 UserController.php $
 */
class UserController extends InsideController
{
    /**
     * 修改个人资料
     * @return Response
     * @throws Exception
     */
    public function profile()
    {
        if ($this->isPost()) {
            $update = AdminUserField::init();
            $update->setId($this->adminUserId);
            $update->setUsername($this->post('username/s', 'trim'));
            $update->setPhone($this->post('phone/s', 'trim'));
            $update->setEmail($this->post('email/s', 'trim'));
            $update->setQq($this->post('qq/s', 'trim'));
            AdminUser::init()->whereEntity(AdminUserField::id($this->adminUserId))->updateAdmin($update);
            $this->log()->record(self::LOG_UPDATE, '修改个人资料');
            
            return $this->success('修改成功');
        }
        
        $this->assign('info', $this->adminUser);
        $this->setPageTitle('修改个人资料');
        
        return $this->display();
    }
    
    
    /**
     * 修改个人密码
     * @return Response
     * @throws Exception
     */
    public function password()
    {
        if ($this->isPost()) {
            $oldPassword = $this->post('old_password/s', 'trim');
            if (!$oldPassword) {
                throw new VerifyException('请输入登录密码', 'old_password');
            }
            
            if (!AdminUser::verifyPassword($oldPassword, $this->adminUser->password)) {
                throw new VerifyException('登录密码输入错误', 'old_password');
            }
            
            AdminUser::init()
                ->updatePassword($this->adminUserId, $this->post('password/s', 'trim'), $this->post('confirm_password/s', 'trim'));
            $this->log()
                ->filterParams(['old_password', 'password', 'confirm_password'])
                ->record(self::LOG_UPDATE, '修改个人密码');
            
            return $this->success('修改成功');
        }
        $this->setPageTitle('修改个人密码');
        
        return $this->display();
    }
    
    
    /**
     * 主题设置
     * @return Response
     * @throws Throwable
     */
    public function theme()
    {
        if ($this->isPost()) {
            AdminUser::init()
                ->whereEntity(AdminUserField::id($this->adminUserId))
                ->setTheme($this->adminUserId, $this->post('data/a'));
            $this->log()->record(self::LOG_UPDATE, '主题设置');
            
            return $this->success('修改成功');
        }
        
        $list = [];
        $i    = 0;
        foreach (glob($this->app->getFrameworkPath('app/admin/static/themes/*.*')) as $i => $cssFile) {
            if (false === $config = $this->parseFile($cssFile, $i)) {
                continue;
            }
            
            $list[$config['value']] = $config;
        }
        
        foreach (glob($this->app->getPublicPath('assets/admin/themes/*.*')) as $cssFile) {
            $i++;
            if (false === $config = $this->parseFile($cssFile, $i)) {
                continue;
            }
            $list[$config['value']] = $config;
        }
        
        $list = ArrayHelper::listSortBy($list, 'sort', ArrayHelper::ORDER_BY_ASC);
        $this->assign('list', $list);
        $this->assign('info', AdminUser::init()->getTheme($this->adminUser));
        $this->setPageTitle('主题设置');
        
        return $this->display();
    }
    
    
    /**
     * 解析主题文件
     * @param string $cssFile
     * @param int    $index
     * @return array|false
     */
    private function parseFile(string $cssFile, int $index)
    {
        if (!is_file($cssFile)) {
            return false;
        }
        $content = file_get_contents($cssFile);
        if (!preg_match('/\/\*!!config(.*?)!!\*\//is', $content, $match)) {
            return false;
        }
        
        $config = json_decode($match[1] ?? '{}', true) ?: [];
        if (!isset($config['name'])) {
            return false;
        }
        $config['value'] = pathinfo($cssFile, PATHINFO_FILENAME);
        $config['sort']  = $config['sort'] ?? (1000 + $index);
        
        return $config;
    }
}