<?php

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\file\SystemFileUploadParameter;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\setting\StorageSetting;
use BusyPHP\exception\VerifyException;
use BusyPHP\upload\parameter\Base64Parameter;
use BusyPHP\upload\parameter\LocalParameter;
use BusyPHP\upload\parameter\RemoteParameter;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;
use think\response\Json;
use think\response\View;
use Throwable;

/**
 * 百度UEditor编辑器
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/8/28 下午上午9:52 UeditorController.php $
 */
class UeditorController extends InsideController
{
    public function initialize($checkLogin = false)
    {
        parent::initialize($checkLogin);
    }
    
    
    /**
     * 基础服务入口
     */
    public function server() : Json
    {
        $action = $this->param('action/s', 'trim');
        $method = 'server' . ucfirst($action);
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        
        return $this->json(['state' => '非法请求']);
    }
    
    
    public function display($template = '', $charset = 'utf-8', $contentType = '', $content = '') : Response
    {
        // 是否输出JS
        if ($this->get('js/b')) {
            $charset     = 'utf-8';
            $contentType = 'application/x-javascript';
            $template    = $this->getTemplatePath() . $this->request->action() . '.js';
        }
        
        return parent::display($template, $charset, $contentType, $content);
    }
    
    
    /**
     * 插入图片
     * @return Response
     * @throws Throwable
     */
    public function insert_image() : Response
    {
        if ($this->isAjax()) {
            return $this->json($this->upload());
        } else {
            $this->assign('upload_url', url('', [$this->request->getVarAjax() => 1]));
            $this->assign('file_config', json_encode($this->getFileConfig()));
            
            return $this->display();
        }
    }
    
    
    /**
     * 插入附件
     * @return Response
     * @throws Throwable
     */
    public function insert_attachment() : Response
    {
        if ($this->isAjax()) {
            return $this->json($this->upload());
        } else {
            $this->assign('upload_url', url('', [$this->request->getVarAjax() => 1]));
            $this->assign('file_config', json_encode($this->getFileConfig()));
            
            return $this->display();
        }
    }
    
    
    /**
     * 插入视频
     * @return Response
     * @throws Throwable
     */
    public function insert_video() : Response
    {
        if ($this->isAjax()) {
            return $this->json($this->upload());
        } else {
            $this->assign('upload_url', url('', [$this->request->getVarAjax() => 1]));
            $this->assign('file_config', json_encode($this->getFileConfig()));
            
            return $this->display();
        }
    }
    
    
    /**
     * 涂鸦
     */
    public function scrawl() : Response
    {
        if ($this->isAjax()) {
            return $this->json($this->upload(true));
        } else {
            $this->assign('upload_url', url('', [$this->request->getVarAjax() => 1]));
            
            return $this->display();
        }
    }
    
    
    /**
     * word图片转存
     * @return Response
     * @throws Throwable
     */
    public function word_image() : Response
    {
        return $this->insert_image();
    }
    
    
    /**
     * 编辑器运行JS
     * @return Response
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function runtime() : Response
    {
        $fileConfig          = json_encode($this->getFileConfig(), JSON_UNESCAPED_UNICODE);
        $pageBreakTag        = $this->app->config->get('app.VAR_CONTENT_PAGE', '');
        $serverUrl           = url('server');
        $host                = $this->request->host(true);
        $insertImageUrl      = url('insert_image');
        $insertVideoUrl      = url('insert_video');
        $insertAttachmentUrl = url('insert_attachment');
        $scrawlUrl           = url('scrawl');
        $wordImageUrl        = url('word_image');
        $script              = <<<JS
// UEditor运行配置
window.UEDITOR_CONFIG = {
    busyFileConfig : {$fileConfig},

    serverUrl           : '{$serverUrl}',
    pageBreakTag        : '{$pageBreakTag}',
    listiconpath        : '',
    emotionLocalization : '',

    // 图片上传配置
    imageActionName     : 'upload',
    imageFieldName      : 'upload',
    imageUrlPrefix      : '',
    imageCompressEnable : false,
    imageCompressBorder : 1600,

    // 截图工具上传
    snapscreenActionName : 'snapscreen',
    snapscreenUrlPrefix  : '',

    // 抓取远程图片配置
    catcherLocalDomain : ["127.0.0.1", "localhost", "{$host}"],
    catcherActionName  : 'remote',
    catcherFieldName   : 'upload',
    catcherUrlPrefix   : '',

    // 重新定义dialog页面
    iframeUrlMap : {
        insertimage : '{$insertImageUrl}',
        insertvideo : '{$insertVideoUrl}',
        attachment  : '{$insertAttachmentUrl}',
        scrawl      : '{$scrawlUrl}',
        wordimage   : '{$wordImageUrl}'
    }
};
JS;
        
        return Response::create($script)->contentType('application/javascript');
    }
    
    
    /**
     * 执行上传
     * @param bool $isBase64
     * @return array
     */
    private function upload(bool $isBase64 = false) : array
    {
        $jsonData          = [];
        $jsonData['state'] = 'SUCCESS';
        try {
            if (!$this->isLogin()) {
                throw new VerifyException('请登录后上传', 'need_login');
            }
            
            // 上传
            $classType  = $this->param('class_type/s', 'trim');
            $classValue = $this->param('class_value/s', 'trim');
            if ($isBase64) {
                // TODO mimetype 和 basename
                $driverParameter = new Base64Parameter($this->post('upload/s', 'trim'));
            } else {
                $driverParameter = new LocalParameter($this->request->file('upload'));
            }
            
            $parameter = new SystemFileUploadParameter($driverParameter);
            $parameter->setUserId($this->adminUserId);
            $parameter->setClassType($classType);
            $parameter->setClassValue($classValue);
            $result = SystemFile::init()->upload($parameter);
            
            $jsonData['url']      = $result->url;
            $jsonData['title']    = $result->name;
            $jsonData['original'] = $result->name;
            $jsonData['type']     = $result->extension;
            $jsonData['size']     = $result->size;
            
            $this->log()
                ->filterParams($isBase64 ? ['upload'] : [])
                ->record(self::LOG_INSERT, 'UEditor上传文件', json_encode($jsonData, JSON_UNESCAPED_UNICODE));
        } catch (Throwable $e) {
            $jsonData['state'] = $e->getMessage();
        }
        
        if ($this->app->isDebug()) {
            $jsonData['trace'] = trace();
        }
        
        return $jsonData;
    }
    
    
    /**
     * 配置
     */
    private function serverConfig() : Json
    {
        return $this->json([]);
    }
    
    
    /**
     * 上传远程图片
     */
    private function serverRemote() : Json
    {
        $jsonData          = [];
        $jsonData['state'] = 'SUCCESS';
        try {
            if (!$this->isLogin()) {
                throw new VerifyException('请登录后上传', 'need_login');
            }
            
            $classType  = $this->post('class_image_type/s', 'trim');
            $classValue = $this->post('class_value/s', 'trim');
            $list       = [];
            $success    = [];
            foreach ($this->post('upload') as $i => $url) {
                $url = str_replace('&amp;', '&', $url);
                try {
                    // TODO 默认mimetype 和 basename
                    $remote    = new RemoteParameter($url);
                    $parameter = new SystemFileUploadParameter($remote);
                    $parameter->setUserId($this->adminUserId);
                    $parameter->setClassType($classType);
                    $parameter->setClassValue($classValue);
                    $result    = SystemFile::init()->upload($parameter);
                    $list[$i]  = [
                        'state'   => 'SUCCESS',
                        'source'  => $url,
                        'url'     => $result->url,
                        'file_id' => $result->id
                    ];
                    $success[] = $list[$i];
                } catch (Throwable $e) {
                    $list[$i] = ['state' => $e->getMessage(), 'source' => '', 'url' => ''];
                }
            }
            
            $jsonData['list'] = $list;
            
            if ($success) {
                $this->log()->record(self::LOG_INSERT, 'UEditor抓取图片', json_encode($success, JSON_UNESCAPED_UNICODE));
            }
        } catch (Throwable $e) {
            $jsonData['state'] = $e->getMessage();
        }
        
        if ($this->app->isDebug()) {
            $jsonData['trace'] = trace();
        }
        
        return $this->json($jsonData);
    }
    
    
    /**
     * 单图上传
     */
    private function serverUpload() : Response
    {
        $this->request->setParam('class_type', $this->param('class_image_type/s', 'trim'));
        
        return $this->json($this->upload());
    }
    
    
    /**
     * 获取配置
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     */
    private function getFileConfig() : array
    {
        $fileSet = StorageSetting::init();
        $list    = SystemFileClass::init()->getList();
        $array   = [];
        foreach ($list as $key => $value) {
            $array[$key] = [
                'suffix' => $fileSet->getAllowExtensions($key),
                'size'   => $fileSet->getMaxSize($key),
                'mime'   => $fileSet->getMimeType($key),
                'type'   => $value->type,
                'name'   => $value->name
            ];
        }
        
        return $array;
    }
}