<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel;

/**
 * Excel辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/7 19:18 Helper.php $
 */
class Helper
{
    /**
     * 生成A-ZZ字母
     * @return string[]
     */
    public static function letters() : array
    {
        static $list;
        
        if (!isset($list)) {
            $list = [];
            for ($i = 'A'; $i < 'ZZ'; $i++) {
                $list[] = $i;
            }
            
            $list[] = 'ZZ';
        }
        
        return $list;
    }
}