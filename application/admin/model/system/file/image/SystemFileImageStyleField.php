<?php

namespace BusyPHP\app\admin\model\system\file\image;

use BusyPHP\exception\VerifyException;
use BusyPHP\helper\RegexHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;

/**
 * SystemImageStyleField
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/15 11:33 AM SystemFileImageStyleField.php $
 * @method static Entity id($op = null, $value = null) 样式名
 * @method static Entity content($op = null, $value = null) 样式内容
 */
class SystemFileImageStyleField extends Field
{
    /**
     * 样式名
     * @var string
     */
    public $id;
    
    /**
     * 样式内容
     * @var string
     */
    public $content;
    
    
    /**
     * 设置ID
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = trim($id);
        if (!$this->id) {
            throw new VerifyException('样式名不能为空', 'id');
        }
        
        if (!RegexHelper::account($this->id)) {
            throw new VerifyException('样式名格式有误，只能包含英文、数字、下划线', 'id');
        }
        
        // 只能是英文开头
        $this->id = StringHelper::snake($this->id);
        if (!RegexHelper::english(substr($this->id, 0, 1))) {
            throw new VerifyException('样式名不能为数字或下划线开头', 'id');
        }
    }
    
    
    /**
     * 设置样式内容
     * @param array $content
     */
    public function setContent(array $content)
    {
        if (!$content) {
            throw new VerifyException('样式内容不能为空', 'content');
        }
        
        $this->content = $content;
    }
}