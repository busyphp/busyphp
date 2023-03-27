<?php

if (!function_exists('ckeditor5_css_url')) {
    /**
     * 获取CkEditor5 CSS URL
     * @return string
     */
    function ckeditor5_css_url(bool $domain = false) : string
    {
        return app()->request->getAssetsUrl($domain) . 'system/css/ckeditor5.css?v=' . app()->getFrameworkVersion();
    }
}

if (!function_exists('ckeditor5_css_link')) {
    /**
     * 获取CkEditor5 CSS Link标签
     * @return string
     */
    function ckeditor5_css_link(bool $domain = false) : string
    {
        return '<link rel="stylesheet" href="' . ckeditor5_css_url($domain) . '"/>';
    }
}