<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\task;

use think\Response;

/**
 * 系统任务结果下载接口类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/17 12:49 SystemTaskDownloadResultInterface.php $
 */
interface DownloadSystemTaskResultInterface
{
    /**
     * @param SystemTaskField $info 任务数据
     * @param string          $filename 下载文件名
     * @param string          $mimetype 下载文件mimetype
     * @return Response
     */
    public function downloadSystemTaskResult(SystemTaskField $info, string $filename, string $mimetype) : Response;
}