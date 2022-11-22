<?php
declare(strict_types = 1);

namespace BusyPHP\helper;

use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\Model;
use BusyPHP\model\Entity;
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
     * @return array{get_by: array, get_field_by:array, get_info_by: array, find_info_by:array, get_extend_info_by: array, find_extend_info_by: array, where_or: array, where: array, common: array, field_static: array, field_public: array, field_public: array, field_setter: array, field_getter: array}
     */
    public static function build($model) : array
    {
        if (!is_subclass_of($model, Model::class)) {
            throw new ClassNotExtendsException($model, Model::class);
        }
        
        if (is_string($model)) {
            $model = new $model();
        }
        
        $bindParseClass       = ClassHelper::getAbsoluteClassname(ClassHelper::getPropertyValueByObject($model, 'bindParseClass'), true);
        $bindParseExtendClass = ClassHelper::getAbsoluteClassname(ClassHelper::getPropertyValueByObject($model, 'bindParseExtendClass'), true);
        $entityClass          = ClassHelper::getAbsoluteClassname(Entity::class, true);
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
        $fieldPublicList      = [];
        $fieldProtectedList   = [];
        $fieldSetterList      = [];
        $fieldGetterList      = [];
        
        foreach ($model->getFields() as $field) {
            $method  = StringHelper::studly($field['name']);
            $name    = StringHelper::camel($field['name']);
            $type    = $model->getFieldType($field['name']);
            $type    = in_array($type, ['date', 'datetime', 'timestamp']) ? 'string' : $type;
            $comment = ClassHelper::replaceMethodDocSpecialStr($field['comment'] ?: $field['name']);
            if ($field['name'] == $pk) {
                $pkType = $type;
            }
            
            $property = <<<PHP
/**
 * %s
 * @var %s
 */
%s $%s;
PHP;
            
            $getByList[]          = sprintf('@method array|null getBy%s(%s $%s)', $method, $type, $name);
            $getFieldByList[]     = sprintf('@method mixed getFieldBy%s(%s $%s, string|%s $field, mixed $default = null)', $method, $type, $name, $entityClass);
            $whereOrList[]        = sprintf('@method $this whereOr%s(mixed $op, mixed $condition = null, array $bind = [])', $method);
            $whereList[]          = sprintf('@method $this where%s(mixed $op, mixed $condition = null, array $bind = [])', $method);
            $fieldStaticList[]    = sprintf('@method static %s %s(mixed $op = null, mixed $condition = null) %s', $entityClass, $name, $comment);
            $fieldPublicList[]    = sprintf($property, $comment, $type, 'public', $name);
            $fieldProtectedList[] = sprintf($property, $comment, $type, 'protected', $name);
            $fieldSetterList[]    = sprintf('@method $this set%s(mixed $%s) 设置%s', $method, $name, $comment);
            $fieldGetterList[]    = sprintf('@method $this get%s() 获取%s', $method, $comment);
            
            if ($bindParseClass) {
                $getInfoByList[]  = sprintf('@method %s getInfoBy%s(%s $%s, string $notFoundMessage = null)', $bindParseClass, $method, $type, $name);
                $findInfoByList[] = sprintf('@method %s|null findInfoBy%s(%s $%s)', $bindParseClass, $method, $type, $name);
            }
            
            if ($bindParseExtendClass) {
                $getExtendInfoByList[]  = sprintf('@method %s getExtendInfoBy%s(%s $%s, string $notFoundMessage = null)', $bindParseExtendClass, $method, $type, $name);
                $findExtendInfoByList[] = sprintf('@method %s|null findExtendInfoBy%s(%s $%s)', $bindParseExtendClass, $method, $type, $name);
            }
        }
        
        $commonList = [];
        if ($bindParseClass) {
            $commonList[] = sprintf('@method %s getInfo(%s $%s, string $notFoundMessage = null)', $bindParseClass, $pkType, $pk);
            $commonList[] = sprintf('@method %s|null findInfo(%s $%s = null)', $bindParseClass, $pkType, $pk);
            $commonList[] = sprintf('@method %s[] selectList()', $bindParseClass);
            $commonList[] = sprintf('@method %s[] buildListWithField(array $values, string|%s $key = null, string|%s $field = null)', $bindParseClass, $entityClass, $entityClass);
        }
        if ($bindParseExtendClass) {
            $commonList[] = sprintf('@method %s getExtendInfo(%s $%s, string $notFoundMessage = null)', $bindParseClass, $pkType, $pk);
            $commonList[] = sprintf('@method %s|null findExtendInfo(%s $%s = null)', $bindParseClass, $pkType, $pk);
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
            'field_public'        => $fieldPublicList,
            'field_protected'     => $fieldProtectedList,
            'field_setter'        => $fieldSetterList,
            'field_getter'        => $fieldGetterList,
        ];
    }
    
    
    /**
     * 打印模型支持的字段虚拟方法/属性
     * @param Model|string $model
     * @return void
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
                'name'    => '虚拟 Getter 方法',
                'content' => implode(PHP_EOL . '* ', $builds['field_getter'])
            ],
            [
                'name'    => 'public 属性',
                'content' => implode(PHP_EOL, $builds['field_public'])
            ],
            [
                'name'    => 'protected 属性',
                'content' => implode(PHP_EOL, $builds['field_protected'])
            ],
            [
                'name'    => '所有虚拟方法',
                'content' => implode(PHP_EOL . '* ', array_merge($builds['field_static'], $builds['field_setter'], $builds['field_getter']))
            ],
        ]);
    }
    
    
    /**
     * 打印模型支持的虚拟方法
     * @param Model|string $model
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