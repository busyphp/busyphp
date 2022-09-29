<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\file;

use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClassInfo;
use BusyPHP\helper\AppHelper;
use BusyPHP\helper\TransHelper;
use BusyPHP\model\Entity;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 附件模型信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
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
 * @property bool|int $fast
 * @property bool|int $pending
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
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function onParseAfter()
    {
        static $fileClassList;
        if (!isset($fileClassList)) {
            $fileClassList = SystemFileClass::init()->getList();
        }
        
        $this->typeName         = SystemFile::getTypes((string) $this->type);
        $this->classInfo        = $fileClassList[$this->classType] ?? null;
        $this->className        = $this->classInfo->name ?? '';
        $this->formatCreateTime = TransHelper::date($this->createTime);
        
        $sizes            = TransHelper::formatBytes($this->size, true);
        $this->sizeUnit   = $sizes['unit'];
        $this->sizeNum    = $sizes['number'];
        $this->formatSize = "$this->sizeNum $this->sizeUnit";
        $this->filename   = pathinfo($this->url, PATHINFO_BASENAME);
        $this->clientName = AppHelper::getName($this->client);
        $this->pending    = $this->pending > 0;
        $this->fast       = $this->fast > 0;
    }
}