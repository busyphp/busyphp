<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\message;

use BusyPHP\helper\ArrayHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use think\db\exception\DbException;
use think\facade\Config;

/**
 * 后台消息模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/18 下午10:56 上午 AdminMessage.php $
 * @method AdminMessageField getInfo(int $id, string $notFoundMessage = null)
 * @method AdminMessageField|null findInfo(int $id = null)
 * @method AdminMessageField[] selectList()
 * @method AdminMessageField[] indexList(string|Entity $key = '')
 * @method AdminMessageField[] indexListIn(array $range, string|Entity $key = '', string|Entity $field = '')
 * @method $this whereType(int $type)
 * @method $this whereUserType(int $userType)
 * @method $this whereUserId(int $userId)
 */
class AdminMessage extends Model implements ContainerInterface
{
    /** @var int 系统用户 */
    public const USER_TYPE_DEFAULT = 1;
    
    protected string $dataNotFoundMessage = '消息不存在';
    
    protected string $listNotFoundMessage = '暂无消息';
    
    protected string $fieldClass          = AdminMessageField::class;
    
    protected array  $config;
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * 获取类型映射
     * @param int|null $type
     * @return null|array{name:string}|array<int,array{name:string}>
     */
    public static function getTypeMap(int $type = null) : ?array
    {
        static $map;
        if (!isset($map)) {
            $map = (array) Config::get('admin.model.admin_message.type_map', []);
        }
        
        return ArrayHelper::getValueOrSelf($map, $type);
    }
    
    
    /**
     * 获取类型名称映射
     * @param int|null $type
     * @return string|array|null
     */
    public static function getTypeNameMap(int $type = null) : string|array|null
    {
        static $map;
        
        if (!isset($map)) {
            $map = [];
            foreach (static::getTypeMap() as $type => $item) {
                $map[$type] = (string) $item['name'];
            }
        }
        
        return ArrayHelper::getValueOrSelf($map, $type);
    }
    
    
    public function __construct(string $connect = '', bool $force = false)
    {
        $this->config = (array) (Config::get('admin.model.admin_message') ?: []);
        
        parent::__construct($connect, $force);
    }
    
    
    /**
     * 创建消息
     * @param AdminMessageField $data
     * @return int
     * @throws DbException
     */
    public function create(AdminMessageField $data) : int
    {
        return (int) $this->validate($data, self::SCENE_CREATE)->insert();
    }
    
    
    /**
     * 标记消息为已读
     * @param int $userType 用户类型
     * @param int $userId 用户ID
     * @param int $id 消息ID
     * @throws DbException
     */
    public function setRead(int $userType, int $userId, int $id)
    {
        $data = AdminMessageField::init();
        $data->setRead(true);
        $data->setReadTime(time());
        
        $this->whereUserId($userId)
            ->whereUserType($userType)
            ->where(AdminMessageField::id($id))
            ->update($data);
    }
    
    
    /**
     * 清空消息
     * @param int $userType 用户类型
     * @param int $userId 用户ID
     * @throws DbException
     */
    public function clear(int $userType, int $userId)
    {
        $this->whereUserId($userId)
            ->whereUserType($userType)
            ->delete();
    }
    
    
    /**
     * 全部设为已读
     * @param int $userType 用户类型
     * @param int $userId 用户ID
     * @throws DbException
     */
    public function setAllRead(int $userType, int $userId)
    {
        $data = AdminMessageField::init();
        $data->setRead(true);
        $data->setReadTime(time());
        $this->whereUserId($userId)
            ->whereUserType($userType)
            ->where(AdminMessageField::read(0))
            ->update($data);
    }
    
    
    /**
     * 获取未读数
     * @param int $userType 用户类型
     * @param int $userId 用户ID
     * @return int
     * @throws DbException
     */
    public function getUnreadTotal(int $userType, int $userId) : int
    {
        return $this->whereUserType($userType)
            ->whereUserId($userId)
            ->where(AdminMessageField::read(0))
            ->count();
    }
}