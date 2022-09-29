<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\controller\common\message\parameter;

/**
 * 消息参数模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/17 下午9:17 下午 MessageNoticeUpdateParams.php $
 */
class MessageUpdateParameter extends MessageParameter
{
    /** @var int */
    private $id = 0;
    
    
    /**
     * 设置信息ID
     * @param int $id
     */
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    
    
    /**
     * 获取信息ID
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }
}