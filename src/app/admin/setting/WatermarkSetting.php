<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\setting;

use BusyPHP\App;
use BusyPHP\exception\VerifyException;
use BusyPHP\model\Setting;
use BusyPHP\helper\util\Filter;

/**
 * 图片水印配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/19 下午下午4:05 WatermarkSetting.php $
 */
class WatermarkSetting extends Setting
{
    /**
     * @param array $data
     * @return array
     * @throws VerifyException
     */
    protected function parseSet($data)
    {
        $data = Filter::trim($data);
        
        $data['position']      = Filter::min($data['position']);
        $data['position']      = Filter::max($data['position'], 10);
        $data['opacity']       = Filter::min($data['opacity'], 1);
        $data['opacity']       = Filter::max($data['opacity'], 100);
        $data['offset_rotate'] = Filter::min($data['offset_rotate'], 0);
        $data['offset_rotate'] = Filter::max($data['offset_rotate'], 360);
        if ($data['file'] && !is_file(App::urlToPath($data['file']))) {
            throw new VerifyException('水印文件不存在', 'file');
        }
        
        return $data;
    }
    
    
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
     * 获取水印文件路径
     * @return string
     */
    public function getFile() : string
    {
        $file = $this->get('file') ?: '';
        if (!$file) {
            return '';
        }
        
        return App::urlToPath($file);
    }
    
    
    /**
     * 获取水印位置
     * @return int
     */
    public function getPosition() : int
    {
        return intval($this->get('position', 0));
    }
    
    
    /**
     * 获取水印透明度
     * @return int
     */
    public function getOpacity() : int
    {
        return intval($this->get('opacity', 25));
    }
    
    
    /**
     * 获取水印X轴偏移
     * @return int
     */
    public function getOffsetX() : int
    {
        return intval($this->get('offset_x', 0));
    }
    
    
    /**
     * 获取水印Y轴偏移
     * @return int
     */
    public function getOffsetY() : int
    {
        return intval($this->get('offset_y', 0));
    }
    
    
    /**
     * 获取水印角度
     * @return int
     */
    public function getOffsetRotate() : int
    {
        return intval($this->get('offset_rotate', 0));
    }
    
    
    /**
     * 水印文件是否存在
     * @return bool
     */
    public function hasFile() : bool
    {
        $watermarkFile = $this->getFile();
        clearstatcache(false, $watermarkFile);
        if (is_file($watermarkFile)) {
            return true;
        }
        
        return false;
    }
}