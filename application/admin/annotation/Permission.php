<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\annotation;

use Attribute;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use ReflectionClass;
use ReflectionException;

/**
 * 非AdminController权限检测注解
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/6/23 11:09 Permission.php $
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Permission
{
    /**
     * @var array|string
     */
    private $node;
    
    
    /**
     * 构造函数
     * @param callable|array|string $node 后台控制器类名及方法，实例：DemoController::add 或 [DemoController::class, 'add']
     */
    public function __construct(mixed $node)
    {
        $this->node = $node;
    }
    
    
    /**
     * 判断是否拥有权限
     * @param AdminUserField $user 系统用户数据
     * @return bool
     */
    public function has(AdminUserField $user) : bool
    {
        $node = $this->node;
        if (is_array($this->node)) {
            $node = implode('::', $this->node);
        }
        
        
        $map  = SystemMenu::instance()->getSourceMap();
        $menu = $map[$node] ?? null;
        if (!$menu) {
            return true;
        }
        
        return AdminGroup::checkPermission($user, $menu->path);
    }
    
    
    /**
     * 检测权限
     * @param object|string  $class 控制器类名或对象
     * @param string         $method 控制器方法
     * @param AdminUserField $user 系统用户数据
     * @return bool
     */
    public static function check(object|string $class, string $method, AdminUserField $user) : bool
    {
        try {
            $reflectionClass = new ReflectionClass($class);
            $attributes      = $reflectionClass->getMethod($method)->getAttributes(self::class);
        } catch (ReflectionException) {
            return true;
        }
        if (!$attributes) {
            return true;
        }
        
        /** @var self $instance */
        $instance = $attributes[0]->newInstance();
        
        return $instance->has($user);
    }
}