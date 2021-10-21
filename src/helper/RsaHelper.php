<?php
declare(strict_types = 1);

namespace BusyPHP\helper;

use DomainException;
use RuntimeException;
use think\exception\FileException;
use think\exception\ValidateException;

/**
 * RSA加密解密辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/21 下午上午10:49 RsaHelper.php $
 */
class RsaHelper
{
    /**
     * 签名
     * @param string $value 待签名的数据
     * @param string $privateCert 私钥证书内容
     * @param bool   $isFile 证书是否文件地址
     * @param bool   $rsa2 是否使用RSA2签名
     * @param string $name 证书标记名称
     * @return string
     * @throws DomainException
     * @throws FileException
     */
    public static function sign(string $value, string $privateCert, bool $isFile = false, bool $rsa2 = false, string $name = '') : string
    {
        $resource = self::getPrivateCert($privateCert, $isFile, $rsa2, $name);
        if (!openssl_sign($value, $sign, $resource, $rsa2 ? OPENSSL_ALGO_SHA256 : OPENSSL_ALGO_SHA1)) {
            openssl_free_key($resource);
            throw new DomainException('签名失败');
        }
        
        openssl_free_key($resource);
        
        return base64_encode($sign);
    }
    
    
    /**
     * 验签名
     * @param string $value 待签名的数据
     * @param string $sign 需要验证的签名内容
     * @param string $publicCert 公钥证书
     * @param bool   $isFile 证书是否文件
     * @param bool   $rsa2 是否使用RSA2验证
     * @param string $name 证书标记名称
     * @throws DomainException
     * @throws FileException
     * @throws ValidateException
     */
    public static function verify(string $value, string $sign, string $publicCert, bool $isFile = false, bool $rsa2 = false, $name = '')
    {
        $resource = self::getPublicCert($publicCert, $isFile, $rsa2, $name);
        $result   = openssl_verify($value, base64_decode($sign), $resource, $rsa2 ? OPENSSL_ALGO_SHA256 : OPENSSL_ALGO_SHA1);
        openssl_free_key($resource);
        if ($result != 1) {
            throw new ValidateException('签名校验错误');
        }
    }
    
    
    /**
     * 私钥加密
     * @param string $value 加密内容
     * @param string $privateCert 私钥证书内容
     * @param bool   $isFile 证书是否文件
     * @param bool   $rsa2 是否使用RSA2加密
     * @param string $name 证书标记名称
     * @return string
     * @throws DomainException
     * @throws FileException
     */
    public static function encryptPrivate(string $value, string $privateCert, bool $isFile = false, bool $rsa2 = false, $name = '') : string
    {
        if (!openssl_private_encrypt($value, $result, self::getPrivateCert($privateCert, $isFile, $rsa2, $name))) {
            throw new DomainException('加密失败');
        }
        
        return base64_encode($result);
    }
    
    
    /**
     * 私钥解密
     * @param string $value 解密内容
     * @param string $privateCert 私钥证书内容
     * @param bool   $isFile 证书是否文件
     * @param bool   $rsa2 是否使用RSA2加密
     * @param string $name 证书标记名称
     * @return string
     * @throws DomainException
     * @throws FileException
     */
    public static function decryptPrivate(string $value, string $privateCert, bool $isFile = false, bool $rsa2 = false, $name = '') : string
    {
        if (!openssl_private_decrypt(base64_decode($value), $result, self::getPrivateCert($privateCert, $isFile, $rsa2, $name))) {
            throw new DomainException('解密失败');
        }
        
        return $result;
    }
    
    
    /**
     * 公钥加密
     * @param string $value 加密内容
     * @param string $publicCert 公钥证书内容
     * @param bool   $isFile 证书是否文件
     * @param bool   $rsa2 是否使用RSA2解密
     * @param string $name 证书标记名称
     * @return string
     * @throws DomainException
     * @throws FileException
     */
    public static function encryptPublic(string $value, string $publicCert, bool $isFile = false, bool $rsa2 = false, $name = '') : string
    {
        if (!openssl_public_encrypt($value, $result, self::getPublicCert($publicCert, $isFile, $rsa2, $name))) {
            throw new DomainException('加密失败');
        }
        
        return base64_encode($result);
    }
    
    
    /**
     * 公钥解密
     * @param string $value 解密内容
     * @param string $publicCert 公钥证书内容
     * @param bool   $isFile 证书是否文件
     * @param bool   $rsa2 是否使用RSA2解密
     * @param string $name 证书标记名称
     * @return string
     * @throws DomainException
     * @throws FileException
     */
    public static function decryptPublic(string $value, string $publicCert, bool $isFile = false, bool $rsa2 = false, $name = '') : string
    {
        if (!openssl_public_decrypt(base64_decode($value), $result, self::getPublicCert($publicCert, $isFile, $rsa2, $name))) {
            throw new DomainException('解密失败');
        }
        
        return $result;
    }
    
    
    /**
     * 解析证书内容
     * @param string $name 证书标记名称
     * @param string $cert 证书内容
     * @return string
     */
    public static function parseCert(string $name, string $cert) : string
    {
        $begin = "-----BEGIN {$name} KEY-----";
        $end   = "-----END {$name} KEY-----";
        $cert  = str_replace([$begin, $end, "\n"], '', $cert);
        $cert  = $begin . "\n" . wordwrap($cert, 64, "\n", true) . "\n" . $end;
        
        return $cert;
    }
    
    
    /**
     * 获取私钥句柄
     * @param string $privateCert 私钥证书内容
     * @param bool   $isFile 证书是否文件
     * @param bool   $rsa2 是否使用RSA2
     * @param string $name 证书标记名称
     * @return resource
     * @throws DomainException
     * @throws FileException
     */
    public static function getPrivateCert(string $privateCert, bool $isFile = false, bool $rsa2 = false, string $name = '')
    {
        if ($isFile) {
            if (!is_file($privateCert)) {
                throw new FileException("私钥证书不存在: {$privateCert}");
            }
            $privateCert = (string) @file_get_contents($privateCert);
        }
        
        $result = $rsa2 ? openssl_pkey_get_private(self::parseCert($name ?: 'PRIVATE', $privateCert)) : openssl_get_privatekey(self::parseCert($name ?: 'PRIVATE', $privateCert));
        if (!$result) {
            throw new DomainException('私钥格式不正确');
        }
        
        return $result;
    }
    
    
    /**
     * 获取私钥句柄
     * @param string $publicCert 公钥证书内容
     * @param bool   $isFile 证书是否文件
     * @param bool   $rsa2 是否使用RSA2
     * @param string $name 证书标记名称
     * @return resource
     * @throws DomainException
     * @throws FileException
     */
    public static function getPublicCert(string $publicCert, bool $isFile = false, bool $rsa2 = false, string $name = '')
    {
        if ($isFile) {
            if (!is_file($publicCert)) {
                throw new FileException("公钥证书不存在: {$publicCert}");
            }
            $publicCert = (string) @file_get_contents($publicCert);
        }
        
        $result = $rsa2 ? openssl_pkey_get_public(self::parseCert($name ?: 'PUBLIC', $publicCert)) : openssl_get_publickey(self::parseCert($name ?: 'PUBLIC', $publicCert));
        if (!$result) {
            throw new DomainException('公钥格式不正确');
        }
        
        return $result;
    }
    
    
    /**
     * 生成证书
     * @param array  $config 配置
     * @param int    $length 长度
     * @param int    $expireDay 过期天数
     * @param string $password 私钥证书密码
     * @param int    $keyType 可选值 OPENSSL_KEYTYPE_DSA, OPENSSL_KEYTYPE_DH, OPENSSL_KEYTYPE_RSA OPENSSL_KEYTYPE_EC.
     * @return array
     */
    public static function createCert(array $config = [], int $length = 512, int $expireDay = 0, string $password = '', int $keyType = OPENSSL_KEYTYPE_RSA) : array
    {
        $privateKey = openssl_pkey_new([
            'private_key_bits' => $length,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        ]);
        
        // 配置
        $config                           = $config ?: [];
        $config['countryName']            = $config['countryName'] ?? 'GB'; // 国家
        $config['stateOrProvinceName']    = $config['stateOrProvinceName'] ?? 'BeiJin'; // 省份
        $config['localityName']           = $config['localityName'] ?? 'BeiJin'; // 城市
        $config['organizationName']       = $config['organizationName'] ?? 'BusyPHP'; // 姓名
        $config['organizationalUnitName'] = $config['organizationalUnitName'] ?? 'BusyPHP'; //组织名称
        $config['commonName']             = $config['commonName'] ?? 'BusyPHP'; //公共名称
        $config['emailAddress']           = $config['emailAddress'] ?? 'busyphp@harter.cn'; //邮箱
        $csr                              = openssl_csr_new($config, $privateKey);
        
        // 导出证书
        $csrSign = openssl_csr_sign($csr, null, $privateKey, $expireDay);
        if (!$csrSign) {
            throw new RuntimeException("证书签名失败");
        }
        
        if (!openssl_x509_export($csrSign, $certificate)) {
            throw new RuntimeException("导出证书失败");
        }
        
        // 私钥
        if (!openssl_pkcs12_export($csrSign, $private, $privateKey, $password)) {
            throw new RuntimeException("导出私钥证书失败");
        }
        
        if (!openssl_pkcs12_read($private, $privateCerts, $password)) {
            throw new RuntimeException("读取证书失败");
        }
        
        // 公钥
        if (!$publicResource = openssl_pkey_get_public($certificate)) {
            throw new RuntimeException("获取公钥证书失败");
        }
        
        $publicCerts = openssl_pkey_get_details($publicResource);
        if (!$publicCerts) {
            throw new RuntimeException("获取公钥证书失败");
        }
        
        return [
            'certificate' => $certificate,
            'private'     => $privateCerts['pkey'],
            'public'      => $publicCerts['key']
        ];
    }
}