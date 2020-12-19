<?php

namespace BusyPHP\app\admin\model\admin\message;

use BusyPHP\exception\SQLException;
use BusyPHP\Model;
use think\route\Url;

/**
 * 后台消息模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/18 下午10:56 上午 AdminMessage.php $
 */
class AdminMessage extends Model
{
    /**
     * 插入消息
     * @param int          $userId 用户ID
     * @param string       $content 消息内容
     * @param string|Url   $url 操作链接
     * @param string       $desc 消息备注
     * @param string|array $icon 图标或图片地址，图标支持传入：图标类名称 或 array(图标类名称, 图标颜色16进制字符)，图片必须是 / 开头或 http 开头
     * @return int
     * @throws SQLException
     */
    public function insertData($userId, $content, $url = '', $desc = '', $icon = '')
    {
        if ($url instanceof Url) {
            $url = $url->__toString();
        }
        
        if (!$id = $this->addData([
            'create_time' => time(),
            'user_id'     => intval($userId),
            'content'     => trim($content),
            'description' => trim($desc),
            'url'         => trim($url),
            'icon'        => is_array($icon) ? json_encode($icon) : json_encode([$icon])
        ])) {
            throw new SQLException('插入消息失败', $this);
        }
        
        return $id;
    }
    
    
    /**
     * 标记消息为已读
     * @param $id
     * @throws SQLException
     */
    public function setRead($id)
    {
        if (false === $this->where('id', intval($id))->saveData([
                'is_read'   => 1,
                'read_time' => time()
            ])) {
            throw new SQLException('标记消息已读失败', $this);
        }
    }
    
    
    /**
     * 清空消息
     * @param $userId
     * @throws SQLException
     */
    public function clearByUserId($userId)
    {
        if (false === $this->where('user_id', intval($userId))->deleteData()) {
            throw new SQLException('清空消息失败', $this);
        }
    }
    
    
    /**
     * 全部设为已读
     * @param $userId
     * @throws SQLException
     */
    public function setAllReadByUserId($userId)
    {
        if (false === $this->where('user_id', intval($userId))->where('is_read', 0)->saveData([
                'is_read'   => 1,
                'read_time' => time()
            ])) {
            throw new SQLException('全部标记已读失败', $this);
        }
    }
    
    
    public static function parseList($list)
    {
        return parent::parseList($list, function($list) {
            foreach ($list as $i => $r) {
                $r['is_read'] = $r['is_read'] > 0;
                
                $icons = json_decode($r['icon'], true);
                $color = '';
                if (count($icons) > 1) {
                    [$icon, $color] = $icons;
                } else {
                    [$icon] = $icons;
                }
                
                $r['icon_color'] = $color;
                if (false !== strpos($icon, '/')) {
                    $r['icon_is_class'] = false;
                    $r['icon']          = $icon;
                } else {
                    $r['icon_is_class'] = true;
                    $r['icon']          = $icon;
                }
                
                $list[$i] = $r;
            }
            
            return $list;
        });
    }
}