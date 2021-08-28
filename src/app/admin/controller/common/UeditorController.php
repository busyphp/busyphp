<?php

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\App;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\helper\util\Transform;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\file\SystemFileUpload;
use BusyPHP\app\admin\setting\FileSetting;
use BusyPHP\exception\AppException;
use Exception;
use stdClass;
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
            if ($isBase64) {
                $upload = new SystemFileUpload();
                $upload->setType($upload::TYPE_BASE64);
                $upload->setBase64DefaultExtension('jpg', 'image/jpg');
                $upload->setIsAdmin(true);
                $upload->setUserId($this->adminUserId);
                $upload->setMark($this->iRequest('mark_type'), $this->iRequest('mark_value'));
                $result = $upload->upload($_POST['upload']);
            } else {
                $upload = new SystemFileUpload();
                $upload->setType($upload::TYPE_CHUNK);
                $upload->setIsAdmin(true);
                $upload->setUserId($this->adminUserId);
                $upload->setMark($this->iRequest('mark_type'), $this->iRequest('mark_value'));
                $result = $upload->upload($_FILES['upload']);
            }
            
            $jsonData['url']      = $result->url;
            $jsonData['title']    = $result->name;
            $jsonData['original'] = $result->name;
            $jsonData['type']     = $result->extension;
            $jsonData['size']     = $result->size;
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
            
            // 上传
            $upload = new SystemFileUpload();
            $upload->setType($upload::TYPE_REMOTE);
            $upload->setRemoteFilterHost($_SERVER['HTTP_HOST']);
            $upload->setRemoteDefaultExtension('jpg', 'image/jpg');
            $upload->setIsAdmin(true);
            $upload->setUserId($this->adminUserId);
            $upload->setMark($this->iRequest('mark_image_type'), $this->iRequest('mark_value'));
            
            $list = [];
            foreach ($this->iPost('upload') as $i => $url) {
                $url = str_replace('&amp;', '&', $url);
                try {
                    $result   = $upload->upload($url);
                    $list[$i] = ['state' => 'SUCCESS', 'source' => $url, 'url' => $result->url];
                } catch (AppException $e) {
                    $list[$i] = ['state' => $e->getMessage(), 'source' => '', 'url' => ''];
                }
            }
            
            $jsonData['list'] = $list;
        } catch (AppException $e) {
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
        $fileSet = FileSetting::init();
        $list    = SystemFileClass::init()->getList();
        $array   = [];
        foreach ($list as $key => $value) {
            $fileSet->setClassify($key);
            $array[$key] = [
                'suffix'  => $fileSet->getAdminType(),
                'size'    => $fileSet->getAdminSize(),
                'mime'    => $fileSet->getMimeType(),
                'width'   => $fileSet->getThumbWidth(),
                'height'  => $fileSet->getThumbHeight(),
                'isThumb' => $fileSet->isThumb(),
                'thumb'   => Transform::boolToNumber($fileSet->isThumb()),
                'type'    => $value['type'],
                'name'    => $value['name']
            ];
        }
        
        return $array;
    }
}