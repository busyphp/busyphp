<?php

namespace BusyPHP\helper\crypt;

/**
 * 3DES加密解密类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/28 下午2:06 下午 TripleDES.php $
 */
class TripleDES
{
    public $key;
    
    public $iv = "01234567";
    
    /** @var self[] */
    private static $init = array();
    
    
    /**
     * 加密数据
     * @param string $data
     * @param string $secret
     * @return false|string
     */
    public static function encode($data, $secret)
    {
        return self::init($secret)->encrypt($data);
    }
    
    
    /**
     * 解密数据
     * @param $data
     * @param $secret
     * @return false|string
     */
    public static function decode($data, $secret)
    {
        return self::init($secret)->decrypt($data);
    }
    
    
    /**
     * 快速实例化
     * @param $secret
     * @return TripleDES
     */
    private static function init($secret)
    {
        if (!isset(self::$init[$secret])) {
            self::$init[$secret] = new self($secret);
        }
        
        return self::$init[$secret];
    }
    
    
    /**
     * TripleDES constructor.
     * @param $key
     */
    function __construct($key)
    {
        $this->key = substr($key, 0, 24);
    }
    
    
    /**
     * 加密
     * @param string $value 要传的参数
     * @return false|string
     * @see https://stackoverflow.com/questions/41181905/php-mcrypt-encrypt-to-openssl-encrypt-and-openssl-zero-padding-problems#
     * */
    public function encrypt($value)
    {
        $value  = $this->paddingPKCS7($value);
        $cipher = "DES-EDE3-CBC";
        if (in_array($cipher, openssl_get_cipher_methods())) {
            return openssl_encrypt($value, $cipher, $this->key, OPENSSL_SSLV23_PADDING, $this->iv);
        }
        
        return false;
    }
    
    
    /**
     * 解密
     * @param string $value 要传的参数
     * @return bool|false|string
     * */
    public function decrypt($value)
    {
        $decrypted = openssl_decrypt($value, 'DES-EDE3-CBC', $this->key, OPENSSL_SSLV23_PADDING, $this->iv);
        
        return $this->unPaddingPKCS7($decrypted);
    }
    
    
    private function paddingPKCS7($data)
    {
        $block_size   = 8;
        $padding_char = $block_size - (strlen($data) % $block_size);
        $data         .= str_repeat(chr($padding_char), $padding_char);
        
        return $data;
    }
    
    
    private function unPaddingPKCS7($text)
    {
        $pad = ord($text[strlen($text) - 1]);
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        
        return substr($text, 0, -1 * $pad);
    }
}