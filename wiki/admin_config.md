# 全局配置

配置文件 `config/admin.php`

```php
return [
    'model' => [
        'admin_user' => [
            // 头像是否必传
            'avatar'   => false,
            
            // 昵称是否必填
            'nickname' => false,
            
            // 手机号选项
            'phone'    => [
                // 是否必填验证
                'required' => false,
                
                // 创建/修改/手机号登录验证规则，支持正则/闭包，默认为验证中国大陆手机号
                'regex'    => null,
                
                // 前端校验手机号正则
                'js_regex' => '^1[3-9]\d{9}$',
            ],
            
            // 邮箱是否必填
            'email'    => false,
            
            // 姓名是否必填
            'name'     => false,
            
            // 证件号码选项
            'card_no'  => [
                // 是否必填验证
                'required' => false,
                
                // 是否验证唯一性
                'unique'   => false,
                
                // 是否校验身份证号码
                'identity' => false
            ],
            
            // 性别是否必选
            'sex'      => false,
            
            // 出生日期是否必选
            'birthday' => false,
            
            // 电话号码选项
            'tel'      => [
                // 是否必填验证
                'required' => false,
                
                // 电话号码验证正则，支持正则/闭包
                'regex'    => null,
                
                // 前端校验电话号码正则
                'js_regex' => null,
            ]
        ]
    ]
];
```

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