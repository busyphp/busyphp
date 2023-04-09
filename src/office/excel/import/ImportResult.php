<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\import;

/**
 * 导入结果
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/9 18:49 ImportResult.php $
 */
class ImportResult
{
    /**
     * 获得的数据
     * @var array
     */
    public array $list = [];
    
    /**
     * 保存成功数统计
     * @var int
     */
    public int $successTotal = 0;
    
    /**
     * 保存失败数统计
     * @var int
     */
    public int $errorTotal = 0;
    
    
    /**
     * 构造函数
     * @param int $successTotal 保存成功数统计
     * @param int $errorTotal 保存失败数统计
     */
    public function __construct(int $successTotal, int $errorTotal)
    {
        $this->successTotal = $successTotal;
        $this->errorTotal   = $errorTotal;
    }
}