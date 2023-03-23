<?php
declare(strict_types = 1);

namespace BusyPHP\upload\driver;

use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\helper\FileHelper;
use BusyPHP\Upload;
use BusyPHP\upload\parameter\ContentParameter;
use BusyPHP\upload\result\UploadResult;
use InvalidArgumentException;
use League\Flysystem\FilesystemException;

/**
 * 文件内容上传
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/20 2:33 PM ContentUpload.php $
 * @property ContentParameter $parameter
 */
class ContentUpload extends Upload
{
    /**
     * 执行上传
     * @return UploadResult
     * @throws FilesystemException
     */
    protected function handle() : UploadResult
    {
        if (!$this->parameter instanceof ContentParameter) {
            throw new ClassNotExtendsException($this->parameter, ContentParameter::class);
        }
        
        if (!$content = $this->parameter->getData()) {
            throw new InvalidArgumentException('无效的文件数据');
        }
        
        return $this->deal($this->parameter, $content);
    }
    
    
    /**
     * 处理内容上传
     * @param ContentParameter $parameter
     * @param string           $content
     * @param string           $basename
     * @param string           $extension
     * @param string           $mimetype
     * @return UploadResult
     * @throws FilesystemException
     */
    protected function deal(ContentParameter $parameter, string $content, string $basename = '', string $extension = '', string $mimetype = '') : UploadResult
    {
        // 文件名与mimetype
        $mimetype  = $mimetype ?: FileHelper::getMimetypeByContent($content);
        $extension = $extension === '' ? FileHelper::getExtensionByMimetype($mimetype) : $extension;
        $basename  = $parameter->getBasename($content, $basename === '' ? sprintf('%s.%s', date('YmdHis'), $extension) : $basename);
        $mimetype  = $parameter->getMimetype($content, $mimetype, $basename);
        
        // 校验
        $extension = pathinfo($basename, PATHINFO_EXTENSION);
        $this->checkExtension($extension);
        $this->checkFilesize($filesize = strlen($content));
        $this->checkMimetype($mimetype);
        [$width, $height] = FileHelper::checkImage($content, $extension, true);
        $path = $this->putContentToDisk($content, $extension);
        
        $result = new UploadResult();
        $result->setBasename($basename);
        $result->setMimetype($mimetype);
        $result->setFilesize($filesize);
        $result->setPath($path);
        $result->setMd5(md5($content));
        $result->setWidth($width);
        $result->setHeight($height);
        
        return $result;
    }
}