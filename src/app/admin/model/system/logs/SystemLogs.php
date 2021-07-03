<?php

namespace BusyPHP\app\admin\model\system\logs;

use BusyPHP\app\admin\model\system\file\SystemFileInfo;
use BusyPHP\model;
use Exception;
use think\db\exception\DbException;

/**
 * 系统操作记录模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午4:59 SystemLogs.php $
 * @method SystemLogsInfo findInfo($data = null, $notFoundMessage = null)
 * @method SystemLogsInfo getInfo($data, $notFoundMessage = null)
 * @method SystemLogsInfo[] selectList()
 */
class SystemLogs extends Model
{
    /** @var int 其它操作 */
    const TYPE_DEFAULT = 0;
    
    /** @var int 添加操作 */
    const TYPE_INSERT = 1;
    
    /** @var int 更新操作 */
    const TYPE_UPDATE = 2;
    
    /** @var int 删除操作 */
    const TYPE_DELETE = 3;
    
    /** @var int 设置操作 */
    const TYPE_SET = 4;
    
    /** @var int 批量操作 */
    const TYPE_BATCH = 5;
    
    protected $bindParseClass = SystemLogsInfo::class;
    
    /**
     * 操作用户ID
     * @var int
     */
    protected $userId = 0;
    
    /**
     * 操作用户名
     * @var string
     */
    protected $username = '';
    
    
    /**
     * 设置操作用户
     * @param int    $userId
     * @param string $username
     * @return $this
     */
    public function setUser($userId, $username)
    {
        $this->userId   = floatval($userId);
        $this->username = trim($username);
        
        return $this;
    }
    
    
    /**
     * 添加LOG
     * @param string $name 操作名称
     * @param mixed  $value 操作内容
     * @param int    $type 操作类型
     * @return false|int
     */
    public function insertData($name, $value, $type = self::TYPE_DEFAULT)
    {
        try {
            $request = request();
            $appName = app()->http->getName();
            $isCli   = $request->isCli();
            
            $data             = SystemLogsField::init();
            $data->createTime = time();
            $data->title      = $name;
            $data->type       = $type;
            $data->content    = serialize($value);
            $data->path       = $request->root();
            $data->username   = $isCli ? 'CLI' : $this->username;
            $data->userid     = $this->userId;
            $data->isAdmin    = $appName === 'admin';
            $data->appName    = $appName;
            $data->ip         = $isCli ? '0.0.0.0' : $request->ip();
            $data->ua         = $isCli ? 'CLI' : $request->server('http_user_agent');
            $data->url        = $isCli ? '' : (($request->isPost() ? 'POST:' : 'GET:') . $request->url());
            
            return $this->addData($data);
        } catch (Exception $e) {
            return false;
        }
    }
    
    
    /**
     * 清空操作记录
     * @return int
     * @throws DbException
     */
    public function clear()
    {
        $time = strtotime('-6 month');
        
        return $this->whereEntity(SystemLogsField::createTime('<=', $time))->delete();
    }
    
    
    /**
     * 获取操作类型
     * @param int $var
     * @return array|string
     */
    public static function getTypes($var = null)
    {
        return self::parseVars(self::parseConst(self::class, 'TYPE_', [], function($item) {
            return $item['name'];
        }), $var);
    }
}