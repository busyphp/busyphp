<?php

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClassInfo;
use BusyPHP\app\admin\model\system\file\SystemFileField;
use BusyPHP\app\admin\setting\UploadSetting;
use BusyPHP\exception\PartUploadSuccessException;
use BusyPHP\file\upload\PartUpload;
use BusyPHP\helper\util\Filter;
use BusyPHP\helper\util\Transform;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\model\Map;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;

/**
 * 附件管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午2:17 下午 File.php $
 */
class SystemFileController extends InsideController
{
    /**
     * @var SystemFile
     */
    private $model;
    
    
    public function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model = SystemFile::init();
    }
    
    
    /**
     * 列表
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function index()
    {
        if ($this->pluginTable) {
            $this->pluginTable->setQueryHandler(function(SystemFile $modal, Map $data) {
                if (!$type = $data->get('type', '')) {
                    $data->remove('type');
                }
                if (0 === strpos($type, 'type:')) {
                    $data->set('type', substr($type, 5));
                } elseif ($type) {
                    $data->set('class_type', $type);
                    $data->remove('type');
                }
                
                if ($data->get('client', -1) == -1) {
                    $data->remove('client');
                }
                
                if ($this->pluginTable->sortField === 'format_size') {
                    $this->pluginTable->sortField = 'size';
                } elseif ($this->pluginTable->sortField === 'format_create_time') {
                    $this->pluginTable->sortField = 'create_time';
                }
            });
            
            return $this->success($this->pluginTable->build($this->model));
        }
        
        $this->assign('type_options', SystemFileClass::init()->getAdminOptions('', '不限类型'));
        $this->assign('client_options', Transform::arrayToOption(SystemFile::getClients(), '__index', 'name'));
        
        return $this->display();
    }
    
    
    /**
     * 文件上传
     * @return Response
     * @throws Exception
     */
    public function upload()
    {
        if ($this->requestPluginName === 'Upload') {
            $this->request->setRequestIsAjax();
            $classType     = $this->request->post('class_type', '', 'trim');
            $classValue    = $this->request->post('class_value', '', 'trim');
            $chunkFilename = $this->request->post('chunk_filename', '', 'trim');
            $chunkComplete = $this->request->post('chunk_complete', 0, 'intval') > 0;
            $chunkTotal    = $this->request->post('chunk_total', 0, 'intval');
            $chunkCurrent  = $this->request->post('chunk_current', 0, 'intval');
            $chunkId       = $this->request->post('chunk_guid', '', 'trim');
            
            try {
                $upload = new PartUpload();
                $upload->setClient(0, $this->adminUserId);
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
            
            return $this->success('上传成功', [
                'file_url'  => $result->url,
                'file_id'   => $result->id,
                'name'      => $result->name,
                'filename'  => $result->file->getFilename(),
                'extension' => $result->file->getExtension(),
            ]);
        }
        
        return $this->display();
    }
    
    
    /**
     * 删除附件
     */
    public function delete()
    {
        $this->bind(self::CALL_BATCH_EACH, function($id) {
            $this->model->deleteInfo($id);
        });
        $this->bind(self::CALL_BATCH_EACH_AFTER, function($params) {
            $this->log('删除附件', ['id' => $params], self::LOG_DELETE);
            
            return '删除成功';
        });
        
        return $this->batch();
    }
    
    
    /**
     * 文件管理器
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function picker()
    {
        $classType  = $this->iGet('class_type', 'trim');
        $classValue = $this->iGet('class_value', 'trim');
        $extensions = $this->iGet('extensions', 'trim');
        $range      = $this->iGet('range', 'intval');
        $type       = $this->iGet('type', 'trim');
        $word       = $this->iGet('word', 'trim');
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
            $extensionsList = Filter::trimArray(explode(',', $extensions));
            if ($extensionsList) {
                $this->model->whereEntity(SystemFileField::extension('in', $extensionsList));
            }
        }
        
        if ($word) {
            $this->model->whereEntity(SystemFileField::name('like', '%' . Filter::searchWord($word) . '%'));
        }
        
        
        $this->assign('type_options', SystemFileClass::init()->getAdminOptions($type));
        $this->assign('class_type', $classType);
        $this->assign('class_value', $classValue);
        $this->assign('extensions', $extensions);
        $this->assign('range', $range);
        $this->assign('word', $word);
        $this->assign('reset_url', url('', ['mark_type' => $classType, 'mark_value' => $classValue]));
        $this->assign('is_file', in_array($fileType, [
            SystemFile::FILE_TYPE_FILE,
            SystemFile::FILE_TYPE_VIDEO,
            SystemFile::FILE_TYPE_AUDIO
        ]));
        $this->assign('is_image', $fileType === SystemFile::FILE_TYPE_IMAGE);
        
        // TODO 分页应该能自定义为迷你版
        return $this->select($this->model);
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
                'size'   => $uploadSetting->getMaxSize(),
                'suffix' => implode(',', $uploadSetting->getAllowExtensions(0, $key)),
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