<?php

namespace BusyPHP\app\admin\model\system\logs;

use BusyPHP\exception\VerifyException;
use BusyPHP\exception\SQLException;
use BusyPHP\model;
use BusyPHP\helper\util\Transform;

/**
 * 系统操作记录模型
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2017 busy^life <busy.life@qq.com>
 * @version $Id: 2017-06-04 下午10:28 SystemLogs.php busy^life $
 */
class SystemLogs extends Model
{
    /** 默认 */
    const TYPE_DEFAULT = 0;
    
    /** 添加 */
    const TYPE_INSERT = 1;
    
    /** 更新 */
    const TYPE_UPDATE = 2;
    
    /** 删除操作 */
    const TYPE_DELETE = 3;
    
    /** 设置操作 */
    const TYPE_SET = 4;
    
    /** 批量操作 */
    const TYPE_BATCH = 5;
    
    /** @var int 操作用户ID */
    protected $userId = 0;
    
    /** @var string 操作用户名 */
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
            $request  = request();
            $appName  = $this->app->http->getName();
            $isCli    = $request->isCli();
            $insertId = parent::addData([
                'create_time' => time(),
                'title'       => $name,
                'type'        => $type,
                'content'     => serialize($value),
                'path'        => $request->root(),
                'username'    => $isCli ? 'CLI' : $this->username,
                'userid'      => $this->userId,
                'is_admin'    => $appName === 'admin',
                'app_name'    => $appName,
                'ip'          => $isCli ? '0.0.0.0' : $request->ip(),
                'ua'          => $isCli ? 'CLI' : $_SERVER['HTTP_USER_AGENT'],
                'url'         => $isCli ? '' : (($request->isPost() ? 'POST:' : 'GET:') . $request->url())
            ]);
            if (!$insertId) {
                throw new SQLException('添加日志失败', $this);
            }
            
            return $insertId;
        } catch (SQLException $e) {
            return false;
        }
    }
    
    
    /**
     * 清空操作记录
     * @return int
     * @throws SQLException
     */
    public function clear()
    {
        $time = strtotime('-3 month');
        $res  = $this->whereof(['create_time' => ['elt', $time]])->deleteData();
        if ($res === false) {
            throw new SQLException('清理失败', $this);
        }
        
        return $res;
    }
    
    
    /**
     * 获取操作类型
     * @param null|int $var
     * @return array|string
     */
    public static function getTypes($var = null)
    {
        return self::parseVars([
            self::TYPE_INSERT  => '添加操作',
            self::TYPE_UPDATE  => '更新操作',
            self::TYPE_DELETE  => '删除操作',
            self::TYPE_SET     => '设置操作',
            self::TYPE_BATCH   => '处理操作',
            self::TYPE_DEFAULT => '其它操作',
        ], $var);
    }
    
    
    /**
     * 解析数据
     * @param array $list
     * @return array
     */
    public static function parseList($list)
    {
        return parent::parseList($list, function($list) {
            foreach ($list as $i => $r) {
                $r['format_create_time'] = Transform::date($r['create_time']);
                $r['type_name']          = self::getTypes(intval($r['type']));
                $r['content']            = unserialize($r['content']);
                $r['is_admin']           = $r['is_admin'] > 0;
                $list[$i]                = $r;
            }
            
            return $list;
        });
    }
}