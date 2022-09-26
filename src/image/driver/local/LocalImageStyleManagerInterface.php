<?php
declare(strict_types = 1);

namespace BusyPHP\image\driver\local;

use BusyPHP\image\result\ImageStyleResult;

/**
 * LocalImageStyleInterface
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/19 10:08 AM LocalImageStyleInterface.php $
 */
interface LocalImageStyleManagerInterface
{
    /**
     * 获取图片样式
     * @param string $name 图片样式
     * @return ImageStyleResult
     */
    public function getImageStyle(string $name) : ImageStyleResult;
    
    
    /**
     * 查询图片样式
     * @return ImageStyleResult[]
     */
    public function selectImageStyle() : array;
    
    
    /**
     * 删除图片样式
     * @param string $name 图片样式
     * @return void
     */
    public function deleteImageStyle(string $name) : void;
    
    
    /**
     * 创建图片样式
     * @param string $name 图片样式
     * @param array  $content 样式规则
     * @return void
     */
    public function createImageStyle(string $name, array $content) : void;
    
    
    /**
     * 更新图片样式
     * @param string $name 图片样式
     * @param array  $content 样式规则
     * @return void
     */
    public function updateImageStyle(string $name, array $content);
}