<?php
declare(strict_types = 1);

namespace BusyPHP\interfaces;

use BusyPHP\model\Setting;
use Psr\Log\LoggerInterface;

/**
 * SettingInterface
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/24 13:34 SettingInterface.php $
 * @see Setting
 */
interface SettingInterface
{
    /**
     * 构造函数
     * @param LoggerInterface|null $logger 日志接口
     * @param string               $connect 连接标识
     * @param bool                 $force 是否强制重连
     */
    public function __construct(LoggerInterface $logger = null, string $connect = '', bool $force = false);
    
    
    /**
     * 设置数据
     * @param string $name 数据名称
     * @param array  $data 数据内容
     */
    public function setSettingData(string $name, array $data);
    
    
    /**
     * 获取数据
     * @param string $name 数据名称
     * @param bool   $force 是否强制获取数据
     * @return array 数据内容
     */
    public function getSettingData(string $name, bool $force = false) : array;
}