<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\lock;

use BusyPHP\helper\CacheHelper;
use BusyPHP\Model;
use InvalidArgumentException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use Throwable;

/**
 * 系统锁模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/29 下午下午4:19 SystemLock.php $
 * @method static string|SystemLock getClass()
 */
class SystemLock extends Model
{
    /**
     * @inheritDoc
     */
    protected static function defineClass() : string
    {
        return self::class;
    }
    
    
    /**
     * 执行锁
     * @param string      $id 锁ID
     * @param callable    $callback 回调
     * @param string|null $remark 锁备注
     * @param bool        $disabledTrans 是否禁用事物
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    public function do(string $id, callable $callback, string $remark = null, $disabledTrans = false)
    {
        if ($id === '') {
            throw new InvalidArgumentException('锁ID不能为空');
        }
        
        CacheHelper::remember(self::class, $id, function() use ($id, $remark) {
            $this->create($id, $remark);
            
            return $id;
        }, 0);
        
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
        } catch (Throwable $e) {
            $this->rollback($disabledTrans);
            
            throw $e;
        }
    }
    
    
    /**
     * 创建锁
     * @param string      $id 锁ID
     * @param string|null $remark 锁说明
     * @throws DbException
     */
    protected function create(string $id, string $remark = null)
    {
        $this->replace(true)->addData(['id' => $id, 'remark' => $remark ?: $id]);
    }
}