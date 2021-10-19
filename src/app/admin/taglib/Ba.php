<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\taglib;

use BusyPHP\App;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use think\template\TagLib;

/**
 * BusyPHP后端模板标签库
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/2 下午6:04 下午 Ba.php $
 */
class Ba extends TagLib
{
    /**
     * 标签配置
     * @var array
     */
    protected $tags = [
        'access' => ['attr' => 'value', 'must' => 'value']
    ];
    
    
    /**
     * 权限检测标签
     * <ba:access value="a/b">有斜杠代表全路径</ba:access>
     * <ba:access value="b">无斜杠则代表传入的是执行方法，会自动识别当前控制器</ba:access>
     * <ba:access value="$path">变量方式</ba:access>
     * <ba:access value=":path">函数方式</ba:access>
     * @param array  $tag
     * @param string $content
     * @return string
     */
    protected function tagAccess(array $tag, string $content) : string
    {
        $value = (string) ($tag['value'] ?? '');
        $list  = explode(',', $value);
        $path  = [];
        foreach ($list as $item) {
            // 解析变量
            $flag = substr($item, 0, 1);
            if ($flag === '$' || $flag === ':') {
                $item = $this->autoBuildVar($item);
            } else {
                $values = explode('/', $item) ?: [];
                // 需要获取控制器补全
                if (count($values) == 1) {
                    $controller = App::init()->request->controller();
                    $item       = "'{$controller}/{$item}'";
                } else {
                    $item = "'{$item}'";
                }
            }
            $path[] = $item;
        }
        $path = implode(',', $path);
        
        $groupClass = AdminGroup::class;
        
        return <<<HTML
<?php if ({$groupClass}::checkPermission(\$system['user'] ?? null, {$path})): ?>{$content}<?php endif; ?>
HTML;
    }
}