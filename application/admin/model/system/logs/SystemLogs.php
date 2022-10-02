<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\logs;

use BusyPHP\App;
use BusyPHP\helper\AppHelper;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\ClassHelper;
use BusyPHP\model;
use BusyPHP\Service;
use Exception;
use think\db\exception\DbException;

/**
 * 系统操作记录模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午4:59 SystemLogs.php $
 * @method SystemLogsInfo findInfo($data = null, $notFoundMessage = null)
 * @method SystemLogsInfo getInfo($data, $notFoundMessage = null)
 * @method SystemLogsInfo[] selectList()
 * @method SystemLogsInfo[] buildListWithField(array $values, $key = null, $field = null) : array
 */
class SystemLogs extends Model
{
    /** @var int 默认操作 */
    const TYPE_DEFAULT = 0;
    
    /** @var int 添加操作 */
    const TYPE_INSERT = 1;
    
    /** @var int 更新操作 */
    const TYPE_UPDATE = 2;
    
    /** @var int 删除操作 */
    const TYPE_DELETE = 3;
    
    protected $bindParseClass = SystemLogsInfo::class;
    
    
    /**
     * 设置操作用户
     * @param int    $userId 用户ID
     * @param string $username 用户名
     * @return $this
     */
    public function setUser(int $userId, $username = '') : self
    {
        $this->setOption('logs_user_id', $userId);
        $this->setOption('logs_username', trim($username));
        
        return $this;
    }
    
    
    /**
     * 设置业务类型以及业务参数
     * @param string|int $type 业务类型
     * @param string     $value 业务参数
     * @return $this
     */
    public function setClass($type, $value = '') : self
    {
        $this->setOption('logs_class_type', $type);
        $this->setOption('logs_class_value', trim($value));
        
        return $this;
    }
    
    
    /**
     * 设置过滤的参数键
     * @param array $keys
     * @return $this
     */
    public function filterParams(array $keys = []) : self
    {
        $this->setOption('logs_params_keys', $keys);
        
        return $this;
    }
    
    
    /**
     * 记录日志
     * @param int    $type 操作类型
     * @param string $name 操作名称
     * @param string $result 操作结果
     * @return int|false
     */
    public function record(int $type, string $name, string $result = '')
    {
        try {
            $app        = App::getInstance();
            $request    = $app->request;
            $isCli      = $app->runningInConsole();
            $filterKeys = array_merge($this->getOptions('logs_params_keys') ?: [], [
                Service::ROUTE_VAR_DIR,
                Service::ROUTE_VAR_CONTROL,
                Service::ROUTE_VAR_ACTION,
                Service::ROUTE_VAR_GROUP,
                Service::ROUTE_VAR_TYPE
            ]);
            $params     = [];
            foreach ($request->param() ?: [] as $key => $value) {
                if (in_array($key, $filterKeys)) {
                    continue;
                }
                $params[$key] = $value;
            }
            
            $insert             = SystemLogsField::init();
            $insert->createTime = time();
            $insert->name       = $name;
            $insert->method     = $request->method() ?: '';
            $insert->type       = $type;
            $insert->username   = $this->getOptions('logs_username') ?: '';
            $insert->userId     = $this->getOptions('logs_user_id') ?: 0;
            $insert->classType  = $this->getOptions('logs_class_type') ?: '';
            $insert->classValue = $this->getOptions('logs_class_value') ?: '';
            $insert->client     = $isCli ? AppHelper::CLI_CLIENT_KEY : $app->getDirName();
            $insert->ip         = $isCli ? '' : ($request->ip() ?: '');
            $insert->url        = $isCli ? '' : ($request->url() ?: '');
            $insert->headers    = json_encode($request->header() ?: [], JSON_UNESCAPED_UNICODE);
            $insert->params     = json_encode($params, JSON_UNESCAPED_UNICODE);
            $insert->result     = trim($result);
            
            return $this->addData($insert);
        } catch (Exception $e) {
            return false;
        }
    }
    
    
    /**
     * 清空操作记录
     * @return int
     * @throws DbException
     */
    public function clear() : int
    {
        $time = strtotime('-6 month');
        
        return $this->whereEntity(SystemLogsField::createTime('<=', $time))->delete();
    }
    
    
    /**
     * 查询日志分类
     * @param string|int $type
     * @param string     $value
     * @return $this
     */
    public function whereClass($type, string $value = '') : self
    {
        $this->whereEntity(SystemLogsField::classType($type));
        if ($value !== '') {
            $this->whereEntity(SystemLogsField::classValue($value));
        }
        
        return $this;
    }
    
    
    /**
     * 获取操作类型
     * @param int $var
     * @return array|string
     */
    public static function getTypes($var = null)
    {
        return ArrayHelper::getValueOrSelf(ClassHelper::getConstMap(self::class, 'TYPE_', ClassHelper::ATTR_NAME), $var);
    }
}