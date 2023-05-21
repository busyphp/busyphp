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
}