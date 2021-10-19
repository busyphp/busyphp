<?php
// +----------------------------------------------------
// + 二维码生成配置
// +----------------------------------------------------
use BusyPHP\App;
use BusyPHP\file\QRCode;

return [
    // 动态二维码绑定域名
    'domain'      => '',
    
    // 动态二维码是否保存到本地
    'save_local'  => false,
    
    // 默认识别率级别
    'level'       => QRCode::LEVEL_H,
    
    // 默认尺寸, 范围 1 - 10
    'size'        => 10,
    
    // 默认间距
    'margin'      => 1,
    
    // 二维码图片质量
    'quality'     => 80,
    
    // 是否加LOGO
    'logo_status' => false,
    
    // LOGO路径
    'logo_path'   => App::init()->getPublicPath('assets/data/images/qr_logo.png'),
    
    // LOGO大小 数值越大，logo越小
    'logo_size'   => 5
];