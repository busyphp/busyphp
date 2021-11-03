<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\logs;

use BusyPHP\App;
use BusyPHP\contract\structs\items\AppListItem;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\TransHelper;

/**
 * 操作记录模型信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午5:04 SystemLogsInfo.php $
 * @method static string formatCreateTime();
 * @method static string typeName();
 * @method static string clientName();
 */
class SystemLogsInfo extends SystemLogsField
{
    /**
     * 格式化的时间
     * @var string
     */
    public $formatCreateTime;
    
    /**
     * 操作类型名称
     * @var string
     */
    public $typeName;
    
    /**
     * 客户端名称
     * @var string
     */
    public $clientName;
    
    /**
     * @var AppListItem[]
     */
    protected static $_appList;
    
    
    public function onParseAfter()
    {
        if (!is_array(static::$_appList)) {
            static::$_appList = ArrayHelper::listByKey(App::init()->getList(), AppListItem::dir());
        }
        
        $this->formatCreateTime = TransHelper::date($this->createTime);
        $this->typeName         = SystemLogs::getTypes($this->type);
        $this->clientName       = $this->client === SystemLogs::CLI_CLIENT_KEY ? SystemLogs::CLI_CLIENT_NAME : ((static::$_appList[$this->client]->name ?? '') ?: $this->client);
        $this->params           = json_decode($this->params, true) ?: [];
        $this->headers          = json_decode($this->headers, true) ?: [];
    }
}