<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace BusyPHP;

use think\db\ConnectionInterface;

/**
 * Db
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/16 下午8:39 上午 BaseDb.php $
 */
class Db extends \think\Db
{
    /**
     * 获取连接池
     * @param string $name 连接标识
     * @param bool   $force 强制重新连接
     * @return ConnectionInterface
     */
    public function instance(string $name = null, bool $force = false) : ConnectionInterface
    {
        return parent::instance($name, $force);
    }
}
