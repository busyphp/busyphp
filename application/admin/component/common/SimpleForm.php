<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\common;

use BusyPHP\App;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use BusyPHP\Request;
use BusyPHP\traits\ContainerDefine;
use think\db\exception\DbException;

/**
 * 常规表单操作
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/21 21:01 SimpleForm.php $
 */
class SimpleForm implements ContainerInterface
{
    use ContainerDefine;
    
    /**
     * @var Model
     */
    protected $model;
    
    /**
     * @var App
     */
    protected $app;
    
    /**
     * @var Request
     */
    protected $request;
    
    
    /**
     * @inheritDoc
     */
    public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * 构造函数
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model   = $model;
        $this->app     = App::getInstance();
        $this->request = $this->app->request;
    }
    
    
    /**
     * 排序
     * @param array<int|string, int>|string $data 排序的数据或http请求参数名称 <p>
     * <pre>array(
     *      id => number,
     *      id1 => number1
     * )</pre>
     * </p>
     * @param string                        $sortField 排序的字段，默认为 sort
     * @param string                        $pkField 主键字段，默认为当前模型主键
     * @return int
     * @throws DbException
     */
    public function sort($data, $sortField = 'sort', $pkField = '') : int
    {
        $sortField = Entity::parse($sortField);
        $pkField   = Entity::parse($pkField) ?: $this->model->getPk();
        $data      = is_string($data) ? $this->request->param("$data/list", [], 'intval') : $data;
        
        $updateData = [];
        foreach ($data as $id => $value) {
            $updateData[] = [
                $pkField   => $id,
                $sortField => $value,
            ];
        }
        
        return $this->model->updateAll($updateData, $pkField);
    }
    
    
    /**
     * 实例化
     * @param Model $model
     * @return static
     */
    public static function init(Model $model) : static
    {
        return self::makeContainer([$model], true);
    }
}