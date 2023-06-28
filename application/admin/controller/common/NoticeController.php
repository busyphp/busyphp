<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\component\notice\Message;
use BusyPHP\app\admin\component\notice\Todo;
use BusyPHP\app\admin\controller\InsideController;
use think\Collection;
use think\db\exception\DbException;
use think\Response;
use Throwable;

/**
 * 通知相关
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/6/26 21:35 NoticeController.php $
 */
class NoticeController extends InsideController
{
    /**
     * 统计
     * @return Response
     * @throws DbException
     */
    public function total() : Response
    {
        $this->ignoreOperate();
        
        $todoTotal    = 0;
        $messageTotal = 0;
        $todo         = Todo::instance();
        $notice       = Message::instance();
        if ($todo->isEnable()) {
            $todoTotal = $todo->getTotal($this->adminUser);
        }
        
        if ($notice->isEnable()) {
            $messageTotal = $notice->getUnreadTotal($this->adminUser);
        }
        
        return $this->success([
            'message_total' => $messageTotal,
            'todo_total'    => $todoTotal,
            'total'         => $messageTotal + $todoTotal
        ]);
    }
    
    
    /**
     * 系统消息
     * @throws Throwable
     */
    public function message() : Response
    {
        if ($this->isPost()) {
            $notice = Message::instance();
            switch ($this->post('action/s', 'trim')) {
                // 读取
                case 'read':
                    $notice->setRead($this->adminUser, $this->post('id/s', 'trim'));
                    $this->log()->record(self::LOG_UPDATE, '阅读消息');
                    
                    return $this->success();
                
                // 全部已读
                case 'all_read':
                    $notice->setAllRead($this->adminUser);
                    $this->log()->record(self::LOG_UPDATE, '将系统消息标记为全部已读');
                    
                    return $this->success('操作成功');
                
                // 删除通知
                case 'delete':
                    $notice->delete($this->adminUser, $this->post('id/s', 'trim'));
                    $this->log()->record(self::LOG_DELETE, '删除系统消息');
                    
                    return $this->success('删除成功');
                
                // 清空通知
                case 'clear':
                    $notice->clear($this->adminUser);
                    $this->log()->record(self::LOG_DELETE, '清空系统消息');
                    
                    return $this->success('清空完成');
                default:
                    return $this->success([
                        'list' => $notice->getList(
                            $this->adminUser,
                            $this->post('page/d'),
                            $this->post('type/d')
                        )
                    ]);
            }
        }
        
        $this->assignNoticeData();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 赋值通知模版数据
     */
    protected function assignNoticeData()
    {
        $this->setPageTitle('消息');
    }
    
    
    /**
     * 待办
     * @return Response
     */
    public function todo() : Response
    {
        if ($this->isPost()) {
            $todo = Todo::instance();
            switch ($this->post('action/s', 'trim')) {
                // 读取
                case 'read':
                    $todo->setRead($this->adminUser, $this->post('id/s', 'trim'));
                    $this->log()->record(self::LOG_UPDATE, '处理待办');
                    
                    return $this->success();
                default:
                    $level  = $this->post('level/d');
                    $list   = $todo->getList($this->adminUser);
                    $levels = array_column($list, 'level');
                    $sorts  = array_column($list, 'sort');
                    array_multisort($levels, SORT_ASC, $sorts, SORT_ASC, $list);
                    
                    if ($level > 0) {
                        $list = Collection::make($list)->where('level', $level)->values();
                    }
                    
                    return $this->success([
                        'list' => $list
                    ]);
            }
        }
        
        $this->assignTodoData();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 赋值待办模版数据
     */
    protected function assignTodoData()
    {
        $this->setPageTitle('待办');
        
        $liveMap = [['name' => '全部', 'level' => 0, 'active' => true]];
        foreach (Todo::getLevelMap() as $level => $item) {
            $liveMap[] = [
                'name'   => $item['name'],
                'level'  => $level,
                'active' => false
            ];
        }
        $this->assign('level_map', $liveMap);
    }
}