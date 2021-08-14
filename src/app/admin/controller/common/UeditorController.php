<?php

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\App;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\helper\util\Filter;
use BusyPHP\helper\util\Transform;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\file\SystemFileUpload;
use BusyPHP\app\admin\setting\FileSetting;
use BusyPHP\exception\AppException;
use stdClass;

/**
 * 百度Ueditor编辑器
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/16 下午2:58 下午 Ueditor.php $
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
     */
    public function insert_image()
    {
        // 在线图片管理
        if ($this->isAjax()) {
            // 上传
            if (isset($_FILES['upload'])) {
                return $this->json($this->upload());
            }
            
            
            // 搜索
            $jsonData          = [];
            $jsonData['state'] = 'SUCCESS';
            
            try {
                if (!$this->checkLogin()) {
                    throw new AppException('请登录后操作');
                }
                
                $word        = $this->iRequest('word', 'trim');
                $markType    = $this->iRequest('mark_type', 'trim');
                $markValue   = $this->iRequest('mark_value', 'trim');
                $newMarkType = $this->iRequest('new_mark_type', 'trim', 0);
                $isSelf      = $this->iRequest('is_self', 'intval');
                $type        = $isSelf ? $markType : $newMarkType;
                $start       = $this->iRequest('start', 'intval');
                $size        = $this->iRequest('size', 'intval');
                
                // 搜索条件
                $where              = [];
                $where['extension'] = ['in', ['jpg', 'jpeg', 'png', 'gif', 'bmp']];
                
                // 当前信息
                if ($isSelf && $markValue) {
                    $where['mark_value'] = $markValue;
                }
                
                // 按照类型搜索
                if ($type && $type != SystemFile::FILE_TYPE_IMAGE) {
                    $where['mark_type'] = $type;
                }
                
                // 按关键字搜索
                if ($word) {
                    $where['name'] = ['like', '%' . Filter::searchWord($word) . '%'];
                }
                
                $model            = SystemFile::init();
                $count            = floatval($model->whereof($where)->count());
                $jsonData['list'] = [];
                
                // 有数据
                if ($count > 0) {
                    $list              = $model->field('id,url,name,size')
                        ->whereof($where)
                        ->order('id DESC')
                        ->limit($start, $size)
                        ->selecting();
                    $jsonData['list']  = $list;
                    $jsonData['total'] = $count;
                    $jsonData['start'] = $start;
                } else {
                    $jsonData['total'] = 0;
                    $jsonData['state'] = '没有找到相关图片';
                }
                
                if ($this->app->isDebug()) {
                    $jsonData['trace'] = trace();
                }
            } catch (AppException $e) {
                $jsonData['state'] = $e->getMessage();
            }
            
            return $this->json($jsonData);
        } else {
            $url = url('', [$this->request->getVarAjax() => 1]);
            $this->assign('search_url', $url);
            $this->assign('upload_url', $url);
            $this->assign('image_options', SystemFileClass::init()->getAdminImageOptions('', false));
            $this->assign('file_config', json_encode($this->getFileConfig()));
            $this->assign('default_type', SystemFile::FILE_TYPE_IMAGE);
            
            return $this->display();
        }
    }
    
    
    /**
     * 插入附件
     */
    public function insert_attachment()
    {
        // 在线附件管理
        if ($this->isAjax()) {
            // 上传
            if (isset($_FILES['upload'])) {
                return $this->json($this->upload());
            }
            
            // 搜索
            $jsonData          = [];
            $jsonData['state'] = 'SUCCESS';
            try {
                if (!$this->checkLogin()) {
                    throw new AppException('请登录后操作');
                }
                
                // 搜索
                $word        = $this->iRequest('word', 'trim');
                $markType    = $this->iRequest('mark_type', 'trim');
                $markValue   = $this->iRequest('mark_value', 'trim');
                $newMarkType = $this->iRequest('new_mark_type', 'trim', 0);
                $isSelf      = $this->iRequest('is_self', 'intval');
                $type        = $isSelf ? $markType : $newMarkType;
                $start       = $this->iRequest('start', 'intval');
                $size        = $this->iRequest('size', 'intval');
                
                // 搜索条件
                $where             = [];
                $where['classify'] = SystemFile::FILE_TYPE_FILE;
                
                // 当前信息
                if ($isSelf && $markValue) {
                    $where['mark_value'] = $markValue;
                }
                
                // 按照类型搜索
                if ($type && $type != SystemFile::FILE_TYPE_FILE) {
                    $where['mark_type'] = $type;
                }
                
                // 按关键字搜索
                if ($word) {
                    $where['name'] = ['like', '%' . Filter::searchWord($word) . '%'];
                }
                
                $model            = SystemFile::init();
                $count            = floatval($model->whereof($where)->count());
                $jsonData['list'] = [];
                
                // 有数据
                if ($count > 0) {
                    $list              = $model->field('id,url,name,size')
                        ->whereof($where)
                        ->order('id DESC')
                        ->limit($start, $size)
                        ->selecting();
                    $jsonData['list']  = $list;
                    $jsonData['total'] = $count;
                    $jsonData['start'] = $start;
                } else {
                    $jsonData['total'] = 0;
                    $jsonData['state'] = '没有找到相关附件';
                }
            } catch (AppException $e) {
                $jsonData['state'] = $e->getMessage();
            }
            
            if ($this->app->isDebug()) {
                $jsonData['trace'] = trace();
            }
            
            return $this->json($jsonData);
        } else {
            $url = url('', [$this->request->getVarAjax() => 1]);
            $this->assign('search_url', $url);
            $this->assign('upload_url', $url);
            $this->assign('options', SystemFileClass::init()->getAdminFileOptions('', false));
            $this->assign('file_config', json_encode($this->getFileConfig()));
            $this->assign('default_type', SystemFile::FILE_TYPE_FILE);
            
            return $this->display();
        }
    }
    
    
    /**
     * 插入视频
     * @throws AppException
     */
    public function insert_video()
    {
        if ($this->isAjax()) {
            return $this->json($this->upload());
        } else {
            $this->assign('upload_url', url('', [$this->request->getVarAjax() => 1]));
            $this->assign('file_config', json_encode($this->getFileConfig()));
            $this->assign('default_type', SystemFile::FILE_TYPE_VIDEO);
            
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
            $this->assign('default_type', SystemFile::FILE_TYPE_IMAGE);
            
            return $this->display();
        }
    }
    
    
    /**
     * word图片转存
     */
    public function word_image()
    {
        return $this->insert_image();
    }
    
    
    /**
     * 编辑器运行JS
     */
    public function runtime()
    {
        $this->app->config->load(App::getBusyPath('app' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'config') . 'ueditor.php', 'ueditor');
        $uEditorConfig = $this->app->config->get('ueditor');
        
        foreach ($uEditorConfig as $key => $config) {
            // 工具栏解析
            $config['toolbars'] = [array_map('trim', explode(',', $config['toolbars']))];
            
            // 白名单规则解析
            if ($config['xssFilterRules']) {
                $whitList           = array_map('trim', explode(';', $config['whitList']));
                $config['whitList'] = [];
                foreach ($whitList as $tag => $rule) {
                    $rule                     = array_map('trim', explode(',', $rule));
                    $config['whitList'][$tag] = $rule;
                }
                $config['whitList'] = $config['whitList'] ? $config['whitList'] : new stdClass();
            } else {
                $config['whitList'] = new stdClass();
            }
            
            $uEditorConfig[$key] = $config;
        }
        
        $this->assign('editor_config', json_encode($uEditorConfig));
        $this->assign('file_config', json_encode($this->getFileConfig()));
        $this->assign('default_image', SystemFile::FILE_TYPE_IMAGE);
        $this->assign('default_video', SystemFile::FILE_TYPE_VIDEO);
        $this->assign('default_file', SystemFile::FILE_TYPE_FILE);
        
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
        } catch (AppException $e) {
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