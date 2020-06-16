<?php

namespace BusyPHP\app\admin\model\system\file {
    
    set_time_limit(0);
    
    use BusyPHP\App;
    use BusyPHP\exception\AppException;
    use BusyPHP\exception\VerifyException;
    use BusyPHP\helper\file\File;
    use BusyPHP\helper\file\UploadFile;
    use BusyPHP\helper\file\UploadFileResult;
    use BusyPHP\helper\image\Image;
    use BusyPHP\helper\image\Thumb;
    use BusyPHP\app\admin\setting\FileSetting;
    
    /**
     * 附件上传
     * @author busy^life <busy.life@qq.com>
     * @copyright 2015 - 2017 busy^life <busy.life@qq.com>
     * @version $Id: 2017-05-30 上午11:48 SystemFileUpload.php busy^life $
     */
    class SystemFileUpload extends UploadFile
    {
        //+--------------------------------------
        //| 裁图类型
        //+--------------------------------------
        /** 按比例裁剪到指定大小 */
        const THUMB_TYPE_CUT_PROPORTION = 1;
        
        /** 不按比例裁剪到指定大小 */
        const THUMB_TYPE_CUT = 5;
        
        /** 按比例缩放(小图不够不缩放) */
        const THUMB_TYPE_ZOOM = 3;
        
        /** 按比例缩放(小图不够强制缩放) */
        const THUMB_TYPE_ZOOM_MUST = 4;
        
        //+--------------------------------------
        //| 私有变量
        //+--------------------------------------
        /** @var int 会员ID */
        protected $userId = 0;
        
        /** @var bool 是否后台上传 */
        protected $isAdmin = false;
        
        /** @var string 文件标记 */
        protected $markType = '';
        
        /** @var string 文件标记值 */
        protected $markValue = '';
        
        /** @var string 附件分类 */
        protected $classify = '';
        
