<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\admin\message;

use BusyPHP\app\admin\component\notice\data\Action;
use BusyPHP\app\admin\component\notice\data\Attr;
use BusyPHP\app\admin\component\notice\data\Operate;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\Field;

/**
 * 消息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/6/26 22:28 AdminMessageContent.php $
 * @method $this setTitle(string $title) 设置消息标题
 * @method $this setDesc(string $desc) 设置消息描述
 * @method $this setImage(string $image) 设置消息图片
 * @method $this setStyle(string $style) 设置消息样式
 * @method $this setExtra(array $extra) 设置额外参数
 * @method $this setNumberPrefix(string $numberPrefix) 设置数字前缀，针对数字样式
 */
#[ToArrayFormat(type: ToArrayFormat::TYPE_SNAKE)]
class AdminMessageContent extends Field
{
    /** @var string 默认样式 */
    public const STYLE_DEFAULT = '';
    
    /** @var string 左图片右内容样式 */
    public const STYLE_LEFT_IMAGE = 'left_image';
    
    /** @var string 封面图样式 */
    public const STYLE_COVER_IMAGE = 'cover_image';
    
    /** @var string 数字样式 */
    public const STYLE_NUMBER = 'number';
    
    /**
     * 消息标题
     * @var string
     */
    public $title = '';
    
    /**
     * 消息描述
     * @var string
     */
    public $desc = '';
    
    /**
     * 消息图片
     * @var array
     */
    public $image = '';
    
    /**
     * 数字前缀
     * @var string
     */
    public $numberPrefix = '';
    
    /**
     * 消息样式
     * @var string
     */
    public $style = self::STYLE_DEFAULT;
    
    /**
     * 消息属性集合
     * @var Attr[]
     */
    public $attrs = [];
    
    /**
     * 消息操作项集合
     * @var Action[]
     */
    public $actions = [];
    
    /**
     * 额外参数
     * @var array
     */
    public $extra = [];
    
    
    protected function onParseAfter()
    {
        $this->extra = $this->extra ?: [];
    }
    
    
    /**
     * 解析后台操作
     * @param AdminMessageField $data
     * @return $this
     */
    public function parseAdminOperate(AdminMessageField $data) : static
    {
        foreach ($this->actions as $action) {
            $action->operate->parseAdmin($data);
        }
        
        return $this;
    }
    
    
    /**
     * 添加属性
     * @param string $name 属性名称
     * @param string $value 属性值
     * @param string $color 属性颜色
     * @return $this
     */
    public function addAttr(string $name, string $value, string $color = '') : static
    {
        $attr        = Attr::init();
        $attr->name  = $name;
        $attr->value = $value;
        $attr->color = $color;
        
        $this->attrs[] = $attr;
        
        return $this;
    }
    
    
    /**
     * 添加操作项
     * @param string $name 操作项名称
     * @param int    $operateType 操作类型
     * @param string $operateValue 操作值
     * @return $this
     */
    public function addAction(string $name, int $operateType = 0, string $operateValue = '') : static
    {
        $action          = Action::init();
        $action->name    = $name;
        $action->operate = Operate::build($operateType, $operateValue);
        
        $this->actions[] = $action;
        
        return $this;
    }
    
    
    /**
     * 设置操作项
     * @param Action[]|array $actions
     * @return $this
     */
    public function setActions(array $actions) : static
    {
        $this->actions = $actions;
        
        foreach ($this->actions as &$action) {
            if (!$action instanceof Action) {
                $action = Action::init($action);
            }
        }
        
        return $this;
    }
    
    
    /**
     * 设置属性
     * @param Attr[]|array $attrs
     * @return $this
     */
    public function setAttrs(array $attrs) : static
    {
        $this->attrs = $attrs;
        
        foreach ($this->attrs as &$attr) {
            if (!$attr instanceof Attr) {
                $attr = Attr::init($attr);
            }
        }
        
        return $this;
    }
    
    
    /**
     * 默认样式，上标题，下描述
     * @param string $title 标题
     * @param string $desc 描述
     * @return static
     */
    public static function default(string $title, string $desc = '') : static
    {
        $obj = static::init();
        $obj->setTitle($title);
        $obj->setDesc($desc);
        $obj->setStyle(self::STYLE_DEFAULT);
        
        return $obj;
    }
    
    
    /**
     * 左图片样式，右上标题，下描述
     * @param string $imageUrl 图片URL
     * @param string $title 标题
     * @param string $desc 描述
     * @return static
     */
    public static function leftImage(string $imageUrl, string $title, string $desc = '') : static
    {
        $obj = static::init();
        $obj->setTitle($title);
        $obj->setDesc($desc);
        $obj->setImage($imageUrl);
        $obj->setStyle(self::STYLE_LEFT_IMAGE);
        
        return $obj;
    }
    
    
    /**
     * 封面图片样式，上封面图，中标题，下描述
     * @param string $imageUrl 封面图URL
     * @param string $title 标题
     * @param string $desc 描述
     * @return static
     */
    public static function coverImage(string $imageUrl, string $title, string $desc = '') : static
    {
        $obj = static::init();
        $obj->setTitle($title);
        $obj->setDesc($desc);
        $obj->setImage($imageUrl);
        $obj->setStyle(self::STYLE_COVER_IMAGE);
        
        return $obj;
    }
    
    
    /**
     * 数字样式，上描述，下数字，如果设置了单位则数字左侧为单位
     * @param string $number 数字
     * @param string $desc 描述
     * @param string $prefix 前缀，如人民币单位
     * @param string $iconUrl 图标，如果设置了则在数字描述上方呈现
     * @return static
     */
    public static function number(string $number, string $desc = '', string $prefix = '', string $iconUrl = '') : static
    {
        $obj = static::init();
        $obj->setTitle($number);
        $obj->setDesc($desc);
        $obj->setImage($iconUrl);
        $obj->setNumberPrefix($prefix);
        $obj->setStyle(self::STYLE_NUMBER);
        
        return $obj;
    }
}