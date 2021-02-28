<?php

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\setting\FileSetting;

/**
 * 动态JS
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/2 下午10:58 上午 Js.php $
 */
class JsController extends InsideController
{
    public function initialize($checkLogin = false)
    {
        parent::initialize($checkLogin);
    }
    
    
    protected function display($template = '', $charset = '', $contentType = '', $content = '', $prefix = '')
    {
        if (!$template) {
            $template = $this->getTemplatePath() . ACTION_NAME . '.js';
        }
        
        return parent::display($template, 'utf-8', 'application/x-javascript');
    }
    
    
    public function index()
    {
        return $this->redirect(URL_APP);
    }
    
    
    /**
     * 附件上传组件JS
     */
    public function uploader()
    {
        $stamp     = time();
        $sid       = session_id();
        $fileSet   = FileSetting::init();
        $classList = SystemFileClass::init()->getListByCache();
        $fileClass = [];
        foreach ($classList as $key => $r) {
            $fileSet->setClassify($key);
            $fileClass[$key] = [
                'size'      => $fileSet->getAdminSize(),
                'suffix'    => implode(',', $fileSet->getAdminType()),
                'mime'      => implode(',', $fileSet->getMimeType()),
                'type'      => $r['type'],
                'name'      => $r['name'],
                'isThumb'   => $fileSet->isThumb(),
                'hasSource' => !$fileSet->isThumbDeleteSource(),
                'width'     => $fileSet->getThumbWidth(),
                'height'    => $fileSet->getThumbHeight(),
            ];
        }
        
        $this->assign('stamp', $stamp);
        $this->assign('sid', $sid);
        $this->assign('file_class', $fileClass);
        
        return $this->display();
    }
    
    
    /**
     * 省市区数据
     */
    public function area()
    {
        $callback = $this->iGet('callback', 'trim');
        $trees    = BusyPHP\Model\System\Region\SystemRegion::init()->getTrees();
        if ($callback) {
            $data = json_encode($trees, JSON_UNESCAPED_UNICODE);
            $data = "var {$callback} = {$data}";
            
            return $this->view->content($data)->contentType('application/javascript');
        }
        
        $this->request->setRequestIsAjax();
        
        return $this->success('', '', $trees);
    }
} 