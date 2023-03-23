<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\field;

use Attribute;
use BusyPHP\Model;
use BusyPHP\model\Field;

/**
 * 字段校验注解类，用于 {@see Model::validate()} 或 {@see Field::getValidate()}
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/12/14 15:50 Validator.php $
 * @see Field
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Validator
{
    /** @var string 验证是否和某个字段的值一致 */
    public const CONFIRM = 'confirm';
    
    /** @var string 验证是否和某个字段的值是否不同 */
    public const DIFFERENT = 'different';
    
    /** @var string 验证是否大于等于某个值 */
    public const EGT = 'egt';
    
    /** @var string 验证是否大于某个值 */
    public const GT = 'gt';
    
    /** @var string 验证是否小于等于某个值 */
    public const ELT = 'elt';
    
    /** @var string 验证是否小于某个值 */
    public const LT = 'lt';
    
    /** @var string 验证是否等于某个值 */
    public const EG = 'eg';
    
    /** @var string 验证是否在范围内 */
    public const IN = 'in';
    
    /** @var string 验证是否不在某个范围 */
    public const NOT_IN = 'notIn';
    
    /** @var string 验证是否在某个区间 */
    public const BETWEEN = 'between';
    
    /** @var string 验证是否不在某个区间 */
    public const NOT_BETWEEN = 'between';
    
    /** @var string 验证数据长度 */
    public const LENGTH = 'length';
    
    /** @var string 验证数据最大长度 */
    public const MAX = 'max';
    
    /** @var string 验证数据最小长度 */
    public const MIN = 'min';
    
    /** @var string 验证日期 */
    public const AFTER = 'after';
    
    /** @var string 验证日期 */
    public const BEFORE = 'before';
    
    /** @var string 验证有效期 */
    public const EXPIRE = 'expire';
    
    /** @var string 验证IP许可 */
    public const ALLOW_IP = 'allowIp';
    
    /** @var string 验证IP禁用 */
    public const DENY_IP = 'denyIp';
    
    /** @var string 使用正则验证数据 */
    public const REGEX = 'regex';
    
    /** @var string 验证表单令牌 */
    public const TOKEN = 'token';
    
    /** @var string 验证字段值是否为有效格式 */
    public const IS      = 'is';
    
    /** @var string 验证字段必须 */
    public const REQUIRE = 'require';
    
    /** @var string 验证字段值是否为数字 */
    public const IS_NUMBER = 'number';
    
    /** @var string 验证字段值是否为数组 */
    public const IS_ARRAY = 'array';
    
    /** @var string 验证字段值是否为整形 */
    public const IS_INTEGER = 'integer';
    
    /** @var string 验证字段值是否为浮点数 */
    public const IS_FLOAT = 'float';
    
    /** @var string 验证字段值是否为手机号 */
    public const IS_MOBILE = 'mobile';
    
    /** @var string 验证字段值是否为身份证号码 */
    public const IS_ID_CARD = 'idCard';
    
    /** @var string 验证字段值是否为中文 */
    public const IS_CHS = 'chs';
    
    /** @var string 验证字段值是否为中文字母及下划线 */
    public const IS_CHS_DASH = 'chsDash';
    
    /** @var string 验证字段值是否为中文和字母 */
    public const IS_CHS_ALPHA = 'chsAlpha';
    
    /** @var string 验证字段值是否为中文字母和数字 */
    public const IS_CHS_ALPHA_NUM = 'chsAlphaNum';
    
    /** @var string 验证字段值是否为有效格式 */
    public const IS_DATE = 'date';
    
    /** @var string 验证字段值是否为布尔值 */
    public const IS_BOOL = 'bool';
    
    /** @var string 验证字段值是否为字母 */
    public const IS_ALPHA = 'alpha';
    
    /** @var string 验证字段值是否为字母和下划线 */
    public const IS_ALPHA_DASH = 'alphaDash';
    
    /** @var string 验证字段值是否为字母和数字 */
    public const IS_ALPHA_NUM = 'alphaNum';
    
    /** @var string 验证字段值是否为yes, on, 或是 1 */
    public const IS_ACCEPTED = 'accepted';
    
    /** @var string 验证字段值是否为有效邮箱格式 */
    public const IS_EMAIL = 'email';
    
    /** @var string 验证字段值是否为有效URL地址 */
    public const IS_URL = 'url';
    
    /** @var string 验证是否为合格的域名或者IP */
    public const ACTIVE_URL = 'activeUrl';
    
    /** @var string 验证是否有效IP */
    public const IP = 'ip';
    
    /** @var string 验证文件后缀 */
    public const FILE_EXT = 'fileExt';
    
    /** @var string 验证文件类型 */
    public const FILE_MIME = 'fileMime';
    
    /** @var string 验证文件大小 */
    public const FILE_SIZE = 'fileSize';
    
    /** @var string 验证图像文件 */
    public const IMAGE = 'image';
    
    /** @var string 验证请求类型 */
    public const METHOD = 'method';
    
    /** @var string 验证时间和日期是否符合指定格式 */
    public const DATE_FORMAT = 'dateFormat';
    
    /** @var string 验证是否唯一 */
    public const UNIQUE = 'unique';
    
    /** @var string 使用行为类验证 */
    public const BEHAVIOR = 'behavior';
    
    /** @var string 使用filter_var方式验证 */
    public const FILTER = 'filter';
    
    /** @var string 验证某个字段等于某个值的时候必须 */
    public const REQUIRE_IF = 'requireIf';
    
    /** @var string 通过回调方法验证某个字段是否必须 */
    public const REQUIRE_CALLBACK = 'requireCallback';
    
    /** @var string 验证某个字段有值的情况下必须 */
    public const REQUIRE_WITH = 'requireWith';
    
    /** @var string 必须验证 */
    public const MUST = 'must';
    
    /** @var string 自定义回调验证 */
    public const CLOSURE = 'closure';
    
    /** @var string 是否英文数字下划线组合，且必须是英文开头 */
    public const IS_FIRST_ALPHA_NUM_DASH = 'firstAlphaNumDash';
    
    private string $name;
    
    private mixed  $rule;
    
    private string $msg;
    
    
    /**
     * 构造函数
     * @param string $name 验证规则名称
     * @param mixed  $rule 验证规则值
     * @param string $msg 验证消息文案
     */
    public function __construct(string $name, mixed $rule = null, string $msg = '')
    {
        if ($name == self::IS_FIRST_ALPHA_NUM_DASH) {
            $name = self::REGEX;
            $rule = '/^[a-zA-Z]+[a-zA-Z0-9_]*$/';
            $msg  = $msg === '' ? ':attribute必须是英文数字下划线组合，且必须是英文开头' : $msg;
        }
        
        $this->msg  = $msg;
        $this->rule = $rule;
        $this->name = $name;
    }
    
    
    /**
     * 获取验证规则名称
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    
    /**
     * 获取验证规则值
     * @return mixed
     */
    public function getRule() : mixed
    {
        return $this->rule;
    }
    
    
    /**
     * 获取验证消息文案
     * @return string
     */
    public function getMsg() : string
    {
        return $this->msg;
    }
}