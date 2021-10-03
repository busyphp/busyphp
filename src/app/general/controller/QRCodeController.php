<?php

namespace BusyPHP\app\general\controller;

use BusyPHP\Controller;
use BusyPHP\exception\AppException;
use BusyPHP\helper\util\Transform;
use BusyPHP\file\QRCode;

/**
 * 动态二维码生成
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/12 下午7:34 下午 QrCodeController.php $
 */
class QRCodeController extends Controller
{
    public function index()
    {
        $path    = $this->param('src', 'trim');
        $path    = trim($path, '/');
        $info    = pathinfo($path);
        $list    = explode('.', $info['filename'], 2);
        $content = $list[0] ?? '';
        $config  = $list[1] ?? '';
        $content = Transform::base64decodeUrl($content);
        $list    = explode('###', $content, 2);
        $text    = $list[0] ?? '';
        $logo    = $list[1] ?? '';
        $list    = explode('X', strtoupper($config), 3);
        $level   = $list[0] ?? null;
        $size    = $list[1] ?? null;
        $margin  = $list[2] ?? null;
        
        try {
            $qrCode = new QRCode();
            $config = $qrCode->getConfig()->get('qrcode');
            $qrCode->text($text);
            
            // 识别率
            if ($level) {
                $qrCode->level($level);
            }
            
            // 间距
            if ($margin) {
                $qrCode->margin((int) $margin);
            }
            
            // 尺寸
            if ($size) {
                $qrCode->size((int) $size);
            }
            
            // 设置LOGO
            if ($logo) {
                $qrCode->logo($logo);
            }
            
            // 保存到本地
            if ($config['save_local'] ?? false) {
                $qrCode->save(true, $this->app->getPublicPath("qrcodes/{$info['dirname']}/{$info['basename']}"));
            }
            
            $data = $qrCode->exec(true);
            
            return response($data, 200, ['Content-Length' => strlen($data)])->contentType('image/jpeg');
        } catch (AppException $e) {
            abort(404, $e->getMessage());
            
            return null;
        }
    }
}