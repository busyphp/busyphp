<?php

namespace BusyPHP\app\admin\model\system\lock;

use BusyPHP\Model;
use Closure;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 系统锁模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/29 下午下午4:19 SystemLock.php $
 */
class SystemLock extends Model
{
    /**
     * 执行锁
     * @param string  $id 锁ID
     * @param Closure $callback 回调
     * @param string  $remark 锁备注
     * @param bool    $disabledTrans 是否禁用事物
     * @return mixed
     * @throws Exception
     */
    public function do(string $id, Closure $callback, ?string $remark = null, $disabledTrans = false)
    {
        $id       = trim($id);
        $isCreate = $this->getCache($id);
        if (!$isCreate) {
            $this->create($id, $remark);
        }
        
        $this->startTrans($disabledTrans);
        try {
            try {
                $this->lock(true)->getInfo($id);
            } catch (DataNotFoundException $e) {
                $this->create($id, $remark);
                $this->lock(true)->getInfo($id);
            }
            
            $result = call_user_func($callback);
            
            $this->commit($disabledTrans);
            
            return $result;
        } catch (Exception $e) {
            $this->rollback($disabledTrans);
            
            throw $e;
        }
    }
    
    
    /**
     * 创建锁
     * @param string $id 锁ID
     * @param string $remark 锁说明
     * @throws DbException
     */
    protected function create(string $id, ?string $remark = '')
    {
        $this->addData([
            'id'     => $id,
            'remark' => (string) $remark
        ], true);
        $this->setCache($id, time());
    }
}