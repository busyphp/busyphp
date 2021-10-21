<?php
declare (strict_types = 1);

namespace BusyPHP\app\general\controller;

use BusyPHP\app\admin\setting\QrcodeSetting;
use BusyPHP\Controller;
use BusyPHP\helper\TransHelper;
use BusyPHP\file\QRCode;
use Exception;
use think\exception\HttpException;

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
        $src      = $this->param('src/s', 'trim');
        $src      = trim($src, '/');
        $pathInfo = pathinfo($src);
        
        // 通过文件名获取到二维码内容和配置
        $list    = explode('.', $pathInfo['filename'], 2);
        $content = $list[0] ?? '';
        $config  = $list[1] ?? '';
        
        // 解析二维码内容提取出内容和LOGO
        $content = TransHelper::base64decodeUrl($content);
        $list    = explode('#!logo!#', $content, 2);
        $text    = $list[0] ?? '';
        $logo    = $list[1] ?? '';
        
        // 提取配置
        $list   = explode('X', strtoupper($config), 3);
        $level  = $list[0] ?? null;
        $size   = $list[1] ?? null;
        $margin = $list[2] ?? null;
        
        try {
            $setting = QrcodeSetting::init();
            $qrCode  = new QRCode();
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
            if ($setting->isSaveLocal()) {
                $qrCode->save(true, $this->app->getPublicPath("qrcodes/{$pathInfo['dirname']}/{$pathInfo['basename']}"));
            }
            
            return $qrCode->exec(true);
        } catch (Exception $e) {
            throw new HttpException(404, $e->getMessage());
        }
    }
}