<?php

namespace BusyPHP\helper;

use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use ReflectionClass;
use ReflectionException;
use think\exception\HttpResponseException;
use think\response\View;

/**
 * 模型辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/10/2 9:11 AM ModelHelper.php $
 */
class ModelHelper
{
    /**
     * 构建模型
     * @param Model|string $model
     * @return array{get_by: array, get_field_by:array, get_info_by: array, find_info_by:array, get_extend_info_by: array, find_extend_info_by: array, where_or: array, where: array, common: array, field_static: array, field_property: array, field_setter: array}
     * @throws ReflectionException
     */
    public static function build($model) : array
    {
        if (!is_subclass_of($model, Model::class)) {
            throw new ClassNotExtendsException($model, Model::class);
        }
        
        if (is_string($model)) {
            $model = new $model();
        }
        
        $class                = new ReflectionClass($model);
        $bindParseClass       = ClassHelper::getAbsoluteClassname(ClassHelper::getPropertyValue($model, 'bindParseClass', true, $class));
        $bindParseExtendClass = ClassHelper::getAbsoluteClassname(ClassHelper::getPropertyValue($model, 'bindParseExtendClass', true, $class));
        $entityClass          = ClassHelper::getAbsoluteClassname(Entity::class);
        $pk                   = $model->getPk();
        $pkType               = 'mixed';
        $getByList            = [];
        $getFieldByList       = [];
        $getInfoByList        = [];
        $getExtendInfoByList  = [];
        $findInfoByList       = [];
        $findExtendInfoByList = [];
        $whereOrList          = [];
        $whereList            = [];
        $fieldStaticList      = [];
        $fieldPropertyList    = [];
        $fieldSetterList      = [];
        
        
        foreach ($model->getFields() as $field) {
            $method  = StringHelper::studly($field['name']);
            $name    = StringHelper::camel($field['name']);
            $type    = static::castFieldType($field['type']);
            $comment = ClassHelper::replaceMethodDocSpecialStr($field['comment'] ?: $field['name']);
            if ($field['name'] == $pk) {
                $pkType = $type;
            }
            
            $getByList[]         = sprintf('@method array|null getBy%s(%s $%s)', $method, $type, $name);
            $getFieldByList[]    = sprintf('@method mixed getFieldBy%s(%s $%s, string|%s $field, mixed $default = null)', $method, $type, $name, $entityClass);
            $whereOrList[]       = sprintf('@method $this whereOr%s(mixed $op, mixed $condition = null, array $bind = [])', $method);
            $whereList[]         = sprintf('@method $this where%s(mixed $op, mixed $condition = null, array $bind = [])', $method);
            $fieldStaticList[]   = sprintf('@method static %s %s(mixed $op = null, mixed $condition = null) %s', $entityClass, $name, $comment);
            $fieldPropertyList[] = sprintf('/**%s * %s%s * @var %s%s */%spublic $%s;', PHP_EOL, $comment, PHP_EOL, $type, PHP_EOL, PHP_EOL, $name);
            $fieldSetterList[]   = sprintf('@method $this set%s(%s $%s) 设置%s', $method, $type, $name, $comment);
            
            if ($bindParseClass) {
                $getInfoByList[]  = sprintf('@method %s getInfoBy%s(%s $%s, string $notFoundMessage = null)', $bindParseClass, $method, $type, $name);
                $findInfoByList[] = sprintf('@method %s|null findInfoBy%s(%s $%s, string $notFoundMessage = null)', $bindParseClass, $method, $type, $name);
            }
            
            if ($bindParseExtendClass) {
                $getExtendInfoByList[]  = sprintf('@method %s getExtendInfoBy%s(%s $%s, string $notFoundMessage = null)', $bindParseExtendClass, $method, $type, $name);
                $findExtendInfoByList[] = sprintf('@method %s|null findExtendInfoBy%s(%s $%s, string $notFoundMessage = null)', $bindParseExtendClass, $method, $type, $name);
            }
        }
        
        $commonList = [];
        if ($bindParseClass) {
            $commonList[] = sprintf('@method %s getInfo(%s $%s, string $notFoundMessage = null)', $bindParseClass, $pkType, $pk);
            $commonList[] = sprintf('@method %s|null findInfo(%s $%s = null, string $notFoundMessage = null)', $bindParseClass, $pkType, $pk);
            $commonList[] = sprintf('@method %s[] selectList()', $bindParseClass);
            $commonList[] = sprintf('@method %s[] buildListWithField(array $values, string|%s $key = null, string|%s $field = null)', $bindParseClass, $entityClass, $entityClass);
        }
        if ($bindParseExtendClass) {
            $commonList[] = sprintf('@method %s getExtendInfo(%s $%s, string $notFoundMessage = null)', $bindParseClass, $pkType, $pk);
            $commonList[] = sprintf('@method %s|null findExtendInfo(%s $%s = null, string $notFoundMessage = null)', $bindParseClass, $pkType, $pk);
            $commonList[] = sprintf('@method %s[] selectExtendList()', $bindParseExtendClass);
            $commonList[] = sprintf('@method %s[] buildExtendListWithField(array $values, string|%s $key = null, string|%s $field = null)', $bindParseExtendClass, $entityClass, $entityClass);
        }
        
        return [
            'get_by'              => $getByList,
            'get_field_by'        => $getFieldByList,
            'get_info_by'         => $getInfoByList,
            'find_info_by'        => $findInfoByList,
            'get_extend_info_by'  => $getExtendInfoByList,
            'find_extend_info_by' => $findExtendInfoByList,
            'where_or'            => $whereOrList,
            'where'               => $whereList,
            'common'              => $commonList,
            'field_static'        => $fieldStaticList,
            'field_property'      => $fieldPropertyList,
            'field_setter'        => $fieldSetterList,
        ];
    }
    
    
    /**
     * 将mysql类型转为php类型
     * @param $type
     * @return string
     */
    public static function castFieldType($type) : string
    {
        $type = strtolower($type);
        switch (true) {
            case 0 === stripos($type, 'int'):
            case 0 === stripos($type, 'tinyint'):
            case 0 === stripos($type, 'smallint'):
            case 0 === stripos($type, 'mediumint'):
            case 0 === stripos($type, 'bigint'):
            case 0 === stripos($type, 'serial'):
                return 'int';
            case 0 === stripos($type, 'decimal'):
            case 0 === stripos($type, 'float'):
            case 0 === stripos($type, 'double'):
                return 'float';
            default:
                return 'string';
        }
    }
    
    
    /**
     * 打印模型支持的字段虚拟方法/属性
     * @param Model|string $model
     * @return void
     * @throws ReflectionException
     */
    public static function printField($model) : void
    {
        $builds = self::build($model);
        $class  = is_string($model) ? $model : get_class($model);
        static::printResponse(sprintf('类 "%s" 结构', $class), [
            [
                'name'    => '虚拟 Static 方法',
                'content' => implode(PHP_EOL . '* ', $builds['field_static'])
            ],
            [
                'name'    => '虚拟 Setter 方法',
                'content' => implode(PHP_EOL . '* ', $builds['field_setter'])
            ],
            [
                'name'    => '实体属性',
                'content' => implode(PHP_EOL, $builds['field_property'])
            ],
            [
                'name'    => '所有虚拟方法',
                'content' => implode(PHP_EOL . '* ', array_merge($builds['field_static'], $builds['field_setter']))
            ],
        ]);
    }
    
    
    /**
     * 打印模型支持的虚拟方法
     * @param Model|string $model
     * @throws ReflectionException
     */
    public static function printModel($model) : void
    {
        $builds = self::build($model);
        $class  = is_string($model) ? $model : get_class($model);
        static::printResponse(sprintf('类 "%s" 结构', $class), [
            [
                'name'    => '虚拟通用方法',
                'content' => implode(PHP_EOL . '* ', $builds['common'])
            ],
            [
                'name'    => '虚拟 getBy 方法',
                'content' => implode(PHP_EOL . '* ', $builds['get_by'])
            ],
            [
                'name'    => '虚拟 getFieldBy 方法',
                'content' => implode(PHP_EOL . '* ', $builds['get_field_by'])
            ],
            [
                'name'    => '虚拟 getInfoBy 方法',
                'content' => implode(PHP_EOL . '* ', $builds['get_info_by'])
            ],
            [
                'name'    => '虚拟 findInfoBy 方法',
                'content' => implode(PHP_EOL . '* ', $builds['find_info_by'])
            ],
            [
                'name'    => '虚拟 getExtendInfoBy 方法',
                'content' => implode(PHP_EOL . '* ', $builds['get_extend_info_by'])
            ],
            [
                'name'    => '虚拟 findExtendInfoBy 方法',
                'content' => implode(PHP_EOL . '* ', $builds['find_extend_info_by'])
            ],
            [
                'name'    => '虚拟 whereOr 方法',
                'content' => implode(PHP_EOL . '* ', $builds['where_or'])
            ],
            [
                'name'    => '虚拟 where 方法',
                'content' => implode(PHP_EOL . '* ', $builds['where'])
            ],
            [
                'name'    => '所有虚拟方法',
                'content' => implode(PHP_EOL . '* ', array_merge($builds['common'], $builds['get_by'], $builds['get_field_by'], $builds['get_info_by'], $builds['find_info_by'], $builds['get_extend_info_by'], $builds['find_extend_info_by'], $builds['where_or'], $builds['where']))
            ],
        ]);
    }
    
    
    /**
     * 响应打印
     * @param string $title
     * @param array  $list
     */
    protected static function printResponse(string $title, array $list) : void
    {
        /** @var View $view */
        $view = View::create(__DIR__ . '/../../assets/template/model_helper_print.html', 'view');
        $view->assign('title', $title);
        $view->assign('list', $list);
        throw new HttpResponseException($view);
    }
}