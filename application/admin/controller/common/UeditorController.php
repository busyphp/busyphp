<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\model\system\file\SystemFileUploadData;
use BusyPHP\app\admin\setting\StorageSetting;
use BusyPHP\exception\VerifyException;
use BusyPHP\uploader\driver\Base64;
use BusyPHP\uploader\driver\base64\Base64Data;
use BusyPHP\uploader\driver\Local;
use BusyPHP\uploader\driver\local\LocalData;
use BusyPHP\uploader\driver\Remote;
use BusyPHP\uploader\driver\remote\RemoteData;
use stdClass;
use think\Response;
use think\response\Json;
use Throwable;

/**
 * 百度UEditor编辑器
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/8/28 下午上午9:52 UeditorController.php $
 */
class UeditorController extends InsideController
{
    protected function initialize($checkLogin = false)
    {
        parent::initialize($checkLogin);
    }
    
    
    protected function insideDisplay($template = '', $charset = 'utf-8', $contentType = '', $content = '', array $config = []) : Response
    {
        // 输出JS
        if ($this->get('js/b')) {
            $charset               = 'utf-8';
            $contentType           = 'application/x-javascript';
            $config['view_suffix'] = 'js';
        }
        
        return parent::insideDisplay($template, $charset, $contentType, $content, $config);
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
    
    
    /**
     * 插入图片
     * @return Response
     * @throws Throwable
     */
    public function insert_image() : Response
    {
        if ($this->isAjax()) {
            return $this->doUpload();
        } else {
            $this->assign('upload_url', url('', [$this->request->getVarAjax() => 1]));
            $this->assign('category', json_encode(SystemFileClass::instance()->getUploadCategory() ?: new stdClass()));
            
            return $this->insideDisplay();
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
            return $this->doUpload();
        } else {
            $this->assign('upload_url', url('', [$this->request->getVarAjax() => 1]));
            $this->assign('category', json_encode(SystemFileClass::instance()->getUploadCategory() ?: new stdClass()));
            
            return $this->insideDisplay();
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
            return $this->doUpload();
        } else {
            $this->assign('upload_url', url('', [$this->request->getVarAjax() => 1]));
            $this->assign('category', json_encode(SystemFileClass::instance()->getUploadCategory() ?: new stdClass()));
            
            return $this->insideDisplay();
        }
    }
    
    
    /**
     * 涂鸦
     */
    public function scrawl() : Response
    {
        if ($this->isAjax()) {
            return $this->doUpload(true);
        } else {
            $this->assign('upload_url', url('', [$this->request->getVarAjax() => 1]));
            
            return $this->insideDisplay();
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
     */
    public function runtime() : Response
    {
        $category            = json_encode(SystemFileClass::instance()
            ->getUploadCategory() ?: new stdClass(), JSON_UNESCAPED_UNICODE);
        $pageBreakTag        = $this->app->config->get('app.VAR_CONTENT_PAGE', '');
        $serverUrl           = url('server');
        $insertImageUrl      = url('insert_image');
        $insertVideoUrl      = url('insert_video');
        $insertAttachmentUrl = url('insert_attachment');
        $scrawlUrl           = url('scrawl');
        $wordImageUrl        = url('word_image');
        $domains             = json_encode(StorageSetting::instance()
            ->getRemoteIgnoreDomains(), JSON_UNESCAPED_UNICODE);
        
        $script = <<<JS
// UEditor运行配置
window.UEDITOR_CONFIG = {
    uploadCategory : {$category},

    serverUrl           : '$serverUrl',
    pageBreakTag        : '$pageBreakTag',
    listiconpath        : '',
    emotionLocalization : '',

    // 图片上传配置
    imageActionName     : 'upload',
    imageFieldName      : 'upload',
    imageUrlPrefix      : '',
    imageCompressEnable : false,
    imageCompressBorder : 1600,
    imageAllowFiles     : ['.jpeg', '.jpg', '.png', '.gif', '.webp', '.bmp'],
    imageMaxSize        : 2 * 1024 * 1024,
    
    // 视频上传配置
    videoAllowFiles     : ['.mp4', '.webm', '.ogv'],
    videoMaxSize        : 10 * 1024 * 1024,
    
    // 上传文件配置
    fileAllowFiles      : [".png", ".jpg", ".jpeg", ".gif", ".bmp", ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg", ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid", ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso", ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"],
    fileMaxSize         : 50 * 1024 * 1024,

    // 截图工具上传
    snapscreenActionName : 'snapscreen',
    snapscreenUrlPrefix  : '',

    // 抓取远程图片配置
    catcherLocalDomain : $domains,
    catcherActionName  : 'remote',
    catcherFieldName   : 'upload',
    catcherUrlPrefix   : '',

    // 重新定义dialog页面
    iframeUrlMap : {
        insertimage : '$insertImageUrl',
        insertvideo : '$insertVideoUrl',
        attachment  : '$insertAttachmentUrl',
        scrawl      : '$scrawlUrl',
        wordimage   : '$wordImageUrl'
    }
};
JS;
        
        return Response::create($script)->contentType('application/javascript');
    }
    
    
    /**
     * 执行上传
     * @param bool $isBase64
     * @return Response
     */
    protected function doUpload(bool $isBase64 = false) : Response
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
                $data   = new Base64Data($this->post('upload/s', 'trim'));
                $driver = Base64::class;
            } else {
                $data   = new LocalData($this->request->file('upload'));
                $driver = Local::class;
            }
            
            $parameter = new SystemFileUploadData($driver, $data);
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
        
        return $this->json($jsonData);
    }
    
    
    /**
     * 配置
     */
    protected function serverConfig() : Json
    {
        return $this->json([]);
    }
    
    
    /**
     * 上传远程图片
     */
    protected function serverRemote() : Json
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
                    $remote = new RemoteData($url);
                    $remote->setIgnoreHosts(StorageSetting::instance()->getRemoteIgnoreDomains());
                    $parameter = new SystemFileUploadData(Remote::class, $remote);
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
                $this->log()
                    ->record(self::LOG_INSERT, 'UEditor抓取图片', json_encode($success, JSON_UNESCAPED_UNICODE));
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
    protected function serverUpload() : Response
    {
        $this->request->setParam('class_type', $this->param('class_image_type/s', 'trim'));
        
        return $this->doUpload();
    }
}