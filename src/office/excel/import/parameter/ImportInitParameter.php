<?php

namespace BusyPHP\office\excel\import\parameter;

use BusyPHP\exception\MethodNotFoundException;
use BusyPHP\office\excel\Import;
use BusyPHP\office\excel\import\ImportColumn;
use BusyPHP\office\excel\import\interfaces\ImportInterface;
use Closure;

/**
 * 导入接口初始化导入配置参数
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/9 16:18 ImportConfig.php $
 * @method $this columns(array $columns) 设置导入列配置
 * @method $this add(ImportColumn $column) 添加导入列配置
 * @method $this start(int $row) 设置从第几行开始读
 * @method $this sheet(int $index) 设置读取的工作集下标
 * @method $this row(?Closure $callback) 设置行处理回调
 * @method $this list(?Closure $callback) 设置总数据处理回调
 * @see ImportInterface::initExcelImport()
 */
class ImportInitParameter
{
    /**
     * 允许的方法
     * @var array
     */
    protected static array $allowMethods = [
        'columns',
        'add',
        'start',
        'sheet',
        'row',
        'list',
    ];
    
    /**
     * @var Import
     */
    protected Import $import;
    
    
    /**
     * 构造函数
     * @param Import $import
     */
    public function __construct(Import $import)
    {
        $this->import = $import;
    }
    
    
    public function __call(string $name, array $arguments)
    {
        if (!in_array($name, self::$allowMethods)) {
            throw new MethodNotFoundException($this, $name);
        }
        
        $this->import->$name(...$arguments);
        
        return $this;
    }
}