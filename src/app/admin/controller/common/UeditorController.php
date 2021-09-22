<?php

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\setting\UploadSetting;
use BusyPHP\exception\AppException;
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
        $action = $this->iRequest('action');
        $method = 'server' . ucfirst($action);
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        
        return $this->json(['state' => '非法请求']);
    }
    
    
    public function display($template = '', $charset = 'utf-8', $contentType = '', $content = '')
    {
        // 插件路径
        $this->assignUrl('plugin', $this->request->getWebAssetsUrl() . 'admin/lib/ueditor/');
        
        // 是否输出JS
        $isJs = $this->iGet('js', 'intval');
        if ($isJs) {
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
        $this->assign('file_config', json_encode($this->getFileConfig()));
        
        return $this->display();
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
            if (!$this->checkLogin()) {
                throw new AppException('请登录后上传');
            }
            
            // 上传
            $classType  = $this->request->post('class_type', '', 'trim');
            $classValue = $this->request->post('class_value', '', 'trim');
            if ($isBase64) {
                $upload = new Base64Upload();
                $upload->setClient(0, $this->adminUserId);
                $upload->setClassType($classType, $classValue);
                $upload->setDefaultMimeType('image/jpg');
                $upload->setDefaultExtension('jpg');
                $result = $upload->upload($this->request->post('upload'));
            } else {
                $upload = new LocalUpload();
                $upload->setClient(0, $this->adminUserId);
                $upload->setClassType($classType, $classValue);
                $result = $upload->upload($this->request->file('upload'));
            }
            
            $jsonData['url']      = $result->url;
            $jsonData['title']    = $result->name;
            $jsonData['original'] = $result->name;
            $jsonData['type']     = $result->file->getExtension();
            $jsonData['size']     = $result->file->getSize();
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
            if (!$this->checkLogin()) {
                throw new AppException('请登录后上传');
            }
            
            $classType  = $this->request->post('mark_image_type', '', 'trim');
            $classValue = $this->request->post('mark_value', '', 'trim');
            $list       = [];
            foreach ($this->iPost('upload') as $i => $url) {
                $url = str_replace('&amp;', '&', $url);
                try {
                    $upload = new RemoteUpload();
                    $upload->setClient(0, $this->adminUserId);
                    $upload->setClassType($classType, $classValue);
                    $upload->setDefaultExtension('jpg');
                    $upload->setDefaultMimeType('image/jpg');
                    $result = $upload->upload($url);
                    
                    $list[$i] = ['state' => 'SUCCESS', 'source' => $url, 'url' => $result->url];
                } catch (Exception $e) {
                    $list[$i] = ['state' => $e->getMessage(), 'source' => '', 'url' => ''];
                }
            }
            
            $jsonData['list'] = $list;
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
        $this->request->setRequest('mark_type', $this->iRequest('mark_image_type'));
        
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
                'suffix' => $fileSet->getAllowExtensions(0, $key),
                'size'   => $fileSet->getMaxSize(0, $key),
                'mime'   => $fileSet->getMimeType($key),
                'type'   => $value->type,
                'name'   => $value->name
            ];
        }
        
        return $array;
    }
}