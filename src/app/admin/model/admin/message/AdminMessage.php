<?php

namespace BusyPHP\app\admin\model\admin\message;

use BusyPHP\Model;
use think\db\exception\DbException;
use think\route\Url;

/**
 * 后台消息模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/18 下午10:56 上午 AdminMessage.php $
 * @method AdminMessageInfo findInfo($data = null, $notFoundMessage = null)
 * @method AdminMessageInfo getInfo($data, $notFoundMessage = null)
 * @method AdminMessageInfo[] selectList()
 */
class AdminMessage extends Model
{
    protected $dataNotFoundMessage = '消息不存在';
    
    protected $listNotFoundMessage = '暂无消息';
    
    protected $bindParseClass      = AdminMessageInfo::class;
    
    
    /**
     * 插入消息
     * @param int          $userId 用户ID
     * @param string       $content 消息内容
     * @param string|Url   $url 操作链接
     * @param string       $desc 消息备注
     * @param string|array $icon 图标或图片地址，图标支持传入：图标类名称 或 array(图标类名称, 图标颜色16进制字符)，图片必须是 / 开头或 http 开头
     * @return int
     * @throws DbException
     */
    public function insertData($userId, $content, $url = '', $desc = '', $icon = '')
    {
        if ($url instanceof Url) {
            $url = $url->__toString();
        }
        
        $data              = AdminMessageField::init();
        $data->createTime  = time();
        $data->userId      = intval($userId);
        $data->content     = trim($content);
        $data->description = trim($desc);
        $data->url         = trim($url);
        $data->icon        = is_array($icon) ? json_encode($icon) : json_encode([$icon]);
        
        return $this->addData($data);
    }
    
    
    /**
     * 标记消息为已读
     * @param $id
     * @throws DbException
     */
    public function setRead($id)
    {
        $save           = AdminMessageField::init();
        $save->isRead   = true;
        $save->readTime = time();
        $this->whereEntity(AdminMessageField::id(intval($id)))->saveData($save);
    }
    
    
    /**
     * 清空消息
     * @param $userId
     * @throws DbException
     */
    public function clearByUserId($userId)
    {
        $this->whereEntity(AdminMessageField::userId(intval($userId)))->delete();
    }
    
    
    /**
     * 全部设为已读
     * @param $userId
     * @throws DbException
     */
    public function setAllReadByUserId($userId)
    {
        $save           = AdminMessageField::init();
        $save->isRead   = true;
        $save->readTime = time();
        $this->whereEntity(AdminMessageField::userId(intval($userId)), AdminMessageField::isRead(0))->saveData($save);
    }
}