<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClassInfo;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\model\system\file\SystemFileField;
use BusyPHP\app\admin\setting\UploadSetting;
use BusyPHP\exception\PartUploadSuccessException;
use BusyPHP\file\upload\PartUpload;
use BusyPHP\helper\FilterHelper;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;

/**
 * 通用文件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/3 下午下午8:33 FileController.php $
 */
class FileController extends InsideController
{
    /**
     * @var SystemFile
     */
    private $model;
    
    
    protected function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model = SystemFile::init();
    }
    
    
    /**
     * 上传文件
     * @throws Exception
     */
    public function upload()
    {
        $this->request->setRequestIsAjax();
        $classType     = $this->post('class_type/s', 'trim');
        $classValue    = $this->post('class_value/s', 'trim');
        $chunkFilename = $this->post('chunk_filename/s', 'trim');
        $chunkComplete = $this->post('chunk_complete/b');
        $chunkTotal    = $this->post('chunk_total/d');
        $chunkCurrent  = $this->post('chunk_current/d');
        $chunkId       = $this->post('chunk_guid/s', 'trim');
        
        try {
            $upload = new PartUpload();
            $upload->setUserId($this->adminUserId);
            $upload->setClassType($classType, $classValue);
            $upload->setName($chunkFilename);
            $upload->setComplete($chunkComplete);
            $upload->setTotal($chunkTotal);
            $upload->setCurrent($chunkCurrent);
            $upload->setId($chunkId);
            
            $result = $upload->upload($this->request->file('upload'));
        } catch (PartUploadSuccessException $e) {
            return $this->success('PART SUCCESS');
        }
        
        $data = [
            'file_url'  => $result->url,
            'file_id'   => $result->id,
            'name'      => $result->name,
            'filename'  => $result->file->getFilename(),
            'extension' => $result->file->getExtension(),
        ];
        $this->log()->record(self::LOG_INSERT, '上传文件', json_encode($data, JSON_UNESCAPED_UNICODE));
        
        return $this->success('上传成功', $data);
    }
    
    
    /**
     * 文件管理器
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function picker()
    {
        $classType  = $this->get('class_type/s', 'trim');
        $classValue = $this->get('class_value/s', 'trim');
        $extensions = $this->get('extensions/s', 'trim');
        $range      = $this->get('range/d');
        $type       = $this->get('type/s', 'trim');
        $word       = $this->get('word/s', 'trim');
        $fileType   = SystemFile::FILE_TYPE_FILE;
        
        // 按分类查询
        if ($range > 0) {
            if (false !== stripos($type, 'type:')) {
                $typeList = SystemFileClass::init()->getAdminOptions(true);
                $fileType = substr($type, 5);
                $typeList = $typeList[$fileType] ?? [];
                $ins      = [];
                
                /** @var SystemFileClassInfo $item */
                foreach ($typeList as $item) {
                    $ins[] = $item->var;
                }
                
                if ($typeList) {
                    $this->model->whereEntity(SystemFileField::type('in', $ins));
                }
            } else {
                $typeList = SystemFileClass::init()->getList();
                $fileType = $typeList[$type]->type ?? $fileType;
                if ($type) {
                    $this->model->whereEntity(SystemFileField::type($type));
                }
            }
        }
        
        //
        // 当前信息
        else {
            $typeList = SystemFileClass::init()->getList();
            $fileType = $typeList[$classType]->type ?? $fileType;
            $this->model->whereEntity(SystemFileField::classType($classType));
            $this->model->whereEntity(SystemFileField::classValue($classValue));
        }
        
        // 允许的后缀
        if ($extensions) {
            $extensionsList = FilterHelper::trimArray(explode(',', $extensions));
            if ($extensionsList) {
                $this->model->whereEntity(SystemFileField::extension('in', $extensionsList));
            }
        }
        
        if ($word) {
            $this->model->whereEntity(SystemFileField::name('like', '%' . FilterHelper::searchWord($word) . '%'));
        }
        
        
        $this->assign('type_options', SystemFileClass::init()->getAdminOptions($type));
        $this->assign('class_type', $classType);
        $this->assign('class_value', $classValue);
        $this->assign('extensions', $extensions);
        $this->assign('range', $range);
        $this->assign('word', $word);
        $this->assign('reset_url', url('', ['class_type' => $classType, 'class_value' => $classValue]));
        $this->assign('is_file', in_array($fileType, [
            SystemFile::FILE_TYPE_FILE,
            SystemFile::FILE_TYPE_VIDEO,
            SystemFile::FILE_TYPE_AUDIO
        ]));
        $this->assign('is_image', $fileType === SystemFile::FILE_TYPE_IMAGE);
        $this->assign('info', $this->list($this->model)->select());
        
        return $this->display();
    }
    
    
    /**
     * 上传配置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function config()
    {
        $uploadSetting = UploadSetting::init();
        $classList     = SystemFileClass::init()->getList();
        $fileClass     = [];
        foreach ($classList as $key => $r) {
            $fileClass[$key] = [
                'size'   => $uploadSetting->getMaxSize($key),
                'suffix' => implode(',', $uploadSetting->getAllowExtensions($key)),
                'mime'   => implode(',', $uploadSetting->getMimeType($key)),
                'type'   => $r['type'],
                'name'   => $r['name'],
                'thumb'  => $uploadSetting->getThumbType() > 0,
                'width'  => $uploadSetting->getThumbWidth($key),
                'height' => $uploadSetting->getThumbHeight($key),
            ];
        }
        
        $uploadUrl = url('upload');
        $pickerUrl = url('picker?class_type=_type_&class_value=_value_&extensions=_extensions_');
        $fileClass = json_encode($fileClass, JSON_UNESCAPED_UNICODE);
        
        $script = <<<JS
(function (factory) {
    if (typeof exports === 'object' && typeof module !== 'undefined'){
        factory();
    } else if (typeof define === 'function' && define.amd) {
        define(factory);
    } else if (busyAdmin) {
        busyAdmin.uploadOptions = factory();
    } else {
        window.busyUploadOptions = factory();
    }
}(function () {
    'use strict';
    
    return {
        url    : '{$uploadUrl}',
        picker : '{$pickerUrl}',
        config : {$fileClass}
    }
}));
JS;
        
        return Response::create($script)->contentType("application/javascript");
    }
}