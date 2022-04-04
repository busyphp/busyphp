<?php
namespace PHPSTORM_META {
    override(
        \app(0) |
        \think\Container::make(0) |
        \think\Container::get(0),
        
        map([
            'app'        => \BusyPHP\App::class,
            'request'    => \BusyPHP\Request::class,
            'db'         => \BusyPHP\Db::class,
            'cache'      => \think\Cache::class,
            'config'     => \think\Config::class,
            'console'    => \think\Console::class,
            'cookie'     => \think\Cookie::class,
            'env'        => \think\Env::class,
            'event'      => \think\Event::class,
            'http'       => \think\Http::class,
            'lang'       => \think\Lang::class,
            'log'        => \think\Log::class,
            'middleware' => \think\Middleware::class,
            'response'   => \think\Response::class,
            'route'      => \think\Route::class,
            'session'    => \think\Session::class,
            'validate'   => \think\Validate::class,
            'view'       => \think\View::class,
            'filesystem' => \think\Filesystem::class,
            ''           => '@' | \BusyPHP\App::class,
        ])
    );
    
    registerArgumentsSet(
        'array_helper_orders',
        \BusyPHP\helper\ArrayHelper::ORDER_BY_ASC,
        \BusyPHP\helper\ArrayHelper::ORDER_BY_DESC,
        \BusyPHP\helper\ArrayHelper::ORDER_BY_NAT
    );
    
    expectedArguments(\BusyPHP\helper\ArrayHelper::listSortBy(), 2, argumentsSet('array_helper_orders'));
    expectedArguments(\BusyPHP\helper\ArrayHelper::sortTree(), 2, argumentsSet('array_helper_orders'));
    
    registerArgumentsSet(
        'pathinfo_options',
        PATHINFO_DIRNAME,
        PATHINFO_FILENAME,
        PATHINFO_EXTENSION,
        PATHINFO_BASENAME,
        PATHINFO_ALL
    );
    expectedArguments(\BusyPHP\helper\FileHelper::pathInfo(1), argumentsSet('pathinfo_options'));
    
    
    registerArgumentsSet(
        'qrcode_levels',
        \BusyPHP\file\QRCode::LEVEL_L,
        \BusyPHP\file\QRCode::LEVEL_M,
        \BusyPHP\file\QRCode::LEVEL_Q,
        \BusyPHP\file\QRCode::LEVEL_H
    );
    expectedArguments(\BusyPHP\file\QRCode::level(), 0, argumentsSet('qrcode_levels'));
    expectedArguments(\BusyPHP\file\qrcode\QRCodeUrl::level(), 0, argumentsSet('qrcode_levels'));
    
    
    registerArgumentsSet(
        'image_thumbs',
        \BusyPHP\file\Image::THUMB_CORP,
        \BusyPHP\file\Image::THUMB_LOSE,
        \BusyPHP\file\Image::THUMB_ZOOM
    );
    expectedArguments(\BusyPHP\file\Image::thumb(), 0, argumentsSet('image_thumbs'));
    expectedArguments(\BusyPHP\file\image\ThumbUrl::type(), 0, argumentsSet('image_thumbs'));
    expectedArguments(\thumb_url(), 2, argumentsSet('image_thumbs'));
    
    
    registerArgumentsSet(
        'image_watermark_positions',
        \BusyPHP\file\Image::P_TOP,
        \BusyPHP\file\Image::P_BOTTOM,
        \BusyPHP\file\Image::P_LEFT,
        \BusyPHP\file\Image::P_RIGHT,
        \BusyPHP\file\Image::P_TOP_LEFT,
        \BusyPHP\file\Image::P_TOP_RIGHT,
        \BusyPHP\file\Image::P_BOTTOM_LEFT,
        \BusyPHP\file\Image::P_BOTTOM_RIGHT,
        \BusyPHP\file\Image::P_CENTER,
        \BusyPHP\file\Image::P_FILL
    );
    expectedArguments(\BusyPHP\file\Image::watermark(), 1, argumentsSet('image_watermark_positions'));
    expectedReturnValues(\BusyPHP\file\Image::numberToWatermarkPosition(), argumentsSet('image_watermark_positions'));
    
    
    registerArgumentsSet(
        'image_ext',
        \BusyPHP\file\Image::F_PNG,
        \BusyPHP\file\Image::F_GIF,
        \BusyPHP\file\Image::F_JPEG,
        \BusyPHP\file\Image::F_JPG,
        \BusyPHP\file\Image::F_ICO,
        \BusyPHP\file\Image::F_BMP
    );
    expectedArguments(\BusyPHP\file\Image::format(), 0, argumentsSet('image_ext'));
}