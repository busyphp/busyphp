<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\common;

use BusyPHP\App;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use BusyPHP\traits\ContainerDefine;
use LogicException;
use think\db\exception\DbException;
use think\exception\InvalidArgumentException;
use think\Request;

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
     * @param Model|null $model
     */
    public function __construct($model)
    {
        $this->model   = $model;
        $this->app     = App::getInstance();
        $this->request = $this->app->request;
    }
    
    
    /**
     * 获取操作模型
     * @return Model
     */
    protected function getModel() : Model
    {
        if (!$this->model) {
            throw new LogicException('未指定操作模型');
        }
        
        return $this->model;
    }
    
    
    /**
     * 排序
     * @param array<int|string, int>|string $data 排序的数据或http请求参数名称 <p>
     * <pre>array(
     *      id => number,
     *      id1 => number1
     * )</pre>
     * </p>
     * @param string|Entity                 $sortField 排序的字段，默认为 sort
     * @param string|Entity                 $pkField 主键字段，默认为当前模型主键
     * @return int
     * @throws DbException
     */
    public function sort(string|array $data, string|Entity $sortField = 'sort', string|Entity $pkField = '') : int
    {
        $sortField  = Entity::parse($sortField);
        $pkField    = Entity::parse($pkField) ?: $this->getModel()->getPk();
        $data       = is_string($data) ? $this->request->param("$data/a", [], 'intval') : $data;
        $updateData = [];
        foreach ($data as $id => $value) {
            $updateData[] = [
                $pkField   => $id,
                $sortField => $value,
            ];
        }
        
        return $this->getModel()->updateAll($updateData, $pkField);
    }
    
    
    /**
     * 批处理
     * @param string|array  $data 处理的数据或HTTP请求参数名称
     * @param string|bool   $empty 是否检测批处理数据为空或批处理数据为空的错误提示
     * @param callable|null $callback 自定义处理回调，默认为执行删除
     * @return array
     * @throws DbException
     */
    public function batch(string|array $data, string|bool $empty = true, callable $callback = null) : array
    {
        $data = is_string($data) ? $this->request->param("$data/a", [], 'trim') : $data;
        if ($empty && !$data) {
            throw new InvalidArgumentException((is_bool($empty) || !$empty) ? '缺少删除条件' : $empty);
        }
        
        $result = [];
        if (!is_callable($callback)) {
            if ($this->model) {
                foreach ($data as $id) {
                    $result[$id] = $this->model->delete($id);
                }
            }
        } else {
            foreach ($data as $index => $id) {
                $result[$id] = call_user_func($callback, $id, $index);
            }
        }
        
        return $result;
    }
    
    
    /**
     * 实例化
     * @param Model|null $model
     * @return static
     */
    public static function init(?Model $model = null) : static
    {
        return self::makeContainer([$model], true);
    }
}