# 全局配置

配置文件 `config/admin.php`

```php
return [
    'model' => [
        // 系统用户模型
        'admin_user'   => [
            // 验证相关
            'validate' => [
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
                ],
            ],
            
            // 登录相关
            'login'    => [
                // 解密通行 authKey 的秘钥，24位字符，默认为：Pe78mUtfomfhHqSHGpQ3jAlI
                'auth_secret'           => '',
                
                // 通行token扩展
                'auth_extend'           => [
                    // 创建扩展内容，生成token时调用
                    'create' => null,
                    
                    // 检测扩展内容，检测登录时调用，失败请抛出异常
                    'check'  => null
                ],
                
                // 允许多设备同时登录的token生成扩展，支持闭包与字符串，闭包必须返回字符串
                'multiple_client_token' => null,
            ],
        ],
        
        // 用户通行模型
        'system_token' => [
            // 登录类型映射
            'type_map'      => [
                1 => '网页端',
                2 => 'APP端'
            ],
            
            // 用户类型映射
            'user_type_map' => [
                1 => '系统用户',
                2 => '会员'
            ],
        ],
        
        // 文件模型
        'system_file' => [
            // 文件类型扩展
            'file_type_map' => [
                // 类型 => [
                //     'name' => '类型名称',
                //     'icon' => 'fa fa-file-o',
                //     'types' => ['文件扩展名1', '文件扩展名2', '文件扩展名...']
                // ]
            ],
    
            // 文件图标映射
            'file_icon_map' => [
                // 格式 => '图标名称或图标路径'
            ]
        ],
        
        // 系统消息模型
        'admin_message' => [
            // 消息类型映射
            'type_map' => [
                // 类型 => [
                //     'name' => '类型名称'
                //     '自定义1' => ...
                //     '自定义2' => ...
                // ]
            ]
        ]
    ]
];
```

# 后台配置

配置文件 `app/admin/config/app.php`

```php
use BusyPHP\app\admin\component\notice\data\AdminOperate;
use BusyPHP\app\admin\component\notice\todo\TodoInterface;
use BusyPHP\app\admin\model\admin\message\AdminMessageField;
use BusyPHP\app\admin\model\admin\user\AdminUserField;

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
                
        // 打印css
        'print_css'             => '',
        
        // 通用打印样式
        'print_style'           => '',
        
        // 通知铃声URL
        'bell'                  => '',
        
        // 全局websocket
        'ws'                    => [
            // 是否启用
            'enable'          => false,
            
            // websocket 地址
            'url'             => function(AdminUserField $user) : string {
                return 'ws://127.0.0.1?id=' . $user->id;
            },
            
            // 发送ping包间隔毫秒，0为不发送
            'ping_interval'   => 5000,
            
            // 发送ping包的数据
            'ping_data'       => function() : string {
                return json_encode(['ping' => '']);
            },
            
            // 断线重连间隔毫秒
            'reconnect_delay' => 3000,
        ],
        
        // 系统消息
        'message'               => [
            // 是否启用
            'enable'  => true,
            
            // 操作解析回调
            'operate' => function(AdminMessageField $message, AdminOperate $operate) {
               
            }
        ],
        
        // 待办
        'todo'                  => [
            // 是否启用
            'enable' => false,
            
            // 注册待办类，必须集成 \BusyPHP\app\admin\component\notice\todo\TodoInterface} 接口
            'class'  => [
            ]
        ]
    ]
];
```