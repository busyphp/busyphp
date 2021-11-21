BusyPHP使用说明
===============

`BusyPHP` 框架基于 `ThinkPHP6.0` 进行的开发，所以 `ThinkPHP6.0` 中的所有内置方都可以继续使用，具体请参考 [官方手册](https://www.kancloud.cn/manual/thinkphp6_0/1037479) 

## 后台管理系统

### 配置 `app/config/app.php`

```php
return [
    // 是否启用js/css调试模式，启用后js/css无缓存
    'debug'                 => true,
    
    // 默认主题风格
    'theme_skin'            => 'default',
    
    // 默认菜单栏是否使用迷你菜单
    'theme_nav_mode'        => false,
    
    // 默认菜单栏是否只有一个能展开
    'theme_nav_single_hold' => false,
    
    // 自定义生成缓存，以配合 "生成缓存" 的功能
    'create_cache'          => function() {
        // 这里放你生成缓存的代码
    },
    
    // 自定义缓存清理，以配合 "清理缓存" 的功能
    'clear_cache'           => function() {
        // 这里放你清理缓存的代码
    }
];
```
