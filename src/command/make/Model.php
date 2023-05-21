<?php
declare(strict_types = 1);

namespace BusyPHP\command\make;

use BusyPHP\helper\ConsoleHelper;
use think\console\Input;
use think\console\Output;

/**
 * MakeModel
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/5/21 21:56 Model.php $
 */
class Model extends Make
{
    protected $type = "Model";
    
    
    protected function configure()
    {
        parent::configure();
        
        $this->setName('bp:make-model')
            ->setDescription('Create a new BusyPHP model class');
    }
    
    
    protected function execute(Input $input, Output $output)
    {
        $result = parent::execute($input, $output);
        
        // 生成field
        if (get_class($this) == self::class) {
            $command = ConsoleHelper::makeScriptCommand(['bp:make-field', $this->name]);
            $process = ConsoleHelper::makeShell(ConsoleHelper::makeShellCommand($command));
            $process->run(function($type, $line) {
                $this->output->write($line);
            });
        }
        
        return $result;
    }
    
    
    protected function getStub() : string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'model.stub';
    }
    
    
    protected function buildClass(string $name)
    {
        if (get_class($this) == self::class) {
            return str_replace('{%fieldClass%}', $name . 'Field', parent::buildClass($name));
        }
        
        return parent::buildClass($name);
    }
    
    
    protected function getPathName(string $name) : string
    {
        return $this->app->getRootPath() . ltrim(str_replace('\\', '/', $name), '/') . '.php';
    }
    
    
    protected function getNamespace(string $app) : string
    {
        return 'core\\model' . ($app ? '\\' . $app : '');
    }
}