<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\plugin\lists;

use BusyPHP\App;
use BusyPHP\model\Field;
use BusyPHP\model\Map;
use think\Paginator;

/**
 * 通用列表查询输出结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/8 下午上午11:05 ListStruct.php $
 * @property string $page 渲染好的分页
 */
class ListSelectResult extends Map
{
    /**
     * @var array|Field[]
     */
    public $list;
    
    /**
     * 总条数
     * @var int
     */
    public $total;
    
    /**
     * 分页对象
     * @var Paginator
     */
    public $paginator;
    
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
     * ListSelectResult constructor.
     * @param array  $list 数据
     * @param int    $listRows 每页显示条数
     * @param int    $currentPage 当前第几页
     * @param int    $total 总条数
     * @param bool   $simple 是否简洁模式
     * @param string $drive 分页驱动
     */
    public function __construct(array $list = [], int $listRows = 30, int $currentPage = 1, int $total = 0, bool $simple = false, ?string $drive = null)
    {
        App::getInstance()->bind(Paginator::class, $drive ?: ListSelectPaginator::class);
        
        // 全部数据
        if ($listRows <= 0) {
            $listRows = count($list);
            $total    = $listRows;
        }
        
        $this->paginator = Paginator::make($list, $listRows, $currentPage, $simple ? null : $total, $simple, [
            'query' => App::getInstance()->request->get(),
            'path'  => (string) url(),
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
    public function render() : string
    {
        if (!$this->list) {
            return '';
        }
        
        $start = ($this->paginator->currentPage() - 1) * $this->paginator->listRows();
        $end   = $start + $this->paginator->getCollection()->count();
        $start = $start == 0 ? 1 : $start;
        
        if ($this->simple) {
            return <<<HTML
<div class="busy-admin-pagination busy--simple clearfix"><div class="busy--info"><span class="busy--step">当前<span class="busy--start">{$start}</span><span class="busy--space">~</span><span class="busy--last">{$end}</span>条</span><span class="busy--current">第<span>{$this->paginator->currentPage()}</span>页</span></div>{$this->paginator->render()}</div>
HTML;
        } else {
            return <<<HTML
<div class="busy-admin-pagination busy--full clearfix"><div class="busy--info"><span class="busy--step">当前<span class="busy--start">{$start}</span><span class="busy--space">~</span><span class="busy--last">{$end}</span>条</span><span class="busy--total">共<span>{$this->total}</span>条</span></div>{$this->paginator->render()}</div>
HTML;
        }
    }
    
    
    public function __get($name)
    {
        if ($name == 'page') {
            return $this->render();
        }
        
        return parent::__get($name);
    }
    
    
    public function __toString()
    {
        return $this->render();
    }
}