<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\message;

use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use think\db\exception\DbException;
use think\route\Url;

/**
 * 后台消息模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/18 下午10:56 上午 AdminMessage.php $
 * @method AdminMessageField getInfo(int $id, string $notFoundMessage = null)
 * @method AdminMessageField|null findInfo(int $id = null)
 * @method AdminMessageField[] selectList()
 * @method AdminMessageField[] indexList(string|Entity $key = '')
 * @method AdminMessageField[] indexListIn(array $range, string|Entity $key = '', string|Entity $field = '')
 */
class AdminMessage extends Model implements ContainerInterface
{
    protected string $dataNotFoundMessage = '消息不存在';
    
    protected string $listNotFoundMessage = '暂无消息';
    
    protected string $fieldClass = AdminMessageField::class;
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
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
     * @param array        $attrs 自定义标签属性
     * @return int
     * @throws DbException
     */
    public function create(int $userId, string $content, $url = '', string $desc = '', $icon = '', array $attrs = []) : int
    {
        $data = AdminMessageField::init();
        $data->setUserId($userId);
        $data->setContent($content);
        $data->setDescription($desc);
        $data->setUrl((string) $url);
        $data->setCreateTime(time());
        $data->setIcon((array) $icon);
        $data->setAttrs($attrs);
        
        return (int) $this->validate($data, self::SCENE_CREATE)->insert();
    }
    
    
    /**
     * 标记消息为已读
     * @param int $userId
     * @param int $id
     * @throws DbException
     */
    public function setRead(int $userId, int $id)
    {
        $data = AdminMessageField::init();
        $data->setRead(true);
        $data->setReadTime(time());
        
        $this->where(AdminMessageField::userId($userId))->where(AdminMessageField::id($id))->update($data);
    }
    
    
    /**
     * 清空消息
     * @param int $userId
     * @throws DbException
     */
    public function clear(int $userId)
    {
        $this->where(AdminMessageField::userId($userId))->delete();
    }
    
    
    /**
     * 全部设为已读
     * @param int $userId
     * @throws DbException
     */
    public function setAllRead(int $userId)
    {
        $data = AdminMessageField::init();
        $data->setRead(true);
        $data->setReadTime(time());
        $this->where(AdminMessageField::userId($userId))->where(AdminMessageField::read(0))->update($data);
    }
}