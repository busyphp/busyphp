<?php

namespace BusyPHP\office\excel\import\parameter;

use BusyPHP\office\excel\Import;
use BusyPHP\office\excel\import\interfaces\ImportInterface;
use RuntimeException;

/**
 * 导入接口初始化导入配置参数
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/9 16:18 ImportConfig.php $
 * @mixin Import
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
        'on'
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
            throw new RuntimeException(sprintf('不允许使用%s，仅支持: %s', $name, implode(',', self::$allowMethods)));
        }
        
        $this->import->$name(...$arguments);
        
        return $this;
    }
}