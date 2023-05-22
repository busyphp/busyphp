<?php
declare(strict_types = 1);

namespace BusyPHP\command\make;

use think\console\Input;
use think\console\Output;

abstract class Make extends \think\console\command\Make
{
    /** @var string */
    protected string $name;
    
    
    protected function execute(Input $input, Output $output)
    {
        $this->name = trim($input->getArgument('name'));
        
        return parent::execute($input, $output);
    }
    
    
    protected function buildClass(string $name)
    {
        return str_replace([
            '{%datetime%}',
            '{%year%}',
            '{%authorName%}',
            '{%authorEmail%}',
            '{%copyright%}',
        ], [
            date('Y/m/d H:i:s'),
            date('Y'),
            $this->app->config->get('console.make.author_name') ?: 'busy^life',
            $this->app->config->get('console.make.author_email') ?: 'busy.life@qq.com',
            $this->app->config->get('console.make.copyright') ?: '2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.'
        ], parent::buildClass($name));
    }
}