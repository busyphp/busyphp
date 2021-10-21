<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\file;

use BusyPHP\App;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClassInfo;
use BusyPHP\contract\structs\items\AppListItem;
use BusyPHP\helper\FileHelper;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\TransHelper;
use BusyPHP\model\Entity;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 附件模型信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午3:26 SystemFileInfo.php $
 * @method static Entity typeName($op = null, $value = null) 附件类型名称;
 * @method static Entity clientName($op = null, $value = null) 客户端名称;
 * @method static Entity formatCreateTime($op = null, $value = null) 格式化的创建时间;
 * @method static Entity sizeUnit($op = null, $value = null) 附件大小单位;
 * @method static Entity sizeNum($op = null, $value = null) 附件大小;
 * @method static Entity formatSize($op = null, $value = null) 格式化的附件大小;
 * @method static Entity filename($op = null, $value = null) 附件名称;
 * @method static Entity classInfo($op = null, $value = null) 分类信息;
 * @method static Entity className($op = null, $value = null) 分类名称;
 */
class SystemFileInfo extends SystemFileField
{
    /**
     * 附件类型名称
     * @var string
     */
    public $typeName;
    
    /**
     * 格式化的创建时间
     * @var string
     */
    public $formatCreateTime;
    
    /**
     * 附件大小单位
     * @var string
     */
    public $sizeUnit;
    
    /**
     * 附件大小
     * @var int
     */
    public $sizeNum;
    
    /**
     * 格式化的附件大小
     * @var string
     */
    public $formatSize;
    
    /**
     * 附件名称
     * @var string
     */
    public $filename;
    
    /**
     * 客户端名称
     * @var string
     */
    public $clientName;
    
    /**
     * 分类信息
     * @var SystemFileClassInfo
     */
    public $classInfo;
    
    /**
     * 文件分类名称
     * @var string
     */
    public $className;
    
    /**
     * @var AppListItem[]
     */
    protected static $_appList;
    
    /**
     * @var SystemFileClassInfo[]
     */
    protected static $_fileClassList;
    
    
    /**
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function onParseAfter()
    {
        if (!is_array(static::$_appList)) {
            static::$_appList = ArrayHelper::listByKey(App::init()->getList(), AppListItem::dir());
        }
        
        if (!is_array(static::$_fileClassList)) {
            static::$_fileClassList = SystemFileClass::init()->getList();
        }
        
        $this->typeName         = SystemFile::getTypes((string) $this->type);
        $this->classInfo        = static::$_fileClassList[$this->classType] ?? null;
        $this->className        = $this->classInfo->name ?? '';
        $this->formatCreateTime = TransHelper::date($this->createTime);
        
        $sizes            = TransHelper::formatBytes($this->size, true);
        $this->sizeUnit   = $sizes['unit'];
        $this->sizeNum    = $sizes['number'];
        $this->formatSize = "{$this->sizeNum} {$this->sizeUnit}";
        $this->filename   = FileHelper::pathInfo($this->url, PATHINFO_BASENAME);
        $this->clientName = (static::$_appList[$this->client]->name ?? '') ?: $this->client;
    }
}