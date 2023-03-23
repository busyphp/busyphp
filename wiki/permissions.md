# 系统权限管理

`BusyPHP` 的权限管理基于标准 `RBAC` 标准实现，可以精准控制操作权限。

## 注解实现

如果做为扩展开发，可以通过在Service中注册控制器

```php
class UseService extends \think\Service {
    public function boot() {
        $this->registerRoutes(function(\think\Route $route) {
            \BusyPHP\app\admin\model\system\menu\SystemMenu::class()::registerAnnotation('扫描的目录');
            \BusyPHP\app\admin\model\system\menu\SystemMenu::class()::registerAnnotation('扫描控制器类名');
        });
    }
}
```

### 一、`class`块支持的注解

**控制器必须继承类**

```php 
\BusyPHP\app\admin\controller\AdminController
```

#### 1. #[MenuGroup] 定义控制器类为分组节点

定义该控制器所属的上级分组节点，该节点必须是已定义的节点。通常顶级分组通过 `开发模式-菜单管理` 创建

```php
/**
 * 分组名称
 */
#[\BusyPHP\app\admin\annotation\MenuGroup(
    path: '#goods', // 定义该控制器类的分组节点标识，默认使用当前控制器名称(不含后缀Controller)，支持自定义分组标识
    name: '', // 定义分组名称，如果不设置默认取注释
    parent: '#system', // 定义该类上级菜单节点，该节点必须是已定义的节点。
    icon: 'fa fa-list-ol', // 定义该分组图标，必须是图标的完整css类名称，如：fa fa-list-ol
    sort: 10 // 定义该分组排序，数字越大排序越靠后
)]
class GoodsController extends \BusyPHP\app\admin\controller\AdminController {};
```

#### 2. #[MenuRoute] 定义控制器类路由名称注解

```php
/**
 * 分组名称
 */
#[\BusyPHP\app\admin\annotation\MenuRoute(
    path: '',   // 定义该控制器类的的路由名称，一般作为外部插件时定义，定义后该控制器将被路由转发，默认为当前控制器名称(不含后缀Controller)
    class: true // 设置路由转发方式为类名，而不是URL
)]
class GoodsController extends \BusyPHP\app\admin\controller\AdminController {};
```

#### 3. #[IgnoreLogin] 排除登录校验注解

设置该属性后，访问该控制器的方法将排除登录校验

```php

#[\BusyPHP\app\admin\annotation\IgnoreLogin]
class GoodsController extends \BusyPHP\app\admin\controller\AdminController {}
```


### 二、`method`块支持的注释

#### 1. #[MenuNode] 定义菜单/权限节点定义注解

```php
class GoodsController extends \BusyPHP\app\admin\controller\AdminController {
    /**
     * 菜单/节点名称
     */
    #[\BusyPHP\app\admin\annotation\MenuNode(
        menu: true, // true是菜单节点，false是权限节点，默认为true
        name: '', // 菜单名称，如果不设置默认取注释
        parent: '/index', // 定义该方法的上级菜单节点，如果不设置则使用当前类定义的MenuGroup注解，支持 / 变量前缀代表当前控制器名称(不含后缀Controller)，如：/index ，则被转为 goods/index
        icon: 'fa fa-list-ol', // 定义菜单图标，必须是图标的完整css类名称，如：fa fa-list-ol
        sort: 10, // 定义菜单所属分组排序，数字越大排序越靠后
        params: '' // 如果是菜单节点，定义该菜单URL支持的参数名，多个用英文逗号分割，便于系统自动获取参数
    )]
    public function action();
}
```


#### 2. #[IgnoreLogin] 排除登录校验注解

设置该属性后，访问该方法将排除登录校验

```php
class GoodsController extends \BusyPHP\app\admin\controller\AdminController {
    /**
     * 菜单/节点名称
     */
    #[\BusyPHP\app\admin\annotation\IgnoreLogin]
    public function action();
}
```

### 示例

```php
<?php
/**
 * 商品管理
 */
#[\BusyPHP\app\admin\annotation\MenuGroup(path: '#goods', parent: '#system', icon: 'fa fa-list-ol')]
class GoodsController extends \BusyPHP\app\admin\controller\AdminController {

    /**
     * 商品列表
     */
    #[\BusyPHP\app\admin\annotation\MenuNode(menu: true, icon: 'fa fa-list-ol', params: 'type,status')]
    public function index() {
        // 该菜单的上级为 #goods
        // 菜单路由地址为 goods/index
    }
    
    /**
     * 添加商品
     */
    #[\BusyPHP\app\admin\annotation\MenuNode(menu: false, parent: '/index')]
    public function add() {
        // 该菜单的上级为 goods/index
        // 菜单路由地址为 goods/add
    }
    
    
    /**
     * 修改商品
     */
    #[\BusyPHP\app\admin\annotation\MenuNode(menu: false, parent: '/index')]
    public function edit() {
        // 该菜单的上级为 goods/index
        // 菜单路由地址为 goods/edit
    }
    
    /**
     * 商品分类
     */
    #[\BusyPHP\app\admin\annotation\MenuNode(menu: true, icon: 'fa fa-list-ol')] 
    public function cate() {
        // 该菜单的上级为 #system
        // 菜单路由地址为 goods/cate
    }
    
    /**
     * 添加商品分类
     */
    #[\BusyPHP\app\admin\annotation\MenuNode(menu: false, parent: '/cate')] 
    public function cate_add() {
        // 该菜单的上级为 goods/cate
        // 菜单路由地址为 goods/cate_add
    }

    /**
     * 修改商品分类
     */
    #[\BusyPHP\app\admin\annotation\MenuNode(menu: false, parent: '/cate')]  
    public function cate_edit() {
        // 该菜单的上级为 goods/cate
        // 菜单路由地址为 goods/cate_edit
    }
    
    /**
     * 商品订单管理
     */
    #[\BusyPHP\app\admin\annotation\MenuNode(menu: true, parent: '#system')]   
    public function order() {
        // 该菜单的上级为 #system
        // 菜单路由地址为 goods/order
    }
        
    /**
     * 修改订单
     * @busy-action-node false
     * @busy-action-parent /order
     */
    #[\BusyPHP\app\admin\annotation\MenuNode(menu: false, parent: '/order')]    
    public function order_edit() {
        // 该菜单的上级为 goods/order
        // 菜单路由地址为 goods/order_edit
    }
    
    /**
     * 删除订单
     */
    #[\BusyPHP\app\admin\annotation\MenuNode(menu: false, parent: '/order')]     
    public function order_delete() {
        // 该菜单的上级为 goods/order
        // 菜单路由地址为 goods/order_delete
    }
}
```

**以上示例最终生成的菜单如下：**

- 系统
    - 商品管理
        - 商品列表
            - 添加商品
            - 修改商品
        - 商品分类
            - 添加商品分类
            - 修改商品分类
    - 商品订单管理
        - 修改订单
        - 删除订单

以上示例看到 `商品订单管理` 可以通过 `#[MenuNode]` 中的 `parent` 参数，脱离当前控制器，作为独立分组。开发过程中可以通过巧妙的搭配实现人性化的`菜单`、`权限`