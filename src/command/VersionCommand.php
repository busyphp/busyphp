<?php
declare (strict_types = 1);

namespace BusyPHP\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 获取版本号
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/12 下午6:43 下午 Version.php $
 */
class VersionCommand extends Command
{
    protected function configure()
    {
        $this->setName('bp:version')->setDescription('BusyPHP get version command');
    }
    
    
    protected function execute(Input $input, Output $output)
    {
        $output->info("V{$this->app->getFrameworkVersion()}");
    }
}