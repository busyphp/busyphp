<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\import\interfaces;

use BusyPHP\office\excel\import\parameter\ImportInitParameter;
use BusyPHP\office\excel\import\parameter\ImportSaveParameter;

/**
 * 导入接口
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/8 20:29 ImportInterface.php $
 */
interface ImportInterface
{
    /**
     * 初始化导入配置
     * @param ImportInitParameter $parameter
     */
    public function initExcelImport(ImportInitParameter $parameter);
    
    
    /**
     * 保存导入的数据
     * @param ImportSaveParameter $parameter
     */
    public function saveExcelImport(ImportSaveParameter $parameter);
}