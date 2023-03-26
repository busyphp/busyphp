# 后台配置

配置文件 `app/admin/config/app.php`

```php
use BusyPHP\app\admin\component\message\todo\TodoInterface;

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
        
        // 操作提示风格，支持 toast notify
        'operate_tip_style'     => '',
        
        // 模态框的取消按钮是否在右边
        'modal_cancel_right'    => false,
        
        // 通知
        'notice'                => false,
        
        // 待办
        'todo'                  => [
            // 是否启用
            'enable' => false,
            
            // 注册待办类，必须集成 \BusyPHP\app\admin\component\message\todo\TodoInterface} 接口
            'class'  => [
            ]
        ]
    ]
];
```