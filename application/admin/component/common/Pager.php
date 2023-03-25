<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\common;

use think\paginator\driver\Bootstrap;

/**
 * 后台分页
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/15 19:37 Pager.php $
 */
class Pager extends Bootstrap
{
    protected function getPreviousButton(string $text = '') : string
    {
        return parent::getPreviousButton($this->simple ? '<i class="fa fa-chevron-circle-left"></i> 上一页' : '<i class="fa fa-angle-double-left"></i>');
    }
    
    
    protected function getNextButton(string $text = '') : string
    {
        return parent::getNextButton($this->simple ? '下一页 <i class="fa fa-chevron-circle-right"></i>' : '<i class="fa fa-angle-double-right"></i>');
    }
    
    
    protected function getLinks() : string
    {
        if ($this->simple) {
            return '';
        }
        
        $side   = 2;
        $window = $side * 2;
        $last   = null;
        $slider = null;
        
        if ($this->lastPage < $window + 6) {
            $first = $this->getUrlRange(1, $this->lastPage);
        } elseif ($this->currentPage <= $window) {
            $first = $this->getUrlRange(1, $window + 1);
            $last  = $this->getUrlRange($this->lastPage, $this->lastPage);
        } elseif ($this->currentPage > ($this->lastPage - $window)) {
            $first = $this->getUrlRange(1, 1);
            $last  = $this->getUrlRange($this->lastPage - ($window + 1), $this->lastPage);
        } else {
            $first  = $this->getUrlRange(1, 1);
            $slider = $this->getUrlRange($this->currentPage - $side, $this->currentPage + $side);
            $last   = $this->getUrlRange($this->lastPage, $this->lastPage);
        }
        
        $html = '';
        
        if ($first) {
            $html .= $this->getUrlLinks($first);
        }
        
        if ($slider) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($slider);
        }
        
        if ($last) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($last);
        }
        
        return $html;
    }
    
    
    public function render() : string
    {
        if (!$this->hasPages() || $this->isEmpty()) {
            return '';
        }
        
        $page  = parent::render();
        $start = ($this->currentPage() - 1) * $this->listRows();
        $end   = $start + $this->getCollection()->count();
        $start = $start == 0 ? 1 : $start;
        
        if ($this->simple) {
            return <<<HTML
<div class="ba-pagination busy--simple clearfix">
    <div class="busy--info">
        <span class="busy--step">
            当前<span class="busy--start">$start</span><span class="busy--space">~</span><span class="busy--last">$end</span>条
        </span>
        <span class="busy--current">第<span>{$this->currentPage()}</span>页</span>
    </div>
    $page
</div>
HTML;
        } else {
            return <<<HTML
<div class="ba-pagination busy--full clearfix">
    <div class="busy--info">
        <span class="busy--step">
            当前<span class="busy--start">$start</span><span class="busy--space">~</span><span class="busy--last">$end</span>条
        </span>
        <span class="busy--total">共<span>$this->total</span>条</span>
    </div>
    $page
</div>
HTML;
        }
    }
}