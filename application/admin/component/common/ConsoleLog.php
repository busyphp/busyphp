<?php
namespace BusyPHP\app\admin\component\common;

use BusyPHP\app\admin\controller\AdminHandle;
use BusyPHP\app\admin\model\system\task\SystemTaskField;
use BusyPHP\helper\CacheHelper;
use BusyPHP\helper\TransHelper;
use think\Response;
use think\route\Url;

/**
 * 控制台日志
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/10 21:11 ConsoleLog.php $
 * @method static void question(string $id, string $message, float|int[]|false $percentage = false, int $backLine = 0) 疑问级别日志
 * @method static void highlight(string $id, string $message, float|int[]|false $percentage = false, int $backLine = 0) 强调级别日志
 * @method static void comment(string $id, string $message, float|int[]|false $percentage = false, int $backLine = 0) 解释级别日志
 * @method static void error(string $id, string $message, float|int[]|false $percentage = false, int $backLine = 0) 错误级别日志
 * @method static void warning(string $id, string $message, float|int[]|false $percentage = false, int $backLine = 0) 警告级别日志
 * @method static void info(string $id, string $message, float|int[]|false $percentage = false, int $backLine = 0) 消息级别日志
 * @method static void record(string $id, string $message, float|int[]|false $percentage = false, int $backLine = 0) 普通日志
 * @method static void create(string $id, string $message, string $level = '') 创建日志
 * @method static void finish(string $id, string $message, bool $success = true) 结束日志
 */
class ConsoleLog
{
    // +----------------------------------------------------
    // + 日志级别
    // +----------------------------------------------------
    /** @var string 疑问级别日志 */
    public const LEVEL_QUESTION = 'question';
    
    /** @var string 强调级别日志 */
    public const LEVEL_HIGHLIGHT = 'highlight';
    
    /** @var string 解释级别日志 */
    public const LEVEL_COMMENT = 'comment';
    
    /** @var string 错误级别日志 */
    public const LEVEL_ERROR = 'error';
    
    /** @var string 警告级别日志 */
    public const LEVEL_WARNING = 'warning';
    
    /** @var string 消息级别日志 */
    public const LEVEL_INFO = 'info';
    
    /** @var string 普通日志 */
    public const LEVEL_RECORD = 'record';
    
    // +----------------------------------------------------
    // + 日志状态
    // +----------------------------------------------------
    /** @var int 日志过程 */
    public const TYPE_DEFAULT = 0;
    
    /** @var int 新创建 */
    public const TYPE_CREATE = 1;
    
    /** @var int 已结束-成功 */
    public const TYPE_FINISH_SUCCESS = 2;
    
    /** @var int 已结束-失败 */
    public const TYPE_FINISH_ERROR = 3;
    
    
    public static function __callStatic(string $name, array $arguments)
    {
        $id         = (string) $arguments[0];
        $message    = (string) $arguments[1];
        $percentage = $arguments[2] ?? false;
        $backLine   = $arguments[3] ?? 0;
        $level      = strtolower($name);
        $type       = self::TYPE_DEFAULT;
        if ($level === 'create') {
            $percentage = false;
            $level      = $arguments[2] ?? '';
            $type       = self::TYPE_CREATE;
        } elseif ($level === 'finish') {
            if ($percentage) {
                $level = self::LEVEL_INFO;
                $type  = self::TYPE_FINISH_SUCCESS;
            } else {
                $level = self::LEVEL_HIGHLIGHT;
                $type  = self::TYPE_FINISH_ERROR;
            }
            $percentage = false;
        }
        
        self::write($id, $message, $percentage, $backLine, $level ?: self::LEVEL_RECORD, $type);
    }
    
    
    /**
     * 记录日志
     * @param string                   $id 日志ID
     * @param string                   $message 进度描述
     * @param float<0,100>|int[]|false $percentage 处理进度数值，范围 0-100，false则为普通日志，数组代表总数和当前值，如：[100,20]
     * @param int                      $backLine 回退行数，上n行会被删除
     * @param string                   $level 日志级别
     * @param int                      $type 日志状态
     * @return void
     */
    public static function write(string $id, string $message, float|false|array $percentage = false, int $backLine = 0, string $level = self::LEVEL_RECORD, int $type = self::TYPE_DEFAULT) : void
    {
        $log = CacheHelper::get(self::class, $id);
        if (!$log || $type === self::TYPE_CREATE) {
            $log = [
                'percentage' => 0.0,
                'progress'   => false,
                'list'       => []
            ];
        }
        
        // 计算进度
        if (is_array($percentage)) {
            $total      = $percentage[0] ?? 0;
            $current    = $percentage[1] ?? 0;
            $percentage = $current / $total * 100;
        }
        
        // 展示进度条
        if (is_numeric($percentage) && !$log['progress']) {
            $log['progress'] = true;
        }
        
        // 保持进度连贯性
        if ($percentage !== false && $percentage > 0) {
            $log['percentage'] = $percentage;
        }
        
        // 回退行
        $backIds = [];
        if ($log['list'] && $backLine !== 0) {
            $backLine = abs($backLine);
            while ($backLine--) {
                $pop = array_pop($log['list']);
                if ($pop) {
                    $backIds[] = $pop['id'];
                }
            }
        }
        
        $log['message'] = $message;
        $log['type']    = $type;
        $log['id']      = md5($id . '_' . md5(uniqid('console_log')));
        $log['level']   = $level;
        $log['max']     = 50;
        $log['list'][]  = [
            'id'         => $log['id'],
            'time'       => TransHelper::date(time()),
            'percentage' => $percentage,
            'message'    => $message,
            'level'      => $level,
            'type'       => $type,
            'back'       => $backIds
        ];
        
        if (count($log['list']) > $log['max']) {
            $log['list'] = array_slice($log['list'], -$log['max']);
        }
        
        CacheHelper::set(self::class, $id, $log, null);
    }
    
    
    /**
     * 获取日志
     * @param string $id 日志ID
     * @return array{id: int, message: string, percentage: float, progress: bool, level: string, list: array<array{time: string, percentage: false|float, message: string, level: string}>}|null
     */
    public static function get(string $id) : ?array
    {
        $info = CacheHelper::get(self::class, $id);
        if (!$info) {
            return null;
        }
        
        return $info;
    }
    
    
    /**
     * 打开控制台日志对话框
     * @param string|SystemTaskField $logId 日志ID或任务信息对象
     * @param string                 $message 提示消息
     * @param Url|string             $url 任务完成操作的URL
     * @param string                 $name 任务完成操作的名称
     * @param bool                   $blank 是否新建窗口打开操作URL
     * @return Response
     */
    public static function dialog(string|SystemTaskField $logId, string $message = '', Url|string $url = '', string $name = '', bool $blank = false) : Response
    {
        if ($logId instanceof SystemTaskField) {
            $url   = $logId->operateUrl;
            $name  = $logId->operateName;
            $blank = $logId->operateBlank;
            $logId = $logId->logId;
        }
        
        return AdminHandle::instance()->jsonSuccess($message, [
            'console_log' => [
                'id'            => $logId,
                'operate_url'   => (string) $url,
                'operate_name'  => $name,
                'operate_blank' => $blank,
            ]
        ]);
    }
}