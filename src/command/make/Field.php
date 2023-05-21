<?php
declare(strict_types = 1);

namespace BusyPHP\command\make;

/**
 * MakeField
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/5/21 23:33 Field.php $
 */
class Field extends Model
{
    protected $type = "Field";
    
    
    protected function configure()
    {
        parent::configure();
        
        $this->setName('bp:make-field')
            ->setDescription('Create a new BusyPHP model field class');
    }
    
    
    protected function buildClass(string $name)
    {
        return str_replace('{%modelClass%}', substr($name, 0, -5), parent::buildClass($name));
    }
    
    
    protected function getStub() : string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'field.stub';
    }
    
    
    protected function getClassName(string $name) : string
    {
        return parent::getClassName($name) . 'Field';
    }
}