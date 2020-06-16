<?php

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\file\SystemFileUpload;
use BusyPHP\exception\AppException;
use think\helper\Str;

/**
 * 后台公共，不验证登录
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:36 下午 Action.php $
 */
class Action extends InsideController
{
    public function initialize($checkLogin = false)
    {
        parent::initialize($checkLogin);
    }
    
    
    /**
     * 获取随机数
     * 默认6位随机参数
     */
    public function rand()
    {
        $line = $this->iRequest('line', 'intval');
        $line = $line > 0 ? $line : 6;
        
        return $this->success('', '', Str::random($line));
    }
    
    
    /**
     * 附件上传
     */
    public function upload()
    {
        set_time_limit(0);
        $this->request->setRequestIsAjax();
        $this->isLogin();
        
        // 导入上传类
        try {
            $upload = new SystemFileUpload();
            $upload->setIsAdmin(true);
            $upload->setUserId($this->adminUserId);
            $upload->setMark($this->iPost('mark_type', 'trim'), $this->iPost('mark_value', 'trim'));
            $upload->setType($upload::TYPE_CHUNK);
            $result = $upload->upload($_FILES['upload']);
            
            // 分片上传成功
            if ($result === true) {
                return $this->success('PART SUCCESS', '', array());
            }
            
            $return = array(
                'file_url'  => $result->url,
                'file_id'   => $result->id,
                'name'      => $result->name,
                'filename'  => $result->filename,
                'folder'    => $result->folderPath,
                'extension' => $result->extension,
                'has_thumb' => false,
            );
            if ($result->thumb) {
                $return['has_thumb'] = true;
                $return['thumb']     = array(
                    'file_url'  => $result->thumb->url,
                    'file_id'   => $result->thumb->id,
                    'name'      => $result->thumb->name,
                    'filename'  => $result->thumb->filename,
                    'folder'    => $result->thumb->folderPath,
                    'extension' => $result->extension,
                );
            }
            
            return $this->success('上传成功', '', $return);
        } catch (AppException $e) {
            return $this->error($e->getMessage());
        }
    }
}