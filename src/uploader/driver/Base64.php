<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\driver;

use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\uploader\driver\base64\Base64Data;
use BusyPHP\uploader\result\UploadResult;
use InvalidArgumentException;
use League\Flysystem\FilesystemException;

/**
 * Base64上传驱动
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/19 2:25 PM Base64Upload.php $
 * @property Base64Data $data
 */
class Base64 extends Content
{
    /**
     * 执行上传
     * @return UploadResult
     * @throws FilesystemException
     */
    protected function handle() : UploadResult
    {
        if (!$this->data instanceof Base64Data) {
            throw new ClassNotExtendsException($this->data, Base64Data::class);
        }
        
        if (!$data = $this->data->getData()) {
            throw new InvalidArgumentException('无效的base64数据');
        }
        
        // 取mimetype
        $mimetype = '';
        if (preg_match('/^(data:\s*(.*);\s*base64,)/i', $data, $match)) {
            $mimetype = strtolower($match[2]);
            $data     = str_replace($match[1], '', $data);
        }
        
        // 解码文件数据
        if (!$data = base64_decode(str_replace(' ', '+', $data))) {
            throw new InvalidArgumentException('无效的base64数据');
        }
        
        return $this->deal($this->data, $data, '', '', $mimetype);
    }
    
    
    /**
     * @inheritDoc
     */
    public static function configName() : string
    {
        return 'base64';
    }
}