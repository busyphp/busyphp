<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\ArrayHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
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
     * @throws Throwable
     */
    public function profile() : Response
    {
        if ($this->isPost()) {
            AdminUser::init()->modify(
                AdminUserField::init($this->post())->setId($this->adminUserId),
                AdminUser::SCENE_PROFILE
            );
            $this->log()->record(self::LOG_UPDATE, '修改个人资料');
            
            return $this->success('修改成功');
        }
        
        $this->setPageTitle('修改个人资料');
        $this->assign([
            'info' => $this->adminUser
        ]);
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 修改个人密码
     * @return Response
     * @throws Throwable
     */
    public function password() : Response
    {
        if ($this->isPost()) {
            $oldPassword = $this->post('old_password/s', 'trim');
            if (!$oldPassword) {
                throw new VerifyException('请输入登录密码', 'old_password');
            }
            
            if (!AdminUser::class()::verifyPassword($oldPassword, $this->adminUser->password)) {
                throw new VerifyException('登录密码输入错误', 'old_password');
            }
            
            AdminUser::init()->modify(
                AdminUserField::init($this->post())->setId($this->adminUserId),
                AdminUser::SCENE_PASSWORD
            );
            $this->log()
                ->filterParams(['old_password', 'password', 'confirm_password'])
                ->record(self::LOG_UPDATE, '修改个人密码');
            
            return $this->success('修改成功');
        }
        
        $this->setPageTitle('修改个人密码');
        $this->assign([
            'info' => $this->adminUser
        ]);
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 主题设置
     * @return Response
     * @throws Throwable
     */
    public function theme() : Response
    {
        if ($this->isPost()) {
            AdminUser::init()->setTheme($this->adminUserId, $this->post('data/a'));
            $this->log()->record(self::LOG_UPDATE, '主题设置');
            
            return $this->success('修改成功');
        }
        
        $list = [];
        $i    = 0;
        foreach (glob(__DIR__ . '/../../../../assets/admin/themes/*.*') as $i => $cssFile) {
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
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 解析主题文件
     * @param string $cssFile
     * @param int    $index
     * @return array|false
     */
    protected function parseFile(string $cssFile, int $index)
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
    
    
    /**
     * 用户详情
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function detail() : Response
    {
        $this->setPageTitle($this->param('title/s', 'trim') ?: '管理员详情');
        $this->assign([
            'info' => AdminUser::init()->getInfo($this->param('id/d'))
        ]);
        
        return $this->insideDisplay();
    }
}