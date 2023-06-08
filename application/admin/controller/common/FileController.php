<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\component\common\SimpleQuery;
use BusyPHP\app\admin\component\filesystem\Driver;
use BusyPHP\app\admin\component\js\Driver as JsDriver;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\model\system\file\SystemFileField;
use BusyPHP\app\admin\model\system\file\SystemFileFrontPrepareUploadData;
use BusyPHP\app\admin\model\system\file\SystemFileUploadData;
use BusyPHP\app\admin\setting\StorageSetting;
use BusyPHP\helper\FileHelper;
use BusyPHP\helper\FilterHelper;
use BusyPHP\uploader\driver\Local;
use BusyPHP\uploader\driver\local\LocalData;
use League\Flysystem\FilesystemException;
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
     * 文件模型
     * @var SystemFile
     */
    protected SystemFile $model;
    
    /**
     * 文件模型字段类
     * @var string|SystemFileField
     */
    protected mixed $field;
    
    
    protected function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model = SystemFile::init();
        $this->field = $this->model->getFieldClass();
    }
    
    
    /**
     * 前端准备上传
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws FilesystemException
     */
    public function front_prepare() : Response
    {
        $parameter = new SystemFileFrontPrepareUploadData(
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
        return $this->success($this->model->getFrontTmpToken($this->post('file_id/d')));
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
        $this->request->setToAjax();
        
        $result = $this->model->frontLocalUpload(
            $this->post('file_id/d'),
            $this->request->file('upload'),
            $this->post('upload_id/s', 'trim'),
            $this->post('part_number/d')
        );
        
        return $this->success()->header(['ETag' => $result['etag']]);
    }
    
    
    /**
     * 普通上传文件
     * @throws Throwable
     */
    public function upload() : Response
    {
        $this->request->setToAjax();
        $uploadData = new SystemFileUploadData(Local::class, new LocalData($this->request->file('upload')));
        $uploadData->setUserId($this->adminUserId);
        $uploadData->setClassType($this->post('class_type/s', 'trim'));
        $uploadData->setClassValue($this->post('class_value/s', 'trim'));
        $uploadData->setDisk($this->post('disk/s', 'trim'));
        $result = $this->model->upload($uploadData);
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
        if (JsDriver::getRequestName() == 'FilePicker') {
            $classType  = $this->param('class_type/s', 'trim');
            $classValue = $this->param('class_value/s', 'trim');
            $extensions = $this->param('extensions/s', 'trim');
            $extensions = FilterHelper::trimArray(explode(',', $extensions));
            $type       = $this->param('type/s', 'trim');
            $category   = $this->param('category/s', 'trim');
            $word       = $this->param('word/s', 'trim');
            
            // 类型搜索
            if ($type) {
                $this->model->where($this->field::type($type));
            }
            
            // 按当前信息查询
            if (!$category) {
                // 按分类搜索
                if ($classType) {
                    $this->model->where($this->field::classType($classType));
                }
                
                $this->model->where($this->field::classValue($classValue));
            } elseif ($category !== ':all') {
                $this->model->where($this->field::classType($category));
            }
            
            // 按扩展名搜索
            if (!$extensions && $classType) {
                $extensions = StorageSetting::instance()->getAllowExtensions($classType);
            }
            if ($extensions) {
                $this->model->where($this->field::extension('in', array_map('strtolower', $extensions)));
            }
            
            // 关键词搜索
            if ($word) {
                $word = FilterHelper::searchWord($word);
                $this->model->where($this->field::name('like', '%' . $word . '%'));
            }
            
            $result = SimpleQuery::init($this->model->whereComplete()->order($this->field::id(), 'desc'))->build();
            
            return $this->success([
                'list'       => $result->list,
                'total'      => $result->total,
                'limit'      => $result->limit,
                'page'       => str_replace('href="', 'href="javascript:void(0)" data-url="', $result->page),
                'extensions' => implode(',', $extensions)
            ]);
        }
        
        $this->assign('info', [
            'type_map'     => SystemFile::class()::getTypes() ?: new stdClass(),
            'category_map' => SystemFileClass::init()->getList() ?: new stdClass()
        ]);
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 上传配置
     * @return Response
     */
    public function config() : Response
    {
        // 遍历磁盘
        $injectScripts = [];
        $setting       = StorageSetting::instance();
        foreach ($setting::getDisks() as $disk) {
            $type            = $disk['type'];
            $injectScript    = Driver::getInstance($disk['type'])->frontUploadInjectScript();
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
            'url'      => (string) url('upload'),
            'prepare'  => (string) url('front_prepare'),
            'done'     => (string) url('front_done'),
            'token'    => (string) url('front_token'),
            'picker'   => (string) url('picker'),
            'category' => SystemFileClass::instance()->getUploadCategory() ?: new stdClass(),
            'disk'     => $setting->getDisk()
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