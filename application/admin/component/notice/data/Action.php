<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\notice\data;

use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\Field;

/**
 * 操作项结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/6/26 22:21 Action.php $
 * @method $this setName(string $name) 设置操作项名称
 */
#[ToArrayFormat(type: ToArrayFormat::TYPE_SNAKE)]
class Action extends Field
{
    /**
     * 操作项名称
     * @var string
     */
    public $name = '';
    
    /**
     * 操作方法
     * @var Operate|null
     */
    public $operate = null;
    
    
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        
        if (!isset($this->operate)) {
            $this->setOperate(Operate::build());
        }
    }
    
    
    /**
     * 设置操作方法
     * @param mixed $operate
     * @return Action
     */
    public function setOperate(mixed $operate) : static
    {
        if ($operate instanceof Operate) {
            $this->operate = $operate;
        } elseif (is_array($operate)) {
            $this->operate = Operate::init($operate);
        }
        
        return $this;
    }
    
    
    /**
     * 构建
     * @param string       $name 操作项名称
     * @param Operate|null $operate 操作方式
     * @return static
     */
    public static function build(string $name, Operate $operate = null) : static
    {
        $obj = static::init();
        $obj->setName($name);
        $obj->setOperate($operate ?: Operate::build());
        
        return $obj;
    }
}