<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\message;

use BusyPHP\Model;
use think\db\exception\DbException;
use think\route\Url;

/**
 * 后台消息模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/18 下午10:56 上午 AdminMessage.php $
 * @method AdminMessageInfo findInfo($data = null, $notFoundMessage = null)
 * @method AdminMessageInfo getInfo($data, $notFoundMessage = null)
 * @method AdminMessageInfo[] selectList()
 * @method AdminMessageInfo[] buildListWithField(array $values, $key = null, $field = null)
 */
class AdminMessage extends Model
{
    protected $dataNotFoundMessage = '消息不存在';
    
    protected $listNotFoundMessage = '暂无消息';
    
    protected $bindParseClass      = AdminMessageInfo::class;
    
    
    /**
     * 添加消息
     * @param int          $userId 用户ID
     * @param string       $content 消息内容
     * @param string|Url   $url 操作链接
     * @param string       $desc 消息备注
     * @param string|array $icon 图标或图片地址，图标支持传入：图标类名称 或 array(图标类名称, 图标颜色16进制字符)，图片必须是 / 开头或 http 开头
     * @return int
     * @throws DbException
     */
    public function add($userId, $content, $url = '', $desc = '', $icon = '') : int
    {
        $url               = (string) $url;
        $data              = AdminMessageField::init();
        $data->createTime  = time();
        $data->userId      = intval($userId);
        $data->content     = trim($content);
        $data->description = trim($desc);
        $data->url         = trim($url);
        $data->icon        = is_array($icon) ? json_encode($icon) : json_encode([$icon]);
        
        return (int) $this->addData($data);
    }
    
    
    /**
     * 标记消息为已读
     * @param int $id
     * @throws DbException
     */
    public function setRead(int $id)
    {
        $save           = AdminMessageField::init();
        $save->read     = true;
        $save->readTime = time();
        $this->whereEntity(AdminMessageField::id($id))->saveData($save);
    }
    
    
    /**
     * 清空消息
     * @param int $userId
     * @throws DbException
     */
    public function clearByUserId(int $userId)
    {
        $this->whereEntity(AdminMessageField::userId($userId))->delete();
    }
    
    
    /**
     * 全部设为已读
     * @param int $userId
     * @throws DbException
     */
    public function setAllReadByUserId(int $userId)
    {
        $save           = AdminMessageField::init();
        $save->read     = true;
        $save->readTime = time();
        $this->whereEntity(AdminMessageField::userId($userId), AdminMessageField::read(0))->saveData($save);
    }
}