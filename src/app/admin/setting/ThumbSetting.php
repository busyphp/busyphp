<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\setting;

use BusyPHP\App;
use BusyPHP\file\image\ThumbUrl;
use BusyPHP\helper\util\Filter;
use BusyPHP\helper\util\Transform;
use BusyPHP\model\Setting;

/**
 * 动态缩图配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/19 下午下午3:44 ThumbSetting.php $
 */
class ThumbSetting extends Setting
{
    /**
     * 获取数据解析器
     * @param mixed $data
     * @return mixed
     */
    protected function parseGet($data)
    {
        return $data;
    }
    
    
    /**
     * 设置数据解析器
     * @param mixed $data
     * @return mixed
     */
    protected function parseSet($data)
    {
        $data                   = Filter::trim($data);
        $data['save_local']     = Transform::dataToBool($data['save_local'] ?? 0);
        $data['unlimited_size'] = Transform::dataToBool($data['unlimited_size'] ?? 0);
        $data['watermark']      = Transform::dataToBool($data['watermark'] ?? 0);
        $sizes                  = [];
        foreach ($data['sizes'] ?? [] as $vo) {
            $width  = Filter::min((int) ($vo['width'] ?? 0));
            $height = Filter::min((int) ($vo['height'] ?? 0));
            $alias  = trim((string) ($vo['alias'] ?? ''));
            if ($width <= 0 || $height <= 0) {
                continue;
            }
            $sizes[] = ['alias' => $alias, 'width' => $width, 'height' => $height];
        }
        $data['sizes'] = $sizes;
        
        return $data;
    }
    
    
    /**
     * 获取绑定域名
     * @return string
     */
    public function getDomain() : string
    {
        return (string) $this->get('domain', '');
    }
    
    
    /**
     * 获取填充背景色
     * @return string
     */
    public function getBgColor() : string
    {
        return (string) ($this->get('bg_color', '') ?: '#FFFFFF');
    }
    
    
    /**
     * 获取填充背景色
     * @return string
     */
    public function getEmptyImageVar() : string
    {
        return (string) ($this->get('empty_image_var', '') ?: ThumbUrl::EMPTY_IMAGE_VAR);
    }
    
    
    /**
     * 是否保存到本地
     * @return bool
     */
    public function isSaveLocal() : bool
    {
        return (bool) $this->get('save_local', false);
    }
    
    
    /**
     * 是否加水印
     * @return bool
     */
    public function isWatermark() : bool
    {
        return (bool) $this->get('watermark', false);
    }
    
    
    /**
     * 是否不限制尺寸白名单
     * @return bool
     */
    public function isUnlimitedSize() : bool
    {
        return (bool) $this->get('unlimited_size', false);
    }
    
    
    /**
     * 获取尺寸
     * @param $name
     * @return array
     */
    public function getSize($name) : array
    {
        $sizes = [];
        $index = 0;
        foreach ($this->get('sizes', []) as $item) {
            $alias                           = "!__alias_!{$item['width']}-{$item['height']}";
            $sizes[$item['alias'] ?: $index] = $item;
            $sizes[$alias]                   = $item;
            $index++;
        }
        
        if ($sizeConfig = ($sizes[$name] ?? null)) {
            return $sizeConfig;
        } else {
            $name = '!__alias_!' . str_replace('x', '-', $name);
            if ($sizeConfig = ($sizes[$name] ?? null)) {
                return $sizeConfig;
            }
        }
        
        return [];
    }
    
    
    /**
     * 获取错误占位图
     * @param bool $isPath
     * @return string
     */
    public function getErrorPlaceholder(bool $isPath = false) : string
    {
        $image = $this->get('error_placeholder', '');
        if (!$image) {
            return PublicSetting::init()->getImgErrorPlaceholder($isPath);
        }
        
        return $isPath ? App::urlToPath($image) : $image;
    }
}