<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\field;

use Attribute;
use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\Model;
use BusyPHP\model\Field;

/**
 * 为字段结构类绑定模型注解
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/20 09:52 BindModel.php $
 * @see Field
 */
#[Attribute(Attribute::TARGET_CLASS)]
class BindModel
{
    private string $model;
    
    private string $alias;
    
    
    /**
     * 构造函数
     * @param class-string<Model> $model 模型类名
     * @param string              $alias JOIN 别名
     */
    public function __construct(string $model, string $alias = '')
    {
        if (!is_subclass_of($model, Model::class)) {
            throw new ClassNotExtendsException($model, Model::class);
        }
        
        $this->model = $model;
        $this->alias = $alias;
    }
    
    
    /**
     * @return string
     */
    public function getAlias() : string
    {
        return $this->alias;
    }
    
    
    /**
     * @return class-string<Model>
     */
    public function getModel() : string
    {
        return $this->model;
    }
}