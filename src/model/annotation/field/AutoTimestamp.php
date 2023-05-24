<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\field;

use Attribute;
use BusyPHP\model\Field;

/**
 * 定义自动写入创建/更新时间注解
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/17 17:32 AutoTimestamp.php $
 * @see Field
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AutoTimestamp
{
    public const TYPE_INT       = 'int';
    
    public const TYPE_DATE      = 'date';
    
    public const TYPE_TIMESTAMP = 'timestamp';
    
    public const TYPE_DATETIME  = 'datetime';
    
    private string|bool $type;
    
    private string      $format;
    
    private bool        $updateTimeSync;
    
    
    /**
     * 构造函数
     * @param string|bool $type 时间类型
     * @param string      $format 格式化
     * @param bool        $updateTimeSync 更新时间在create时是否同步createTime
     */
    public function __construct(string|bool $type = true, string $format = 'Y-m-d H:i:s', bool $updateTimeSync = true)
    {
        $this->type           = $type;
        $this->format         = $format;
        $this->updateTimeSync = $updateTimeSync;
    }
    
    
    /**
     * @return bool
     */
    public function isUpdateTimeSync() : bool
    {
        return $this->updateTimeSync;
    }
    
    
    /**
     * @return bool|string
     */
    public function getType() : bool|string
    {
        return $this->type;
    }
    
    
    /**
     * @return string
     */
    public function getFormat() : string
    {
        return $this->format;
    }
}