<?php
// 注册路由
use think\facade\Route;


// +----------------------------------------------------
// + admin路由
// +----------------------------------------------------
if (app()->http->getName() === 'admin') {
    Route::group(function() {
        Route::rule('Develop.<control>/<action>', 'Develop\<control>@<action>')->append([
            'group' => 'Develop'
        ]);
        
        Route::rule('System.<control>/<action>', 'System\<control>@<action>')->append([
            'group' => 'System',
        ])->pattern([
            'control' => '[File|Region|Group|User|Update|Index]+'
        ]);
        
        Route::rule('Common.<control>/<action>', 'Common\<control>@<action>')->append([
            'group' => 'Common',
        ])->pattern([
            'control' => '[Passport|Ueditor|Js|Action|Index]+'
        ]);
        
        Route::group(function() {
            $index = 'Common\Index@index';
            Route::rule('/', $index);
            Route::rule('index', $index);
        })->append([
            'action'  => 'index',
            'control' => 'Index',
            'group'   => 'Common'
        ]);
        
        
        // 注册登录地址
        Route::rule('login', 'Common\Passport@login')->append([
            'group'   => 'Common',
            'control' => 'Passport',
            'action'  => 'login'
        ])->name('admin_login');
        
        // 注册退出地址
        Route::rule('out', 'Common\Passport@out')->append([
            'group'   => 'Common',
            'control' => 'Passport',
            'action'  => 'out'
        ])->name('admin_out');
    })->prefix('BusyPHP\app\admin\controller\\')->append(['type' => 'plugin']);
}


// +----------------------------------------------------
// + 验证码路由
// +----------------------------------------------------
Route::rule('general/verify', 'BusyPHP\app\general\controller\Verify@index');


// +----------------------------------------------------
// + 动态缩图路由
// +----------------------------------------------------
Route::rule('thumbs/<src>', 'BusyPHP\app\general\controller\Thumb@index')->pattern([
    'src' => '.+'
]);
Route::rule('thumbs', 'BusyPHP\app\general\controller\Thumb@index');


// +----------------------------------------------------
// + 动态二维码路由
// +----------------------------------------------------
Route::rule('qrcodes/<src>', 'BusyPHP\app\general\controller\QRCode@index')->pattern([
    'src' => '.+'
]);
Route::rule('qrcodes', 'BusyPHP\app\general\controller\QRCode@index');


// +----------------------------------------------------
// + 数据库安装路由
// +----------------------------------------------------
Route::group(function() {
    Route::rule('general/install/<action>', 'BusyPHP\app\general\controller\Install@<action>');
    Route::rule('general/install', 'BusyPHP\app\general\controller\Install@index')->append(['action' => 'index']);
})->append([
    'type'    => 'plugin',
    'control' => 'Install'
]);