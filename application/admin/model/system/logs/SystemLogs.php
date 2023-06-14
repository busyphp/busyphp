<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\logs;

use BusyPHP\App;
use BusyPHP\helper\AppHelper;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\ClassHelper;
use BusyPHP\helper\LogHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use BusyPHP\Service;
use Exception;
use think\db\exception\DbException;
use Throwable;

/**
 * 系统操作记录模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午4:59 SystemLogs.php $
 * @method SystemLogsField getInfo(int $id, string $notFoundMessage = null)
 * @method SystemLogsField|null findInfo(int $id = null)
 * @method SystemLogsField[] selectList()
 * @method SystemLogsField[] indexList(string|Entity $key = '')
 * @method SystemLogsField[] indexListIn(array $range, string|Entity $key = '', string|Entity $field = '')
 */
class SystemLogs extends Model implements ContainerInterface
{
    /** @var int 默认操作 */
    const TYPE_DEFAULT = 0;
    
    /** @var int 添加操作 */
    const TYPE_INSERT = 1;
    
    /** @var int 更新操作 */
    const TYPE_UPDATE = 2;
    
    /** @var int 删除操作 */
    const TYPE_DELETE = 3;
    
    protected string $fieldClass = SystemLogsField::class;
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * 设置操作用户
     * @param int    $userId 用户ID
     * @param string $username 用户名
     * @return static
     */
    public function setUser(int $userId, string $username = '') : static
    {
        $this->setOption('logs_user_id', $userId);
        $this->setOption('logs_username', $username);
        
        return $this;
    }
    
    
    /**
     * 设置业务类型以及业务参数
     * @param string|int $type 业务类型
     * @param string     $value 业务参数
     * @return static
     */
    public function setClass(string|int $type, string $value = '') : static
    {
        $this->setOption('logs_class_type', $type);
        $this->setOption('logs_class_value', $value);
        
        return $this;
    }
    
    
    /**
     * 设置过滤的参数键
     * @param array $keys
     * @return static
     */
    public function filterParams(array $keys = []) : static
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
    public function record(int $type, string $name, string $result = '') : false|int
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
            
            $data = SystemLogsField::init();
            $data->setCreateTime(time());
            $data->setName($name);
            $data->setMethod($request->method() ?: '');
            $data->setType($type);
            $data->setUsername($this->getOptions('logs_username') ?: '');
            $data->setUserId($this->getOptions('logs_user_id') ?: 0);
            $data->setClassType($this->getOptions('logs_class_type') ?: '');
            $data->setClassValue($this->getOptions('logs_class_value') ?: '');
            $data->setClient($isCli ? AppHelper::CLI_CLIENT_KEY : $app->getDirName());
            $data->setIp($isCli ? '' : ($request->ip() ?: ''));
            $data->setUrl($isCli ? '' : ($request->url() ?: ''));
            $data->setHeaders($request->header() ?: []);
            $data->setParams($params);
            $data->setResult($result);
            
            return (int) $this->insert($data);
        } catch (Throwable $e) {
            LogHelper::default()->tag('记录操作日志失败', __METHOD__)->error($e);
            
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
        
        return $this->where(SystemLogsField::createTime('<=', $time))->delete();
    }
    
    
    /**
     * 查询日志分类
     * @param string|int $type
     * @param string     $value
     * @return static
     */
    public function whereClass(string|int $type, string $value = '') : static
    {
        $this->where(SystemLogsField::classType((string) $type));
        if ($value !== '') {
            $this->where(SystemLogsField::classValue($value));
        }
        
        return $this;
    }
    
    
    /**
     * 获取操作类型
     * @param int $var
     * @return array|string|null
     */
    public static function getTypeMap($var = null) : array|string|null
    {
        return ArrayHelper::getValueOrSelf(ClassHelper::getConstAttrs(self::class, 'TYPE_', ClassHelper::ATTR_NAME), $var);
    }
}