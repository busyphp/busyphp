<?php
declare(strict_types = 1);

namespace BusyPHP\file\upload;

use BusyPHP\file\Upload;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\file\UploadedFile;

/**
 * 本地上传类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/20 下午下午4:06 LocalUpload.php $
 */
class LocalUpload extends Upload
{
    /**
     * 处理
     * @param $file
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function dealFile($file) : array
    {
        $file = $this->getFile($file);
        if ($file instanceof UploadedFile) {
            $name      = $file->getOriginalName();
            $mime      = $file->getOriginalMime();
            $extension = $file->getOriginalExtension();
        } else {
            $name      = $file->getFilename();
            $mime      = $file->getMime();
            $extension = $file->getExtension();
        }
        
        // 合规检测
        $this->checkExtension($extension);
        $this->checkFileSize($file->getSize());
        $this->checkMimeType($mime);
        $imageInfo = $this->checkImage($file->getRealPath(), $extension);
        
        return [$name, $this->putFile($file), $imageInfo];
    }
    
    
    /**
     * 上传处理
     * @param mixed $file 上传的数据
     * @return array
     * @throws Exception
     */
    protected function handle($file) : array
    {
        return $this->dealFile($file);
    }
}