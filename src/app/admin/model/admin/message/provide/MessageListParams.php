<?php

namespace BusyPHP\app\admin\model\admin\message\provide;

/**
 * 消息参数模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/17 下午9:17 下午 MessageParams.php $
 */
class MessageListParams extends MessageParams
{
    /** @var int */
    private $page = 0;
    
    
    /**
     * 设置分页
     * @param int $page
     */
    public function setPage(int $page) : void
    {
        $this->page = $page;
    }
    
    
    /**
     * 获取分页
     * @return int
     */
    public function getPage() : int
    {
        return $this->page;
    }
}