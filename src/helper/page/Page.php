<?php

namespace BusyPHP\helper\page;

use think\Collection;
use think\Paginator;


/**
 * Bootstrap 分页驱动
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/6 下午12:34 上午 Bootstrap.php $
 */
class Page extends Paginator
{
    /**
     * 是否强制渲染分页
     * @var bool
     */
    protected $forceRender = false;
    
    
    public function __construct($items, int $listRows, int $currentPage = 1, int $total = null, bool $simple = false, array $options = [])
    {
        $options['var_page'] = $options['var_page'] ?? config('route.var_page');
        $options['path']     = $options['path'] ?? self::getCurrentPath();
        $options['query']    = $options['query'] ?? request()->get();
        parent::__construct($items, $listRows, $currentPage, $total, $simple, $options);
        
        $this->setTheme(isset($options['theme']) ? $options['theme'] : []);
        $this->setTemplate(isset($options['template']) ? $options['template'] : []);
    }
    
    
    /**
     * 设置风格
     * @param $theme
     * @return $this
     */
    public function setTheme($theme)
    {
        $this->options['theme'] = array_merge([
            'default' => '<ul class="pagination">%s %s %s</ul>',
            'simple'  => '<ul class="pager">%s %s</ul>'
        ], is_array($theme) ? $theme : []);
        
        return $this;
    }
    
    
    /**
     * 设置模板
     * @param $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->options['template'] = array_merge([
            'prev'     => '<li class="prev %s"><a href="%s">&laquo;</a></li>',
            'next'     => '<li class="next %s"><a href="%s">&raquo;</a></li>',
            'active'   => '<li class="active"><span>%s</span></li>',
            'disabled' => '<li class="disabled"><span>%s</span></li>',
            'link'     => '<li><a href="%s">%s</a></li>',
        ], is_array($template) ? $template : []);
        
        return $this;
    }
    
    
    /**
     * 设置 Query
     * @param array $query
     * @return $this
     */
    public function setQuery($query) : self
    {
        $this->options['query'] = $query;
        
        return $this;
    }
    
    
    /**
     * 设置 Fragment
     * @param string $fragment
     * @return $this
     */
    public function setFragment($fragment) : self
    {
        $this->options['fragment'] = $fragment;
        
        return $this;
    }
    
    
    /**
     * 设置Path
     * @param string $path
     * @return $this
     */
    public function setPath($path) : self
    {
        $this->options['path'] = $path;
        
        return $this;
    }
    
    
    /**
     * 设置varPage
     * @param string $varPage
     * @return $this
     */
    public function setVarPage($varPage)
    {
        $this->options['var_page'] = $varPage;
        
        return $this;
    }
    
    
    /**
     * 设置是否强制渲染分页
     * @param bool $forceRender
     * @return $this
     */
    public function setForceRender(bool $forceRender)
    {
        $this->forceRender = $forceRender;
        
        return $this;
    }
    
    
    /**
     * 上一页按钮
     * @return string
     */
    protected function getPreviousButton() : string
    {
        if ($this->currentPage() <= 1) {
            return sprintf($this->options['template']['prev'], ' disabled', 'javascript:void(0)');
        }
        
        $url = $this->url($this->currentPage() - 1);
        
        return sprintf($this->options['template']['prev'], '', $url);
    }
    
    
    /**
     * 下一页按钮
     * @return string
     */
    protected function getNextButton() : string
    {
        if (!$this->hasMore) {
            return sprintf($this->options['template']['next'], ' disabled', 'javascript:void(0)');
        }
        
        $url = $this->url($this->currentPage() + 1);
        
        return sprintf($this->options['template']['next'], '', $url);
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
            $block['last']  = $this->getUrlRange($this->lastPage, $this->lastPage);
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
    
    
    /**
     * 渲染分页html
     * @return mixed
     */
    public function render()
    {
        if ($this->hasPages() || $this->forceRender) {
            if ($this->simple) {
                return sprintf($this->options['theme']['simple'], $this->getPreviousButton(), $this->getNextButton());
            } else {
                return sprintf($this->options['theme']['default'], $this->getPreviousButton(), $this->getLinks(), $this->getNextButton());
            }
        }
        
        return '';
    }
    
    
    /**
     * 生成一个可点击的按钮
     *
     * @param string $url
     * @param string $page
     * @return string
     */
    protected function getAvailablePageWrapper(string $url, string $page) : string
    {
        return sprintf($this->options['template']['link'], htmlentities($url), $page);
    }
    
    
    /**
     * 生成一个禁用的按钮
     *
     * @param string $text
     * @return string
     */
    protected function getDisabledTextWrapper(string $text) : string
    {
        return sprintf($this->options['template']['disabled'], $text);
    }
    
    
    /**
     * 生成一个激活的按钮
     *
     * @param string $text
     * @return string
     */
    protected function getActivePageWrapper(string $text) : string
    {
        return sprintf($this->options['template']['active'], $text);
    }
    
    
    /**
     * 生成省略号按钮
     *
     * @return string
     */
    protected function getDots() : string
    {
        return $this->getDisabledTextWrapper('...');
    }
    
    
    /**
     * 批量生成页码按钮.
     *
     * @param array $urls
     * @return string
     */
    protected function getUrlLinks(array $urls) : string
    {
        $html = '';
        
        foreach ($urls as $page => $url) {
            $html .= $this->getPageLinkWrapper($url, $page);
        }
        
        return $html;
    }
    
    
    /**
     * 生成普通页码按钮
     *
     * @param string $url
     * @param string $page
     * @return string
     */
    protected function getPageLinkWrapper(string $url, string $page) : string
    {
        if ($this->currentPage() == $page) {
            return $this->getActivePageWrapper($page);
        }
        
        return $this->getAvailablePageWrapper($url, $page);
    }
    
    
    public static function getCurrentPage(string $varPage = '', int $default = 1) : int
    {
        return parent::getCurrentPage($varPage, $default);
    }
    
    
    /**
     * 实例化分页
     * @param array|Collection $items
     * @param int              $listRows 每页显示多少条，默认20条
     * @param int              $currentPage 当前页码
     * @param int|null         $total 总条数
     * @param bool             $simple 简洁模式
     * @param array            $options 配置
     * @return $this
     */
    public static function init($items, int $listRows, int $currentPage = 1, int $total = null, bool $simple = false, array $options = [])
    {
        self::maker(function($items, $listRows, $currentPage, $total, $simple, $options) {
            return new Page($items, $listRows, $currentPage, $total, $simple, $options);
        });
        
        return Paginator::make($items, $listRows, $currentPage, $total, $simple, $options);
    }
}
