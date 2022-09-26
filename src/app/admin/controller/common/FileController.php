<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClassInfo;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\model\system\file\SystemFileField;
use BusyPHP\app\admin\model\system\file\SystemFilePrepareUploadParameter;
use BusyPHP\app\admin\model\system\file\SystemFileUploadParameter;
use BusyPHP\app\admin\plugin\FrontUploadInjectScriptPlugin;
use BusyPHP\app\admin\setting\StorageSetting;
use BusyPHP\helper\FileHelper;
use BusyPHP\helper\FilterHelper;
use BusyPHP\upload\parameter\LocalParameter;
use stdClass;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;
use Throwable;

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
     * 前端准备上传
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function front_prepare() : Response
    {
        $parameter = new SystemFilePrepareUploadParameter(
            $this->post('md5/s', 'trim'),
            $this->post('filename/s', 'trim'),
            $this->post('filesize/d', 'intval'),
            $this->post('mimetype/s', 'trim')
        );
        $parameter->setUserId($this->adminUserId);
        $parameter->setClassType($this->post('class_type/s', 'trim'));
        $parameter->setClassValue($this->post('class_value/s', 'trim'));
        $parameter->setPart($this->post('part/b'));
        $parameter->setDisk($this->post('disk/s', 'trim'));
        $result = $this->model->frontPrepareUpload($parameter);
        $info   = $result->getInfo();
        $server = $result->getServerUrl();
        $server = $server === 'local' ? (string) url('front_local') : $server;
        $data   = [
            'file_id'    => $info->id,
            'file_url'   => $info->url,
            'name'       => $info->name,
            'filename'   => $info->filename,
            'extension'  => $info->extension,
            'path'       => $info->path,
            'fast'       => $info->fast,
            'upload_id'  => $result->getUploadId(),
            'server_url' => $server,
        ];
        
        $this->log()->record(
            self::LOG_INSERT,
            $info->fast ? '秒传文件' : '准备上传',
            json_encode($result, JSON_UNESCAPED_UNICODE)
        );
        
        return $this->success($data);
    }
    
    
    /**
     * 前端上传获取临时令牌
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function front_token() : Response
    {
        return $this->success($this->model->frontTmpToken($this->post('file_id/d')));
    }
    
    
    /**
     * 前端完成上传
     * @return Response
     * @throws Throwable
     */
    public function front_done() : Response
    {
        $this->model->frontDoneUpload(
            $this->post('file_id/d'),
            $this->post('upload_id/s', 'trim'),
            json_decode($this->post('parts/s', 'trim'), true) ?: []
        );
        
        $this->log()->record(self::LOG_UPDATE, '完成上传');
        
        return $this->success('上传完成');
    }
    
    
    /**
     * 前端上传整个文件或分块
     * @return Response
     * @throws Throwable
     */
    public function front_local() : Response
    {
        $this->request->setRequestIsAjax();
        
        $etag = $this->model->frontLocalUpload(
            $this->post('file_id/d'),
            $this->request->file('upload'),
            $this->post('upload_id/s', 'trim'),
            $this->post('part_number/d')
        );
        
        return $this->success()->header(['ETag' => $etag]);
    }
    
    
    /**
     * 普通上传文件
     * @throws Throwable
     */
    public function upload() : Response
    {
        $this->request->setRequestIsAjax();
        $parameter = new SystemFileUploadParameter(new LocalParameter($this->request->file('upload')));
        $parameter->setUserId($this->adminUserId);
        $parameter->setClassType($this->post('class_type/s', 'trim'));
        $parameter->setClassValue($this->post('class_value/s', 'trim'));
        $parameter->setDisk($this->post('disk/s', 'trim'));
        $result = $this->model->upload($parameter);
        $data   = [
            'file_url'  => $result->url,
            'file_id'   => $result->id,
            'name'      => $result->name,
            'filename'  => $result->filename,
            'extension' => $result->extension,
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
    public function picker() : Response
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
        
        $isImage = $fileType === SystemFile::FILE_TYPE_IMAGE;
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
        $this->assign('is_image', $isImage);
        $this->assign('info', $this->list($this->model->whereComplete(), $isImage ? 20 : 30)->select());
        
        return $this->display();
    }
    
    
    /**
     * 上传配置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function config() : Response
    {
        $setting   = StorageSetting::init();
        $fileClass = [];
        foreach (SystemFileClass::init()->getList() as $key => $item) {
            $fileClass[$key] = [
                'size'             => $setting->getMaxSize($key), // TODO 移除
                'suffix'           => implode(',', $setting->getAllowExtensions($key)), // TODO 移除
                'mime'             => implode(',', $setting->getMimeType($key)),  // TODO 移除
                'max_size'         => $setting->getMaxSize($key),
                'allow_extensions' => implode(',', $setting->getAllowExtensions($key)),
                'allow_mimetypes'  => implode(',', $setting->getMimeType($key)),
                'type'             => $item['type'],
                'name'             => $item['name']
            ];
        }
        
        // 遍历磁盘
        $injectScripts = [];
        foreach ($setting->getDisks() as $disk) {
            $type            = $disk['type'];
            $injectScript    = FrontUploadInjectScriptPlugin::getInstance($disk['type'])->injectScript();
            $injectScripts[] = <<<JS
config.disks['$type'] = (function (){
    var exports = {};
    $injectScript;
    return exports;
})();
JS;
        }
        $injectScripts = implode('', $injectScripts);
        
        $config = json_encode([
            'url'     => (string) url('upload'),
            'prepare' => (string) url('front_prepare'),
            'done'    => (string) url('front_done'),
            'token'   => (string) url('front_token'),
            'picker'  => (string) url('picker?class_type=_type_&class_value=_value_&extensions=_extensions_'),
            'config'  => $fileClass ?: new stdClass(),
            'disk'    => $setting->getDisk()
        ], JSON_UNESCAPED_UNICODE);
        
        
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
    var config = $config;
    
   
    config.disks = {};
    
    /**
     * 初始化异步回调
     * config.disk.key.asyncInit = function
     * @method {{asyncInit}}
     * @param {{}} options 配置
     */
    
    /**
     * 异步上传文件前回调(还没有切割分块)
     * config.disk.key.asyncBeforeSendFile = function
     * @param {busyAdmin.UploadFile} file 文件数据
     * @param {busyAdmin.UploadPrepareResult} result 准备上传返回数据结构
     */
    
    /**
     * 异步文件发送前回调(如果有分块，此时可以处理了)
     * config.disk.key.asyncBeforeSend = function
     * @param {busyAdmin.UploadBlock} block 块数据
     */
    
    /**
     * 同步文件发送前回调(如果有分块，此时可以处理了)
     * config.disk.key.syncBeforeSend = function
     * @param {busyAdmin.UploadBlock} block 块数据
     * @param {{}} params HTTP参数
     * @param {{}} headers HTTP头
     */
   
    /**
     * 每一个分块或文件上传结果解析，返回false代表上传失败
     * config.disk.key.uploadAccept = function
     * @param {busyAdmin.UploadBlock} block 块数据
     * @param {{_raw: string}} response 响应内容
     * @param {(result: {}) => void} resultCallback 响应结果回调
     * @param {(errorMsg: string) => void} errorCallback 响应错误回调
     * @return {boolean}
     */
    
    /**
     * 所有文件上传完毕回调
     * config.disk.key.asyncAfterSendFile = function
     * @param {busyAdmin.UploadFile} file 文件数据
     * @param {{_raw: string, _result: {}|null}} result 响应内容
     */
    
    // use-script-start
    $injectScripts;
    // use-script-end
    
    return config;
}));
JS;
        
        return Response::create($script)->contentType(FileHelper::getMimetypeByExtension('js'));
    }
}