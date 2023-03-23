<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\field;

use Attribute;
use BusyPHP\model\Field;

/**
 * 字段Array转JSON注解类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/12/15 17:06 Json.php $
 * @see Field
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Json extends Format
{
    private string $default;
    
    private int    $flags;
    
    
    /**
     * @param string $default 默认值
     * @param int    $flags
     */
    public function __construct(string $default = '{}', int $flags = JSON_UNESCAPED_UNICODE)
    {
        $this->flags   = $flags;
        $this->default = $default;
    }
    
    
    /**
     * @param string $default
     * @return Json
     */
    public function setDefault(string $default) : Json
    {
        $this->default = $default;
        
        return $this;
    }
    
    
    /**
     * @return int
     */
    public function getFlags() : int
    {
        return $this->flags;
    }
    
    
    /**
     * @param int $flags
     * @return Json
     */
    public function setFlags(int $flags) : Json
    {
        $this->flags = $flags;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function encode(mixed $data) : string
    {
        if ($data) {
            if (is_array($data) || is_object($data)) {
                return json_encode($data, $this->getFlags());
            }
            
            if (is_string($data) && (str_starts_with($data, '[') || str_starts_with($data, '{'))) {
                return $data;
            }
        }
        
        return $this->default;
    }
    
    
    /**
     * @inheritDoc
     */
    public function decode(string $data) : array
    {
        return json_decode($data, true) ?: [];
    }
}