        /** @var FileSetting 上传配置 */
        protected $fileSetting = null;
        
        
        /**
         * SystemFileUpload constructor.
         * @throws AppException
         */
        public function __construct()
        {
            $this->fileSetting = FileSetting::init();
            
            // 基本目录
            $savePath = str_replace('/', DIRECTORY_SEPARATOR, $this->fileSetting->getSavePath());
            $savePath = trim($savePath, DIRECTORY_SEPARATOR);
            $this->setRootPath(App::urlToPath($savePath) . DIRECTORY_SEPARATOR);
            
            // URL入口
            $this->setRootUrl($this->fileSetting->getSavePath());
            
            // 临时文件目录
            $this->setTmpPath(App::runtimeUploadPath());
            
            // 上传目录解析
            $format = $this->fileSetting->getFormat();
            if (false !== stripos($format, 'hash-')) {
                $arr = explode('-', $format);
                $this->setFolderNameMethod(self::FOLDER_NAME_METHOD_HASH, intval($arr[1]));
            } else {
                $this->setFolderNameMethod(self::FOLDER_NAME_METHOD_DATE, $format);
            }
            
            // 分片参数
            $this->setChunkField('guid', 'chunks', 'chunk', 'is_complete', 'filename');
            
            // 同名覆盖
            $this->setReplaceCover(true);
            
            // 命名方法和hash方法
            $this->setFileNameMethod('uniqid', 'md5_file');
        }
        
        
        /**
         * 设置是否后台上传
         * @param boolean $isAdmin
         */
        public function setIsAdmin($isAdmin)
        {
            $this->isAdmin = $isAdmin;
        }
        
        
        /**
         * 设置上传用户ID
         * @param int $userId
         */
        public function setUserId($userId)
        {
            $this->userId = floatval($userId);
        }
        
        
        /**
         * 设置文件标记和值
         * @param string $type
         * @param string $value
         */
        public function setMark($type, $value = '')
        {
            $this->markType  = trim($type);
            $this->markValue = trim($value);
            $this->fileSetting->setClassify($type);
        }
        
        
        /**
         * 执行上传
         * @param mixed $data
         * @return SystemFileUpload_Result|true
         * @throws AppException
         */
        public function upload($data)
        {
            // 按类型解析
            if (!$this->markType || !$this->fileSetting->getClassConfig()) {
                throw new VerifyException('系统不允许上传该类型[empty]', 'empty');
            }
            
            // 前端上传
            if (!$this->isAdmin) {
                if (!$this->fileSetting->homeCanUpload()) {
                    throw new VerifyException('系统不允许上传该类型[home.disabled]', 'home.disabled');
                }
                if ($this->fileSetting->homeNeedLogin() && $this->userId < 1) {
                    throw new VerifyException('请登录后上传', 'login');
                }
                
                $limitType = $this->fileSetting->getHomeType();
                $limitSize = $this->fileSetting->getHomeSize();
            } else {
                if ($this->userId < 1) {
                    throw new VerifyException('请登录后上传', 'login');
                }
                
                $limitType = $this->fileSetting->getAdminType();
                $limitSize = $this->fileSetting->getAdminSize();
            }
            
            $this->setLimitMaxSize($limitSize);
            $this->setLimitExtensions($limitType);
            $this->classify = $this->fileSetting->getClassConfig('type');
            
            
            // 执行上传
            $uploadResult = parent::upload($data);
            if ($uploadResult === true) {
                return true;
            }
            
            $thumbResult = null;
            $fileModel   = new SystemFile();
            $fileModel->startTrans();
            try {
                // 缩图
                if ($this->fileSetting->isThumb()) {
                    $thumbResult = $this->thumb($uploadResult);
                }
                
                // 加水印
                if ($this->fileSetting->isWatermark()) {
                    // 缩放图片并删除原图
                    // 则在新图上添加水印
                    if ($this->fileSetting->isThumb() && $this->fileSetting->isThumbDeleteSource()) {
                        Image::water($thumbResult->savePath, $this->fileSetting->getWatermarkFile(), $this->fileSetting->getWatermarkPosition());
                    }
                    
                    // 不删除原图
                    // 在原图上添加水印
                    else {
                        Image::water($uploadResult->savePath, $this->fileSetting->getWatermarkFile(), $this->fileSetting->getWatermarkPosition());
                    }
                }
                
                
                // 保存上传数据
                $insert = new SystemFileCreate();
                $insert->setUserId($this->userId)
                    ->setIsAdmin($this->isAdmin)
                    ->setClassify($this->classify)
                    ->setMarkType($this->markType)
                    ->setMarkValue($this->markValue);
                
                // 删除原图则只保存缩图数据
                if ($this->fileSetting->isThumbDeleteSource()) {
                    $insert->setUrl($thumbResult->url)
                        ->setName($thumbResult->name)
                        ->setSize($thumbResult->size)
                        ->setMimeType($thumbResult->mimeType)
                        ->setExtension($thumbResult->extension)
                        ->setHash($thumbResult->hash);
                    
                    $fileId     = $fileModel->insertData($insert);
                    $returnData = $this->parseResult($thumbResult, $fileId);
                } else {
                    // 先保存原图数据
                    $insert->setUrl($uploadResult->url)
                        ->setName($uploadResult->name)
                        ->setSize($uploadResult->size)
                        ->setMimeType($uploadResult->mimeType)
                        ->setExtension($uploadResult->extension)
                        ->setHash($uploadResult->hash);
                    $fileId     = $fileModel->insertData($insert);
                    $returnData = $this->parseResult($uploadResult, $fileId);
                    
                    // 保存缩图数据
                    if ($thumbResult) {
                        $insert->setUrl($thumbResult->url)
                            ->setName($thumbResult->name)
                            ->setSize($thumbResult->size)
                            ->setMimeType($thumbResult->mimeType)
                            ->setExtension($thumbResult->extension)
                            ->setHash($thumbResult->hash)
                            ->setThumbId($fileId)
                            ->setIsThumb(true)
                            ->setThumbHeight($this->fileSetting->getThumbWidth())
                            ->setThumbWidth($this->fileSetting->getThumbHeight());
                        
                        $fileId            = $fileModel->insertData($insert);
                        $returnData->thumb = $this->parseResult($thumbResult, $fileId);
                    }
                }
                
                $fileModel->commit();
                
                return $returnData;
            } catch (AppException $e) {
                $fileModel->rollback();
                
                if ($uploadResult) {
                    unlink($uploadResult->savePath);
                }
                if ($thumbResult) {
                    unlink($thumbResult->savePath);
                }
                
                throw new AppException($e->getMessage());
            }
        }
        
        
        /**
         * 进行缩图
         * @param UploadFileResult $uploadResult
         * @return UploadFileResult
         * @throws AppException
         */
        public function thumb($uploadResult)
        {
            $sourcePath = $uploadResult->savePath;
            $result     = clone $uploadResult;
            
            // 不删除源文件 需要重新生成缩图路径
            if (!$this->fileSetting->isThumbDeleteSource()) {
                $pathInfo         = File::pathInfo($uploadResult->savePath);
                $thumbSuffix      = "{$this->fileSetting->getThumbWidth()}X{$this->fileSetting->getThumbHeight()}";
                $result->filename = "{$pathInfo['filename']}_{$thumbSuffix}.{$pathInfo['extension']}";
                $result->savePath = "{$pathInfo['dirname']}/{$result->filename}";
                $nameInfo         = File::pathInfo('/parse/' . $uploadResult->name);
                $result->name     = "THUMB_{$nameInfo['filename']}_{$thumbSuffix}.{$nameInfo['extension']}";
                $result->url      = $this->parseUrl($result->folderPath, $result->filename);
            }
            
            // 生成缩略图
            $thumb = new Thumb();
            $thumb->setSave(true);
            $thumb->setSource($sourcePath);
            $thumb->setExport($result->savePath);
            $thumb->setWidth($this->fileSetting->getThumbWidth());
            $thumb->setHeight($this->fileSetting->getThumbHeight());
            switch ($this->fileSetting->getThumbType()) {
                case self::THUMB_TYPE_ZOOM:
                    $thumb->scale(false);
                break;
                case self::THUMB_TYPE_ZOOM_MUST:
                    $thumb->scale(true);
                break;
                case self::THUMB_TYPE_CUT:
                    $thumb->contort();
                break;
                default:
                    $thumb->cut();
            }
            
            $result->size = filesize($result->savePath);
            $result->hash = $this->parseHash($result->savePath);
            
            return $result;
        }
        
        
        /**
         * 解析返回结果
         * @param UploadFileResult $result
         * @param int              $fileId
         * @return SystemFileUpload_Result
         */
        private function parseResult($result, $fileId)
        {
            $obj             = new SystemFileUpload_Result();
            $obj->savePath   = $result->savePath;
            $obj->url        = $result->url;
            $obj->hash       = $result->hash;
            $obj->extension  = $result->extension;
            $obj->mimeType   = $result->mimeType;
            $obj->size       = $result->size;
            $obj->name       = $result->name;
            $obj->rootPath   = $result->rootPath;
            $obj->filename   = $result->filename;
            $obj->folderPath = $result->folderPath;
            $obj->id         = floatval($fileId);
            
            return $obj;
        }
    }
    
    
    /**
     * 附件上传返回数据容器
     * @author busy^life <busy.life@qq.com>
     * @copyright 2015 - 2018 busy^life <busy.life@qq.com>
     * @version $Id: 2018-01-18 下午3:43 SystemFileUpload.php busy^life $
     */
    class SystemFileUpload_Result extends UploadFileResult
    {
        /** @var int 附件ID */
        public $id;
        
        /** @var SystemFileUpload_Result 缩图数据 */
        public $thumb;
    }
}