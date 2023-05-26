<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\driver\part\exception;

use RuntimeException;

/**
 * 单个分块上传完成
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/5/26 09:12 PartPuttedException.php $
 */
class PartPuttedException extends RuntimeException
{
    private array $result;
    
    
    public function __construct(array $result)
    {
        parent::__construct('单个分块已上传完成', 0);
        
        $this->result = $result;
    }
    
    
    /**
     * @return array
     */
    public function getResult() : array
    {
        return $this->result;
    }
}