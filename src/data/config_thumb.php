<?php

use BusyPHP\App;
use BusyPHP\file\Image;

// +----------------------------------------------------
// + 动态缩图配置
// +----------------------------------------------------
return [
    // 绑定域名
    'domain'               => '',
    
    // 无图的变量标识
    'empty_image_var'      => 'no_picture',
    
    // 无图图片资源路径
    'empty_image_path'     => App::getInstance()->getPublicPath('assets/data/images/no_image.jpeg'),
    
    // 允许的尺寸配置，false则自由配置
    'sizes'                => [
        'test' => [500, 500],
        '100x100'
    ],
    
    // 背景颜色
    'bg_color'             => 'FFFFFF',
    
    // 是否保存到本地
    'save_local'           => false,
    
    // 是否打水印
    'watermark_status'     => false,
    
    // 水印图片文件，留空则获取后台配置
    'watermark_image_path' => App::getInstance()->getPublicPath('assets/data/images/watermark.png'),
    
    // 水印图片位置，留空则获取后台配置
    'watermark_position'   => Image::P_BOTTOM_RIGHT,
    
    // 水印透明度
    'watermark_opacity'    => 90,
    
    // 水印水平偏移像素
    'watermark_x'          => 10,
    
    // 水印垂直偏移像素
    'watermark_y'          => 10,
];