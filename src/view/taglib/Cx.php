<?php
declare(strict_types = 1);

namespace BusyPHP\view\taglib;

/**
 * 基础标签解析器
 * 本解析器继承自TP默认的标签库，主要为了解决一些默认库遗留的BUG，也可以写自己标签
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/3 下午9:24 上午 Cx.php $
 */
class Cx extends \think\template\taglib\Cx
{
    /**
     * load 标签解析 {load file="/static/js/base.js" /}
     * 格式：{load file="/static/css/base.css" /}
     * @access public
     * @param array  $tag 标签属性
     * @param string $content 标签内容
     * @return string
     */
    public function tagLoad(array $tag, string $content) : string
    {
        $file     = isset($tag['file']) ? $tag['file'] : $tag['href'];
        $parseStr = '';
        $endStr   = '';
        
        // 判断是否存在加载条件 允许使用函数判断(默认为isset)
        if (isset($tag['value'])) {
            $name     = $tag['value'];
            $name     = $this->autoBuildVar($name);
            $name     = 'isset(' . $name . ')';
            $parseStr .= '<?php if(' . $name . '): ?>';
            $endStr   = '<?php endif; ?>';
        }
        
        // 文件方式导入
        $array = explode(',', $file);
        
        
        foreach ($array as $val) {
            $type = strtolower(substr(strrchr($val, '.'), 1));
            if (false !== $index = strpos($type, '?')) {
                $type = substr($type, 0, $index);
            }
            
            switch ($type) {
                case 'js':
                    $parseStr .= '<script type="text/javascript" src="' . $val . '"></script>';
                break;
                case 'css':
                    $parseStr .= '<link rel="stylesheet" type="text/css" href="' . $val . '" />';
                break;
                case 'php':
                    $parseStr .= '<?php include "' . $val . '"; ?>';
                break;
            }
        }
        
        return $parseStr . $endStr;
    }
}