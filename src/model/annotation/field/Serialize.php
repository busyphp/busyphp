<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\field;

use Attribute;

/**
 * 字段Array转Serialize注解类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/12/15 17:08 Serialize.php $
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Serialize extends Format
{
    /**
     * @inheritDoc
     */
    public function encode(mixed $data) : string
    {
        if ($this->serialized($data)) {
            return $data;
        }
        
        return serialize($data);
    }
    
    
    /**
     * @inheritDoc
     */
    public function decode(string $data) : mixed
    {
        if (!$this->serialized($data)) {
            return $data;
        }
        
        return unserialize($data);
    }
    
    
    /**
     * 是否已序列化
     * @param string $data
     * @return bool
     */
    protected function serialized(mixed $data) : bool
    {
        if (!is_string($data) || !$data) {
            return false;
        }
        
        return str_starts_with($data, 'O:') || str_starts_with($data, 'a:') || str_starts_with($data, 's:') || str_starts_with($data, 'b:') || str_starts_with($data, 'i:') || str_starts_with($data, 'd:') || str_starts_with($data, 'N;');
    }
}