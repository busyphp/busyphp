<?php
declare(strict_types = 1);

namespace BusyPHP\image\concern;

use BusyPHP\app\admin\setting\StorageSetting;
use BusyPHP\helper\FileHelper;
use think\exception\FileException;
use think\file\UploadedFile;
use Throwable;

/**
 * 上传水印相关类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/12 8:51 PM UploadWatermarkConcern.php $
 */
trait UploadWatermarkConcern
{
    /**
     * 检测上传的水印是否有效
     * @param UploadedFile $file
     * @throws Throwable
     */
    protected function checkUploadWatermark(UploadedFile $file)
    {
        FileHelper::checkFilesize(StorageSetting::init()->getMaxSize(), $file->getSize());
        FileHelper::checkImage($file->getPathname(), $file->getOriginalExtension());
        if (!in_array(strtolower($file->getOriginalExtension()), ['png', 'jpeg', 'jpg', 'gif'])) {
            throw new FileException('仅支持png,jpeg,jpg,gif');
        }
    }
}