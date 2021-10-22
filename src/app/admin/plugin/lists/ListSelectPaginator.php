<?php

namespace BusyPHP\app\admin\plugin\lists;

use think\paginator\driver\Bootstrap;

/**
 * 分页
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/8 下午下午4:00 ListSelectPaginator.php $
 */
class ListSelectPaginator extends Bootstrap
{
    protected function getPreviousButton(string $text = '') : string
    {
        return parent::getPreviousButton($this->simple ? '<i class="fa fa-chevron-circle-left"></i> 上一页' : '<i class="fa fa-angle-double-left"></i>');
    }
    
    
    protected function getNextButton(string $text = '') : string
    {
        return parent::getNextButton($this->simple ? '下一页 <i class="fa fa-chevron-circle-right"></i>' : '<i class="fa fa-angle-double-right"></i>');
    }
    
    
    /**
     * 页码按钮
     * @return string
     */
    protected function getLinks() : string
    {
        if ($this->simple) {
            return '';
        }
        
        $block = [
            'first'  => null,
            'slider' => null,
            'last'   => null,
        ];
        
        $side   = 2;
        $window = $side * 2;
        
        if ($this->lastPage < $window + 6) {
            $block['first'] = $this->getUrlRange(1, $this->lastPage);
        } elseif ($this->currentPage <= $window) {
            $block['first'] = $this->getUrlRange(1, $window + 1);
            $block['last']  = $this->getUrlRange($this->lastPage - 1, $this->lastPage);
        } elseif ($this->currentPage > ($this->lastPage - $window)) {
            $block['first'] = $this->getUrlRange(1, 1);
            $block['last']  = $this->getUrlRange($this->lastPage - ($window + 1), $this->lastPage);
        } else {
            $block['first']  = $this->getUrlRange(1, 1);
            $block['slider'] = $this->getUrlRange($this->currentPage - $side, $this->currentPage + $side);
            $block['last']   = $this->getUrlRange($this->lastPage, $this->lastPage);
        }
        
        $html = '';
        
        if (is_array($block['first'])) {
            $html .= $this->getUrlLinks($block['first']);
        }
        
        if (is_array($block['slider'])) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['slider']);
        }
        
        if (is_array($block['last'])) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['last']);
        }
        
        return $html;
    }
}