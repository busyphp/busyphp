<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\common\SimpleQuery;

use ArrayAccess;
use BusyPHP\App;
use BusyPHP\app\admin\component\common\Pager;
use BusyPHP\app\admin\component\common\SimpleQuery;
use InvalidArgumentException;
use think\Collection;
use think\Container;
use think\facade\Route;
use think\Paginator;

/**
 * SimpleQuery 执行构建 {@see SimpleQuery::build()} 返回结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/14 16:52 SimpleQueryBuildResult.php $
 * @property string $page 构建的分页HTML
 */
class SimpleQueryBuildResult implements ArrayAccess
{
    /**
     * 数据集
     * @var array|Collection
     */
    public $list;
    
    /**
     * 总条数
     * @var int
     */
    public $total;
    
    /**
     * 每页显示条数
     * @var int
     */
    public $limit;
    
    /**
     * 是否简洁分页
     * @var bool
     */
    public $simple;
    
    /**
     * 分页对象
     * @var Paginator
     */
    protected $paginator;
    
    
    /**
     * 构造函数
     * @param array|Collection             $list 数据集
     * @param int                          $limit 每页显示条数
     * @param int                          $page 当前第几页
     * @param int                          $total 总条数
     * @param bool                         $simple 是否简洁模式
     * @param class-string<Paginator>|null $paginator 分页驱动类
     */
    public function __construct($list, int $limit, int $page, int $total, bool $simple = false, string $paginator = null)
    {
        // 全部数据
        if ($limit <= 0) {
            $limit = count($list);
            $total = $limit;
        }
        
        if (!$paginator && !is_subclass_of($paginator, Paginator::class)) {
            $paginator = Pager::class;
        }
        $this->paginator = Container::getInstance()->make($paginator, [
            $list,
            $limit,
            $page,
            $simple ? null : $total,
            $simple,
            [
                'query' => App::getInstance()->request->get(),
                'path'  => (string) Route::buildUrl(),
            ]
        ]);
        $this->list      = $this->paginator->all();
        $this->total     = !$simple ? $this->paginator->total() : 0;
        $this->limit     = $this->paginator->listRows();
        $this->simple    = $simple;
    }
    
    
    /**
     * 渲染分页
     * @return string
     */
    public function page() : string
    {
        return $this->paginator->render();
    }
    
    
    public function __get($name)
    {
        if ($name === 'page') {
            return $this->page();
        }
        
        throw new InvalidArgumentException($name);
    }
    
    
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }
    
    
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if ($offset === 'page') {
            return $this->page();
        }
        
        return $this->{$offset};
    }
    
    
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }
    
    
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->{$offset});
    }
}