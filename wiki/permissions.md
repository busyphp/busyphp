# 系统权限管理

`BusyPHP` 的权限管理基于标准 `RBAC` 标准实现，可以精准控制操作权限。

## 注解实现

由于`PHP8.0+`才支持注解，为了兼容`8.0`以下版本，目前仅支持使用标准注释块

### `class`块支持的注释

**控制器必须继承类**

```php 
\BusyPHP\app\admin\controller\AdminController
```

| 注释名称                         | 注释说明                                                             |
|------------------------------|:-----------------------------------------------------------------|
| @busy-controller-parent      | 定义该控制器所属的上级分组节点，该节点必须是已定义的节点                                     |
| @busy-controller-group       | 定义该控制器类是否为分组节点，设为 `true` 则使用当前控制器名称(不含后缀`Controller`)，支持自定义分组标识  |
| @busy-controller-icon        | 定义分组图标，必须是图标的完整`css类名称`，如：`fa fa-list-ol`                        |
| @busy-controller-name        | 定义该控制器的名称，一般作为外部插件时定义，定义后该控制器将被路由转发，默认为当前控制器名称(不含后缀`Controller`) |
| @busy-controller-route-class | 设置路由转发方式为类名，而不是URL                                               |
| @busy-controller-sort        | 定义该分组排序，数字越大排序越靠后                                                |

### `method`块支持的注释

| 注释名称                | 注释说明                                                                                                                                 |
|---------------------|:-------------------------------------------------------------------------------------------------------------------------------------|
| @busy-action-node   | 定义该方法是否为`菜单`/`权限`节点，`true`菜单节点，`false`权限节点                                                                                           |
| @busy-action-parent | 定义该方法的上级菜单节点，该节点必须是已定义的节点，如果不设置则使用当前类定义的 `@busy-controller-group`，支持`/`变量前缀代表当前控制器名称(不含后缀`Controller`)，如：`/index`，则被转为 `goods/index` |
| @busy-action-icon   | 定义菜单图标，必须是图标的完整`css类名称`，如：`fa fa-list-ol`                                                                                            |
| @busy-action-params | 如果是`菜单节点`，定义该菜单URL支持的参数名，多个用英文逗号分割，便于系统自动获取参数                                                                                        |
| @busy-action-sort   | 定义该分组排序，数字越大排序越靠后                                                                                                                    |

**示例**

```php
<?php
/**
 * 商品管理
 * @busy-controller-group #goods
 * @busy-controller-icon fa fa-list-ol
 * @busy-controller-parent #system
 */
class GoodsController extends \BusyPHP\app\admin\controller\AdminController {

    /**
     * 商品列表
     * @busy-action-node true
     * @busy-action-icon fa fa-list-ol
     * @busy-action-params type,status
     */
    public function index() {
        // 该菜单的上级为 #goods
        // 菜单路由地址为 goods/index
    }
    
    /**
     * 添加商品
     * @busy-action-node false
     * @busy-action-parent /index
     */
    public function add() {
        // 该菜单的上级为 goods/index
        // 菜单路由地址为 goods/add
    }
    
    
    /**
     * 修改商品
     * @busy-action-node false
     * @busy-action-parent /index
     */
    public function edit() {
        // 该菜单的上级为 goods/index
        // 菜单路由地址为 goods/edit
    }
    
    /**
     * 商品分类
     * @busy-action-node true
     * @busy-action-icon fa fa-list-ol
     */
    public function cate() {
        // 该菜单的上级为 #system
        // 菜单路由地址为 goods/cate
    }
    
    /**
     * 添加商品分类
     * @busy-action-node false
     * @busy-action-parent /cate
     */
    public function cate_add() {
        // 该菜单的上级为 goods/cate
        // 菜单路由地址为 goods/cate_add
    }

    /**
     * 修改商品分类
     * @busy-action-node false
     * @busy-action-parent /cate
     */
    public function cate_edit() {
        // 该菜单的上级为 goods/cate
        // 菜单路由地址为 goods/cate_edit
    }
    
    /**
     * 商品订单管理
     * @busy-action-node true
     * @busy-action-parent #system
     */
    public function order() {
        // 该菜单的上级为 #system
        // 菜单路由地址为 goods/order
    }
        
    /**
     * 修改订单
     * @busy-action-node false
     * @busy-action-parent /order
     */
    public function order_edit() {
        // 该菜单的上级为 goods/order
        // 菜单路由地址为 goods/order_edit
    }
    
    /**
     * 删除订单
     * @busy-action-node false
     * @busy-action-parent /order
     */
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

以上示例看到 `商品订单管理` 可以通过 `@busy-action-parent`，脱离当前控制器，作为独立分组。开发过程中可以通过巧妙的搭配实现人性化的`菜单`、`权限`