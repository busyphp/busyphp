<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\field;

use Attribute;
use BusyPHP\model\Field;
use BusyPHP\traits\ContainerDefine;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use think\Container;

/**
 * 字段设置值过滤注解类，用于 {@see Field} 中的虚拟setter方法过滤
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/12/14 15:59 Filter.php $
 * @see Field
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Filter
{
    /**
     * @var callable
     */
    private mixed        $filter;
    
    private mixed        $real;
    
    private array        $args;
    
    private static array $data = [];
    
    
    /**
     * 构造函数
     * @param callable $filter 过滤函数
     * @param mixed    ...$args 参数
     */
    public function __construct(callable $filter, ...$args)
    {
        $this->filter = $filter;
        $this->args   = $args;
    }
    
    
    /**
     * 获取过滤函数
     * @return callable
     */
    public function getFilter() : callable
    {
        if (!isset($this->real)) {
            $filter = $this->filter;
            if (is_array($filter)) {
                $key = $filter[0] . '::' . $filter[1];
                if (!isset(self::$data[$key])) {
                    try {
                        $reflect    = new ReflectionClass($filter[0]);
                        $traitNames = $reflect->getTraitNames();
                        if (in_array(ContainerDefine::class, $traitNames)) {
                            $filter[0] = $filter[0]::class();
                        }
                        
                        if (!$reflect->getMethod($filter[1])->isStatic()) {
                            $filter[0] = Container::getInstance()->make($filter[0]);
                        }
                    } catch (ReflectionException $e) {
                        throw new RuntimeException($e->getMessage());
                    }
                    
                    self::$data[$key] = $filter[0];
                }
                $filter[0] = self::$data[$key];
            }
            
            $this->real = $filter;
        }
        
        return $this->real;
    }
    
    
    /**
     * @return array
     */
    public function getArgs() : array
    {
        return $this->args;
    }
}