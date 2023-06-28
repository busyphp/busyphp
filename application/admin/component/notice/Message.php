<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\component\notice;

use BusyPHP\App;
use BusyPHP\app\admin\model\admin\message\AdminMessage;
use BusyPHP\app\admin\model\admin\message\AdminMessageField;
use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\traits\ContainerDefine;
use BusyPHP\traits\ContainerInstance;
use think\db\exception\DbException;
use Throwable;

/**
 * 后台系统消息类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/18 下午12:18 下午 Message.php $
 */
class Message implements ContainerInterface
{
    use ContainerDefine;
    use ContainerInstance;
    
    
    protected App $app;
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    public function __construct(App $app)
    {
        $this->app = $app;
    }
    
    
    /**
     * 获取未读消息总数
     * @param AdminUserField $user
     * @return int
     * @throws DbException
     */
    public function getUnreadTotal(AdminUserField $user) : int
    {
        return AdminMessage::init()->getUnreadTotal(AdminMessage::USER_TYPE_DEFAULT, $user->id);
    }
    
    
    /**
     * 设为已读
     * @param AdminUserField $user
     * @param string         $id
     * @throws Throwable
     */
    public function setRead(AdminUserField $user, string $id)
    {
        AdminMessage::init()->setRead(AdminMessage::USER_TYPE_DEFAULT, $user->id, (int) $id);
    }
    
    
    /**
     * 全部已读
     * @param AdminUserField $user
     * @throws Throwable
     */
    public function setAllRead(AdminUserField $user)
    {
        AdminMessage::init()->setAllRead(AdminMessage::USER_TYPE_DEFAULT, $user->id);
    }
    
    
    /**
     * 删除消息
     * @param AdminUserField $user
     * @param string         $id
     * @throws Throwable
     */
    public function delete(AdminUserField $user, string $id)
    {
        AdminMessage::init()
            ->whereUserId($user->id)
            ->whereUserType(AdminMessage::USER_TYPE_DEFAULT)
            ->delete($id);
    }
    
    
    /**
     * 清空消息
     * @param AdminUserField $user
     * @throws Throwable
     */
    public function clear(AdminUserField $user)
    {
        AdminMessage::init()->clear(AdminMessage::USER_TYPE_DEFAULT, $user->id);
    }
    
    
    /**
     * 获取通知列表
     * @param AdminUserField $user 管理员信息
     * @param int            $page 分页码
     * @return AdminMessageField[]
     * @throws Throwable
     */
    public function getList(AdminUserField $user, int $page, int $type = 0) : array
    {
        $model = AdminMessage::init();
        if ($type == 1) {
            $model->where(AdminMessageField::read(0));
        } elseif ($type == 2) {
            $model->where(AdminMessageField::read('>', 0));
        }
        
        return $model
            ->whereUserId($user->id)
            ->whereUserType(AdminMessage::USER_TYPE_DEFAULT)
            ->order(AdminMessageField::id(), 'desc')
            ->page(max($page, 1), 10)
            ->selectList();
    }
    
    
    /**
     * 是否已启用通知
     * @return bool
     */
    public function isEnable() : bool
    {
        return (bool) $this->app->config->get('app.admin.message.enable', false);
    }
}