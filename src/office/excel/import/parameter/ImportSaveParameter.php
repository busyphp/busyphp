<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\import\parameter;

use BusyPHP\office\excel\Import;
use BusyPHP\office\excel\import\interfaces\ImportInterface;
use Throwable;

/**
 * 导入接口保存导入的数据参数
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/9 16:31 ImportSaveParameter.php $
 * @see ImportInterface::saveExcelImport()
 */
class ImportSaveParameter
{
    /**
     * @var Import
     */
    protected Import $import;
    
    /**
     * 要处理的数据
     * @var array
     */
    public array $list;
    
    
    /**
     * @param Import $import
     * @param array  $list
     */
    public function __construct(Import $import, array $list)
    {
        $this->import = $import;
        $this->list   = $list;
    }
    
    
    /**
     * 触发数据保存完成事件
     * @param bool           $success 是否成功
     * @param mixed          $data 完成的单条数据或完成的批次数据集合
     * @param Throwable|null $error 失败异常
     * @return static
     */
    public function triggerSaved(bool $success, mixed $data, Throwable $error = null) : static
    {
        $this->import->triggerSaved(...func_get_args());
        
        return $this;
    }
}