<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\component\message;

use BusyPHP\App;
use BusyPHP\app\admin\component\message\notice\NoticeNode;
use BusyPHP\app\admin\model\admin\message\AdminMessage;
use BusyPHP\app\admin\model\admin\message\AdminMessageField;
use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\helper\TransHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\traits\ContainerDefine;
use BusyPHP\traits\ContainerInstance;
use Throwable;

/**
 * 后台通知类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/18 下午12:18 下午 Notice.php $
 */
class Notice implements ContainerInterface
{
    use ContainerDefine;
    use ContainerInstance;
    
    /**
     * @var App
     */
    protected $app;
    
    
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
     * @param AdminUserField $AdminUserField
     * @return int
     */
    public function getUnreadTotal(AdminUserField $AdminUserField) : int
    {
        return AdminMessage::init()
            ->where(AdminMessageField::userId($AdminUserField->id))
            ->where(AdminMessageField::read(0))
            ->count();
    }
    
    
    /**
     * 设为已读
     * @param AdminUserField $AdminUserField
     * @param string        $id
     * @throws Throwable
     */
    public function setRead(AdminUserField $AdminUserField, string $id)
    {
        AdminMessage::init()->setRead($AdminUserField->id, (int) $id);
    }
    
    
    /**
     * 全部已读
     * @param AdminUserField $AdminUserField
     * @throws Throwable
     */
    public function setAllRead(AdminUserField $AdminUserField)
    {
        AdminMessage::init()->setAllRead($AdminUserField->id);
    }
    
    
    /**
     * 删除消息
     * @param AdminUserField $AdminUserField
     * @param string        $id
     * @throws Throwable
     */
    public function delete(AdminUserField $AdminUserField, string $id)
    {
        AdminMessage::init()
            ->where(AdminMessageField::userId($AdminUserField->id))
            ->remove($id);
    }
    
    
    /**
     * 清空消息
     * @param AdminUserField $AdminUserField
     * @throws Throwable
     */
    public function clear(AdminUserField $AdminUserField)
    {
        AdminMessage::init()->clear($AdminUserField->id);
    }
    
    
    /**
     * 获取通知列表
     * @param AdminUserField $AdminUserField 管理员信息
     * @param int           $page 分页码
     * @return NoticeNode[]
     * @throws Throwable
     */
    public function getList(AdminUserField $AdminUserField, int $page) : array
    {
        $size = 20;
        $page = max($page, 1);
        $data = AdminMessage::init()
            ->where(AdminMessageField::userId($AdminUserField->id))
            ->order(AdminMessageField::id(), 'desc')
            ->page($page, $size)
            ->selectList();
        
        $list = [];
        foreach ($data as $info) {
            $item = new NoticeNode();
            $item->setId($info->id);
            $item->setRead($info->read);
            $item->setCreateTime(TransHelper::date($info->createTime));
            $item->setTitle($info->content);
            $item->setDesc($info->description);
            $item->setIcon($info->icon);
            $item->setIconColor($info->iconColor);
            $item->setUrl($info->url);
            $item->setAttrs($info->attrs);
            $list[] = $item;
        }
        
        return $list;
    }
    
    
    /**
     * 是否已启用通知
     * @return bool
     */
    public function isEnable() : bool
    {
        return (bool) $this->app->config->get('app.admin.notice', false);
    }
}