<?php
declare(strict_types = 1);

namespace BusyPHP\helper;

use DomainException;

/**
 * 3DES加密解密辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/21 下午上午10:37 TripleDesHelper.php $
 */
class TripleDesHelper
{
    /**
     * 加密
     * @param string $value 加密内容
     * @param string $key 密钥
     * @param string $iv 向量
     * @return string
     * @throws DomainException
     */
    public static function encrypt(string $value, string $key = '', string $iv = 'BusyPHP.') : string
    {
        if (strlen($iv) != 8) {
            throw new DomainException('IV必须8位字符');
        }
        
        
        $result = openssl_encrypt($value, 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if (!$result) {
            throw new DomainException('加密失败');
        }
        
        return (string) base64_encode($result);
    }
    
    
    /**
     * 解密
     * @param string $value 解密内容
     * @param string $key 密钥
     * @param string $iv 向量
     * @return string
     * @throws DomainException
     */
    public static function decrypt(string $value, string $key = '', string $iv = 'BusyPHP.') : string
    {
        if (strlen($iv) != 8) {
            throw new DomainException('IV必须8位字符');
        }
        
        $result = openssl_decrypt(base64_decode($value), 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if (!$result) {
            throw new DomainException('解密失败');
        }
        
        return (string) $result;
    }
}