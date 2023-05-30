<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\token;

use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\helper\TransHelper;
use BusyPHP\helper\TripleDesHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use LogicException;
use RuntimeException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\facade\Config;
use think\facade\Request;

/**
 * 系统用户通行秘钥模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/5/30 13:48 SystemToken.php $
 * @method SystemTokenField getInfo(string $id, string $notFoundMessage = null)
 * @method SystemTokenField|null findInfo(string $id = null)
 * @method SystemTokenField[] selectList()
 * @method SystemTokenField[] indexList(string|Entity $key = '')
 * @method SystemTokenField[] indexListIn(array $range, string|Entity $key = '', string|Entity $field = '')
 */
class SystemToken extends Model implements ContainerInterface
{
    protected string $fieldClass          = SystemTokenField::class;
    
    protected string $dataNotFoundMessage = '记录不存在';
    
    /** @var int 网页端 */
    public const DEFAULT_TYPE = 1;
    
    /** @var int 系统用户 */
    public const DEFAULT_USER_TYPE = 1;
    
    /** @var array 系统配置 */
    protected array $config;
    
    
    public function __construct(string $connect = '', bool $force = false)
    {
        $this->config = (array) (Config::get('admin.model.system_token') ?: []);
        
        parent::__construct($connect, $force);
    }
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * 获取登录类型
     * @param int|null $type
     * @return array|string
     */
    public static function getTypeMap(int $type = null) : array|string
    {
        static $map;
        if (!isset($map)) {
            $map = [static::DEFAULT_TYPE => '网页端'];
            $map = (array) Config::get('admin.model.system_token.type_map', []) + $map;
        }
        
        return ArrayHelper::getValueOrSelf($map, $type);
    }
    
    
    /**
     * 获取用户类型
     * @param int|null $userType
     * @return array|string
     */
    public static function getUserTypeMap(int $userType = null) : array|string
    {
        static $map;
        if (!isset($map)) {
            $map = [static::DEFAULT_USER_TYPE => '系统用户'];
            $map = (array) Config::get('admin.model.system_token.user_type_map', []) + $map;
        }
        
        return ArrayHelper::getValueOrSelf($map, $userType);
    }
    
    
    /**
     * 生成记录ID
     * @param int $type
     * @param int $userType
     * @param int $userId
     * @return string
     */
    public static function createId(int $type, int $userType, int $userId) : string
    {
        return md5(implode(',', [$type, $userType, $userId]));
    }
    
    
    /**
     * 更新登录密钥
     * @param int    $type 登录类型
     * @param int    $userType 用户类型
     * @param int    $userId 用户ID
     * @param string $token 自定义token
     * @return SystemTokenField
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function updateToken(int $type, int $userType, int $userId, string $token = '') : SystemTokenField
    {
        $data = SystemTokenField::init();
        $data->setId(static::createId($type, $userType, $userId));
        $data->setToken($token ?: StringHelper::random(32));
        
        if ($this->find($data->id)) {
            $data->setLastTime(SystemTokenField::loginTime());
            $data->setLastIp(SystemTokenField::loginIp());
            $data->setLoginTotal(SystemTokenField::loginTotal('+', 1));
            $data->setLoginIp(Request::ip());
            $data->setLoginTime(time());
            $this->update($data);
        } else {
            $data->setType($type);
            $data->setUserId($userId);
            $data->setUserType($userType);
            $data->setCreateTime(time());
            $data->setLoginTotal(1);
            $data->setLoginIp(Request::ip());
            $data->setLoginTime(time());
            $this->insert($data);
        }
        
        return $this->getInfo($data->id);
    }
    
    
    /**
     * 创建通行认证密钥
     * @param string           $secret 加密秘钥
     * @param SystemTokenField $info 用户通行秘钥数据
     * @param int              $expire 过期时长(秒)
     * @param string           $extend 扩展数据
     * @return string
     */
    public static function encode(string $secret, SystemTokenField $info, int $expire = 0, string $extend = '') : string
    {
        return TransHelper::base64encodeUrl(TripleDesHelper::encrypt(
            sprintf("%s,%s,%s,%s,%s,%s,%s", $info->userType, $info->userId, $info->type, $info->token, time(), $expire, $extend),
            $secret
        ));
    }
    
    
    /**
     * 解密通行认证密钥
     * @param string $secret 解码秘钥
     * @param string $authKey 通行秘钥
     * @return array{info: SystemTokenField, extend: string}
     * @throws DataNotFoundException
     * @throws DbException
     */
    public static function check(string $secret, string $authKey) : array
    {
        $arr      = explode(',', TripleDesHelper::decrypt(TransHelper::base64decodeUrl($authKey), $secret));
        $userType = (int) ($arr[0] ?? 0);
        $userId   = (int) ($arr[1] ?? 0);
        $type     = (int) ($arr[2] ?? 0);
        $token    = trim($arr[3] ?? '');
        $time     = (int) ($arr[4] ?? 0);
        $expire   = (int) ($arr[5] ?? 0);
        $extend   = trim($arr[6] ?? '');
        
        if ($userId < 1) {
            throw new RuntimeException('token不正确');
        }
        
        if ($expire > 0 && $time < time() - $expire) {
            throw new RuntimeException('token已过期');
        }
        
        $id   = static::createId($type, $userType, $userId);
        $info = static::init()->getInfo($id);
        if ($info->token !== $token) {
            throw new LogicException('token不匹配');
        }
        
        return [
            'info'   => $info,
            'extend' => $extend
        ];
    }
}