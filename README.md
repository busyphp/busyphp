BusyPHP使用说明
===============

`BusyPHP` 框架基于 `ThinkPHP6.0` 进行的开发，所以 `ThinkPHP6.0` 中的所有内置方都可以继续使用，具体请参考 [官方手册](https://www.kancloud.cn/manual/thinkphp6_0/1037479) 

## 后台管理系统

### 配置 `app/config/app.php`

```php

<?php

use BusyPHP\app\admin\model\admin\user\AdminUser;

return [
    // 管理面板配置
    'admin' => [
        // 是否启用js/css调试模式，启用后js/css无缓存
        'debug'                 => true,
        
        // JS/CSS版本号
        'version'               => '',
        
        // 自定义全局数据
        'data'                  => '',
        
        // 全局requires，支持数组|闭包|字符串
        'requires'              => '',
        
        // 默认主题风格
        'theme_skin'            => 'default',
        
        // 默认菜单栏是否使用迷你菜单
        'theme_nav_mode'        => false,
        
        // 默认菜单栏是否只有一个能展开
        'theme_nav_single_hold' => false,
        
        // 自定义模板配置
        'template'              => [
            // 首页模板
            'index' => ''
        ]
    ],
    
    // 模型相关配置
    'model' => [
        // 管理员模型配置
        AdminUser::class => [
            // 创建/修改/手机号登录验证规则，支持正则/闭包，默认为验证中国大陆手机号
            'check_phone_match' => function(string $phone) {
            }
        ]
    ]
];
```
