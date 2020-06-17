<?php

namespace BusyPHP\app\general\controller;

use BusyPHP\Controller;
use BusyPHP\exception\AppException;
use BusyPHP\helper\image\Verify as Captcha;

/**
 * 验证码生成
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/28 下午9:16 上午 VerifyController.php $
 */
class Verify extends Controller
{
    /** 英文验证码 */
    const TYPE_DEFAULT = 0;
    
    /** 中文验证码 */
    const TYPE_CN = 1;
    
    
    /**
     * 验证码显示
     */
    public function index()
    {
        $key                = $this->iGet('key', 'trim');
        $type               = $this->iGet('type', 'intval', 0);
        $width              = $this->iGet('width', 'intval', 0);
        $height             = $this->iGet('height', 'intval', 0);
        $config             = [];
        $config['seKey']    = 'BusyPHP';
        $config['expire']   = 600;
        $config['length']   = 4;
        $config['fontSize'] = 14;
        $config['useCurve'] = true;
        $config['useNoise'] = false;
        $config['fontttf']  = '4.ttf';
        if ($width && $height) {
            $config['imageW'] = $width;
            $config['imageH'] = $height;
        }
        
        switch ($type) {
            // 中文
            case self::TYPE_CN:
                $config['useZh']    = true;
                $config['length']   = 3;
                $config['fontttf']  = '1.ttf';
                $config['fontSize'] = 10;
            break;
        }
        
        $verify = new Captcha($config);
        
        return $verify->entry($key);
    }
    
    
    /**
     * 验证码链接
     * @param string $key 验证码标识
     * @param int    $width 验证码宽度
     * @param int    $height 验证码高度
     * @param int    $type 验证码类型（预留）
     * @return string
     */
    public static function url($key, $width = 0, $height = 0, $type = self::TYPE_DEFAULT)
    {
        return (string) url('/general/verify', [
            'key'    => $key,
            'width'  => $width,
            'height' => $height,
            'type'   => $type
        ]);
    }
    
    
    /**
     * 校验验证码
     * @param string $key 验证码标识
     * @param string $code 验证码
     * @throws AppException
     */
    public static function check($key, $code)
    {
        $verify = new Captcha([
            'reset' => false,
            'seKey' => 'BusyPHP'
        ]);
        
        $verify->check($code, $key);
    }
    
    
    /**
     * 清理验证码
     * @param string $key 验证码标识
     */
    public static function clear($key)
    {
        $verify = new Captcha([
            'reset' => false,
            'seKey' => 'BusyPHP'
        ]);
        $verify->clear($key);
    }
}