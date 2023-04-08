<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\common;

use BusyPHP\App;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use BusyPHP\office\excel\Export;
use BusyPHP\office\excel\export\Sheet;
use BusyPHP\office\excel\export\interfaces\SheetInterface;
use BusyPHP\traits\ContainerDefine;
use Closure;
use LogicException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use think\db\exception\DbException;
use think\exception\InvalidArgumentException;
use think\Request;
use think\Response;
use Throwable;

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
     * @var Model|null
     */
    protected ?Model $model;
    
    /**
     * @var App
     */
    protected App $app;
    
    /**
     * @var Request
     */
    protected Request $request;
    
    
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
     * @return array 批处理返回结果集
     * @throws DbException
     */
    public function batch(string|array $data, string|bool $empty = true, callable $callback = null) : array
    {
        $data = is_string($data) ? $this->request->param("$data/a", [], 'trim') : $data;
        if (($empty || $empty === '') && !$data) {
            throw new InvalidArgumentException((is_bool($empty) || $empty === '') ? '缺少删除条件' : $empty);
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
     * 导出
     * @param Model[]|Sheet[]|SheetInterface[] $sheets 其它模型或工作集
     * @param string                           $filename 导出文件名
     * @param string                           $type 导出类型
     * @param Closure(Spreadsheet):void|null   $handle 导出工作集处理回调
     * @throws Throwable
     */
    public function export(array $sheets = [], string $filename = '', string $type = Export::TYPE_XLSX, ?Closure $handle = null) : Response
    {
        $model  = $this->getModel();
        $export = Export::init($model);
        foreach ($sheets as $item) {
            $export->add($item);
        }
        
        return $export
            ->type($type ?: Export::TYPE_XLSX)
            ->handle($handle)
            ->response($filename === '' ? sprintf("%s_%s", $model->getName(), date('YmdHis')) : $filename);
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