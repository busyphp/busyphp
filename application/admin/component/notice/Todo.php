<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\component\notice;

use BusyPHP\App;
use BusyPHP\app\admin\component\notice\todo\TodoInterface;
use BusyPHP\app\admin\component\notice\todo\TodoListParameter;
use BusyPHP\app\admin\component\notice\todo\TodoNode;
use BusyPHP\app\admin\component\notice\todo\TodoReadParameter;
use BusyPHP\app\admin\component\notice\todo\TodoTotalParameter;
use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\ClassHelper;
use BusyPHP\helper\TransHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\traits\ContainerDefine;
use BusyPHP\traits\ContainerInstance;

/**
 * 后台待办类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/18 下午12:18 下午 Todo.php $
 */
class Todo implements ContainerInterface
{
    use ContainerDefine;
    use ContainerInstance;
    
    /**
     * 紧急
     * @var int
     * @style danger
     */
    public const LEVEL_MUST = 1;
    
    /**
     * 重要
     * @var int
     * @style primary
     */
    public const LEVEL_IMPORTANT = 2;
    
    /**
     * 一般
     * @var int
     * @style default
     */
    public const LEVEL_DEFAULT = 99;
    
    protected App $app;
    
    
    /**
     * 定义容器接口
     * @return string
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * 构造函数
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }
    
    
    /**
     * 获取待办总数
     * @param AdminUserField $user
     * @return int
     */
    public function getTotal(AdminUserField $user) : int
    {
        $total = 0;
        foreach ($this->getInterfaces() as $item) {
            $parameter = new TodoTotalParameter();
            $parameter->setUser($user);
            $total += $item->getAdminTodoTotal($parameter);
        }
        
        return $total;
    }
    
    
    /**
     * 获取待办数据
     * @param AdminUserField $user
     * @return TodoNode[]
     */
    public function getList(AdminUserField $user) : array
    {
        $list  = [];
        $index = 0;
        foreach ($this->getInterfaces() as $item) {
            $interface = TransHelper::base64encodeUrl(get_class($item));
            $parameter = new TodoListParameter();
            $parameter->setUser($user);
            foreach ($item->getAdminTodoList($parameter) as $todo) {
                if (!$todo instanceof TodoNode) {
                    continue;
                }
                
                $todo->setId(TransHelper::base64encodeUrl($interface . ',' . TransHelper::base64encodeUrl($todo->id)));
                if ($todo->sort < 0) {
                    $todo->setSort($index);
                }
                
                $list[] = TodoNode::parse($todo);
                $index++;
            }
        }
        
        return $list;
    }
    
    
    /**
     * 用户点击待办项目的时候反馈已读
     * @param AdminUserField $user
     * @param string         $id
     */
    public function setRead(AdminUserField $user, string $id)
    {
        [$class, $id] = explode(',', TransHelper::base64decodeUrl($id));
        $class = TransHelper::base64decodeUrl($class);
        $id    = TransHelper::base64decodeUrl($id);
        if (!$interface = $this->makeInterface($class)) {
            return;
        }
        
        $parameter = new TodoReadParameter();
        $parameter->setUser($user);
        $parameter->setId($id);
        $interface->setAdminTodoRead($parameter);
    }
    
    
    /**
     * 是否已启用待办
     * @return bool
     */
    public function isEnable() : bool
    {
        return (bool) $this->app->config->get('app.admin.todo.enable', false);
    }
    
    
    /**
     * 获取注册的待办
     * @return TodoInterface[]
     */
    protected function getInterfaces() : array
    {
        $list = [];
        foreach ((array) $this->app->config->get('app.admin.todo.class', []) as $item) {
            if ($interface = $this->makeInterface($item)) {
                $list[] = $interface;
            }
        }
        
        return $list;
    }
    
    
    /**
     * 实例化接口类
     * @param class-string<TodoInterface> $class
     * @return null|TodoInterface
     */
    protected function makeInterface($class) : ?TodoInterface
    {
        if (!is_subclass_of($class, TodoInterface::class)) {
            return null;
        }
        
        return $this->app->make($class, [], true);
    }
    
    
    /**
     * 获取级别
     * @param int|null $val
     * @return array{name:string,style:string}|array<int,array{name:string,style:string}>|null
     */
    public static function getLevelMap(int $val = null) : ?array
    {
        return ArrayHelper::getValueOrSelf(ClassHelper::getConstAttrs(self::class, 'LEVEL_', [
            'style' => 'string'
        ]), $val);
    }
}