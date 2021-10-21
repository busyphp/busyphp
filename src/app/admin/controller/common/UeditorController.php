<?php

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\setting\UploadSetting;
use BusyPHP\exception\VerifyException;
use BusyPHP\file\upload\Base64Upload;
use BusyPHP\file\upload\LocalUpload;
use BusyPHP\file\upload\RemoteUpload;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;

/**
 * 百度UEditor编辑器
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
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
    public function server()
    {
        $action = $this->param('action/s', 'trim');
        $method = 'server' . ucfirst($action);
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        
        return $this->json(['state' => '非法请求']);
    }
    
    
    public function display($template = '', $charset = 'utf-8', $contentType = '', $content = '')
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
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function insert_image()
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
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function insert_attachment()
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
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function insert_video()
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
    public function scrawl()
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
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function word_image()
    {
        return $this->insert_image();
    }
    
    
    /**
     * 编辑器运行JS
     * @return Response
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function runtime()
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
    private function upload($isBase64 = false)
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
                $upload = new Base64Upload();
                $upload->setUserId($this->adminUserId);
                $upload->setClassType($classType, $classValue);
                $upload->setDefaultMimeType('image/jpg');
                $upload->setDefaultExtension('jpg');
                $result = $upload->upload($this->post('upload/s', 'trim'));
            } else {
                $upload = new LocalUpload();
                $upload->setUserId($this->adminUserId);
                $upload->setClassType($classType, $classValue);
                $result = $upload->upload($this->request->file('upload'));
            }
            
            $jsonData['url']      = $result->url;
            $jsonData['title']    = $result->name;
            $jsonData['original'] = $result->name;
            $jsonData['type']     = $result->file->getExtension();
            $jsonData['size']     = $result->file->getSize();
            
            $this->log()
                ->filterParams($isBase64 ? ['upload'] : [])
                ->record(self::LOG_INSERT, 'UEditor上传文件', json_encode($jsonData, JSON_UNESCAPED_UNICODE));
        } catch (Exception $e) {
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
    private function serverConfig()
    {
        return $this->json([]);
    }
    
    
    /**
     * 上传远程图片
     */
    private function serverRemote()
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
                    $upload = new RemoteUpload();
                    $upload->setUserId($this->adminUserId);
                    $upload->setClassType($classType, $classValue);
                    $upload->setDefaultExtension('jpg');
                    $upload->setDefaultMimeType('image/jpg');
                    $result = $upload->upload($url);
                    
                    $list[$i]  = [
                        'state'   => 'SUCCESS',
                        'source'  => $url,
                        'url'     => $result->url,
                        'file_id' => $result->id
                    ];
                    $success[] = $list[$i];
                } catch (Exception $e) {
                    $list[$i] = ['state' => $e->getMessage(), 'source' => '', 'url' => ''];
                }
            }
            
            $jsonData['list'] = $list;
            
            if ($success) {
                $this->log()->record(self::LOG_INSERT, 'UEditor抓取图片', json_encode($success, JSON_UNESCAPED_UNICODE));
            }
        } catch (Exception $e) {
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
    private function serverUpload()
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
    private function getFileConfig()
    {
        $fileSet = UploadSetting::init();
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