<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\message;

use BusyPHP\Model;
use BusyPHP\model\Entity;
use think\db\exception\DbException;
use think\route\Url;

/**
 * 后台消息模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/18 下午10:56 上午 AdminMessage.php $
 * @method AdminMessageInfo getInfo(int $id, string $notFoundMessage = null)
 * @method AdminMessageInfo|null findInfo(int $id = null, string $notFoundMessage = null)
 * @method AdminMessageInfo[] selectList()
 * @method AdminMessageInfo[] buildListWithField(array $values, string|Entity $key = null, string|Entity $field = null)
 * @method static AdminMessage getClass()
 */
class AdminMessage extends Model
{
    protected $dataNotFoundMessage = '消息不存在';
    
    protected $listNotFoundMessage = '暂无消息';
    
    protected $bindParseClass      = AdminMessageInfo::class;
    
    
    /**
     * @inheritDoc
     */
    final protected static function defineClass() : string
    {
        return self::class;
    }
    
    
    /**
     * 创建消息
     * @param int          $userId 用户ID
     * @param string       $content 消息内容
     * @param string|Url   $url 操作链接
     * @param string       $desc 消息备注
     * @param string|array $icon 图标或图片地址，图标支持传入：图标类名称 或 array(图标类名称, 图标颜色16进制字符)，图片必须是 / 开头或 http 开头
     * @return int
     * @throws DbException
     */
    public function createInfo(int $userId, string $content, $url = '', $desc = '', $icon = '') : int
    {
        $data = AdminMessageField::init();
        $data->setUserId($userId);
        $data->setContent($content);
        $data->setDescription($desc);
        $data->setUrl((string) $url);
        $data->setCreateTime(time());
        $data->setIcon((array) $icon);
        
        return (int) $this->validate($data, self::SCENE_CREATE)->addData();
    }
    
    
    /**
     * 标记消息为已读
     * @param int $id
     * @throws DbException
     */
    public function setRead(int $id)
    {
        $data = AdminMessageField::init();
        $data->setId($id);
        $data->setRead(true);
        $data->setReadTime(time());
        $this->saveData($data);
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
        $data = AdminMessageField::init();
        $data->setRead(true);
        $data->setReadTime(time());
        $this->whereEntity(AdminMessageField::userId($userId), AdminMessageField::read(0))->saveData($data);
    }
}