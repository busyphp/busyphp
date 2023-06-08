<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\admin\user\AdminUserEventUpdateAfter;
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
     * 用户模型
     * @var AdminUser
     */
    protected AdminUser $model;
    
    /**
     * 用户模型字段类
     * @var string|AdminUserField
     */
    protected mixed $field;
    
    
    protected function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model = AdminUser::init();
        $this->field = $this->model->getFieldClass();
    }
    
    
    /**
     * 修改个人资料
     * @return Response
     * @throws Throwable
     */
    public function profile() : Response
    {
        if ($this->isPost()) {
            $this->model->modify(
                $this->field::init($this->post())->setId($this->adminUserId),
                $this->model::SCENE_PROFILE
            );
            $this->log()->record(self::LOG_UPDATE, '修改个人资料');
            
            return $this->success('修改成功');
        }
        
        $this->assignProfileData();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 赋值个人资料模版参数
     */
    protected function assignProfileData()
    {
        $sexMap = $this->model::getSexMap();
        unset($sexMap[$this->model::SEX_UNKNOWN]);
        
        $this->setPageTitle('修改个人资料');
        $this->assign([
            'info'     => $this->adminUser,
            'validate' => $this->model->getViewValidateRule(),
            'sex'      => $sexMap
        ]);
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
            
            if (!$this->model::verifyPassword($oldPassword, $this->adminUser->password)) {
                throw new VerifyException('登录密码输入错误', 'old_password');
            }
            
            $this->model->modify(
                $this->field::init($this->post())->setId($this->adminUserId),
                $this->model::SCENE_PASSWORD
            );
            $this->log()
                ->filterParams(['old_password', 'password', 'confirm_password'])
                ->record(self::LOG_UPDATE, '修改个人密码');
            
            return $this->success('修改成功');
        }
        
        $this->assignPasswordData();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 赋值修改密码模版参数
     */
    protected function assignPasswordData()
    {
        $this->setPageTitle('修改个人密码');
        $this->assign([
            'info' => $this->adminUser
        ]);
    }
    
    
    /**
     * 主题设置
     * @return Response
     * @throws Throwable
     */
    public function theme() : Response
    {
        if ($this->isPost()) {
            $this->model
                ->listen(AdminUserEventUpdateAfter::class, function(AdminUserEventUpdateAfter $event) {
                    $this->handle->saveTheme($event->finalInfo->id, $event->finalInfo->theme);
                })
                ->setTheme($this->adminUserId, $this->post('data/a'));
            
            $this->log()->record(self::LOG_UPDATE, '主题设置');
            
            return $this->success('修改成功');
        }
        
        $this->assignThemeData();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 赋值切换主题模版参数
     */
    protected function assignThemeData()
    {
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
        $this->assign('info', $this->handle::getTheme($this->adminUser));
        $this->setPageTitle('主题设置');
    }
    
    
    /**
     * 解析主题文件
     * @param string $cssFile
     * @param int    $index
     * @return array|false
     */
    protected function parseFile(string $cssFile, int $index) : false|array
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
     * 用户资料
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function detail() : Response
    {
        $this->assignDetailData();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 赋值用户资料模版参数
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function assignDetailData()
    {
        $this->setPageTitle($this->param('title/s', 'trim') ?: '用户资料');
        $this->assign([
            'info' => $this->model->getInfo($this->param('id/d'))
        ]);
    }
}