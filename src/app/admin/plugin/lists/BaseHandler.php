<?php

namespace BusyPHP\app\admin\plugin\lists;

use BusyPHP\Model;
use BusyPHP\model\Field;

/**
 * 列表查询回调
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/2 下午下午3:03 BaseHandler.php $
 */
abstract class BaseHandler
{
    /**
     * 字段查询处理
     * @param Model  $model 查询模型
     * @param string $field 查询字段
     * @param string $word 查询关键词，如果是模糊查询，则是已经处理过的关键词
     * @param string $op 查询条件
     * @param string $sourceWord 未处理过的关键词
     * @return string 处理后的字段名称
     */
    public function field($model, string $field, string $word, string $op, string $sourceWord) : string
    {
        return $field;
    }
    
    
    /**
     * 支持数据处理
     * @param Field[]|array $list 要处理的数据
     * @return array|null 处理后的数据
     */
    public function list(array &$list) : ?array
    {
        return null;
    }
}