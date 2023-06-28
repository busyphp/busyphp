<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\notice\data;

use BusyPHP\app\admin\model\admin\message\AdminMessageField;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\ClassHelper;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\Field;
use Closure;
use think\facade\Config;

/**
 * 操作方法结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/6/26 22:18 Operate.php $
 * @method $this setType(int $type) 设置操作类型
 * @method $this setValue(string $value) 设置操作值
 */
#[ToArrayFormat(type: ToArrayFormat::TYPE_SNAKE)]
class Operate extends Field
{
    /** @var int 无操作 */
    public const TYPE_NONE = 0;
    
    /** @var int 打开路由页面 */
    public const TYPE_ROUTE = 1;
    
    /** @var int 打开URL */
    public const TYPE_WEBVIEW = 2;
    
    /** @var int 打开浏览器 */
    public const TYPE_BROWSER = 3;
    
    
    /**
     * 获取操作类型
     * @param int $type
     * @return array|string|null
     */
    public static function getTypeMap(int $type) : array|string|null
    {
        return ArrayHelper::getValueOrSelf(ClassHelper::getConstAttrs(self::class, 'TYPE_', ClassHelper::ATTR_NAME), $type);
    }
    
    
    /**
     * 操作类型
     * @var int
     */
    public $type = self::TYPE_NONE;
    
    /**
     * 操作值
     * @var string
     */
    public $value = '';
    
    /**
     * 后台操作
     * @var AdminOperate
     */
    public $admin;
    
    
    /**
     * 解析后台操作
     * @param AdminMessageField $message
     */
    public function parseAdmin(AdminMessageField $message)
    {
        static $call;
        if (!isset($call)) {
            $call = Config::get('app.admin.message.operate', false);
        }
        
        if ($this->admin) {
            return;
        }
        
        $this->admin = AdminOperate::init()->setType($this->type)->setValue($this->value);
        if ($call instanceof Closure) {
            $call($message, $this->admin);
        }
    }
    
    
    /**
     * 构建
     * @param int    $type 操作类型
     * @param string $value 操作值
     * @return static
     */
    public static function build(int $type = self::TYPE_NONE, string $value = '') : static
    {
        $obj        = static::init();
        $obj->type  = $type;
        $obj->value = $value;
        
        return $obj;
    }
}