<?php
declare(strict_types = 1);

namespace BusyPHP\helper;

/**
 * 正则验证辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/21 下午上午10:19 RegexHelper.php $
 */
class RegexHelper
{
    protected static $validate = [
        'require'  => '/.+/',
        'email'    => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
        'url'      => '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
        'currency' => '/^\d+(\.\d+)?$/',
        'number'   => '/^\d+$/',
        'zip'      => '/^\d{6}$/',
        'integer'  => '/^[-\+]?\d+$/',
        'double'   => '/^[-\+]?\d+(\.\d+)?$/',
        'english'  => '/^[A-Za-z]+$/',
        'phone'    => '/^(\\+?86-?)?(18|15|13|14|17|16|19)[0-9]{9}$/',
        'tel'      => '/^(010|02\\d{1}|0[3-9]\\d{2})-\\d{7,9}(-\\d+)?$/',
        'account'  => '/^[A-Za-z0-9_]+$/'
    ];
    
    
    /**
     * 执行正则验证
     * @param mixed  $value 校验的内容
     * @param string $rule 正则
     * @return bool
     */
    public static function match($value, string $rule) : bool
    {
        if (isset(self::$validate[$rule])) {
            $rule = self::$validate[$rule];
        }
        
        return preg_match($rule, $value) === 1;
    }
    
    
    /**
     * 校验是否英文数字下划线
     * @param string $value
     * @return bool
     */
    public static function account(string $value) : bool
    {
        return self::match($value, 'account');
    }
    
    
    /**
     * 校验是否电话号码
     * @param string $value
     * @return bool
     */
    public static function tel(string $value) : bool
    {
        return self::match($value, 'tel');
    }
    
    
    /**
     * 校验是否手机号
     * @param string $value
     * @return bool
     */
    public static function phone(string $value) : bool
    {
        return self::match($value, 'phone');
    }
    
    
    /**
     * 校验是否全部是英文字母
     * @param string $value
     * @return bool
     */
    public static function english(string $value) : bool
    {
        return self::match($value, 'english');
    }
    
    
    /**
     * 校验是否纯数字
     * @param mixed $value
     * @return bool
     */
    public static function number($value) : bool
    {
        return self::match($value, 'number');
    }
    
    
    /**
     * 校验是否邮箱地址
     * @param string $value
     * @return bool
     */
    public static function email(string $value) : bool
    {
        return self::match($value, 'email');
    }
}