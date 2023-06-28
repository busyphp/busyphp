<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\component\notice\todo;

use BusyPHP\app\admin\component\notice\data\AdminOperate;
use BusyPHP\app\admin\component\notice\Todo;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\Field;

/**
 * 待办任务消息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/17 下午9:17 下午 TodoNode.php $
 * @method $this setSort(int $sort) 设置排序值
 * @method $this setId(string $id) 设置代办项ID
 * @method $this setTitle(string $title) 设置代办项名称
 * @method $this setDesc(string $desc) 设置代办项描述
 * @method $this setTotal(int $total) 设置代办数统计
 * @method $this setLevel(int $level) 设置代办级别
 * @method $this setOperate(AdminOperate $operate) 设置代办操作
 */
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
class TodoNode extends Field
{
    /**
     * 排序下标
     * @var int
     */
    public $sort = -1;
    
    /**
     * 待办ID
     * @var string
     */
    public $id = '';
    
    /**
     * 待办标题
     * @var string
     */
    public $title = '';
    
    /**
     * 待办描述
     * @var string
     */
    public $desc = '';
    
    /**
     * 待办数
     * @var int
     */
    public $total = 0;
    
    /**
     * 待办级别
     * @var int
     */
    public $level = Todo::LEVEL_DEFAULT;
    
    /**
     * 操作方式
     * @var AdminOperate|null
     */
    public $operate = null;
    
    /**
     * 待办级别名称
     * @var string
     */
    public $levelName = '';
    
    /**
     * 待办级别样式
     * @var string
     */
    public $levelStyle = '';
    
    
    protected function onParseAfter()
    {
        $level            = Todo::getLevelMap($this->level);
        $this->levelStyle = $level['style'] ?? '';
        $this->levelName  = $level['name'] ?? '';
        $this->operate    = $this->operate ?: AdminOperate::build();
    }
}