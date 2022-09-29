<?php
declare(strict_types = 1);

namespace BusyPHP\image\concern;

use BusyPHP\helper\CacheHelper;
use BusyPHP\image\parameter\UrlParameter;
use think\facade\Request;
use think\Response;
use Throwable;

/**
 * 图片响应相关类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/14 2:43 PM ResponseConcern.php $
 */
trait ResponseConcern
{
    /**
     * 处理并响应
     * @param UrlParameter $urlParameter
     * @return Response
     * @throws Throwable
     */
    public function response(UrlParameter $urlParameter) : Response
    {
        $append = '';
        if ($style = $urlParameter->getStyle()) {
            $append = serialize($this->getStyleByCache($style)->getUrlParameter());
        }
        
        $key  = md5(serialize($urlParameter) . $append); // TODO 读取源文件的最后修改时间并追加到Key中
        $dir  = 'image';
        $data = CacheHelper::get($dir, $key);
        if (!$data) {
            CacheHelper::set($dir, $key, $data = $this->process($urlParameter)->getData(), $urlParameter->getLifetime());
        }
        
        $mimetype = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $data);
        $code     = 200;
        $header   = ['Content-Length' => strlen($data)];
        
        // 下载
        if ($urlParameter->isDownload()) {
            $filename = rawurldecode($urlParameter->getFilename()) . '.' . $urlParameter->getFormat();
            
            $header['Content-Disposition'] = "attachment; filename=\"$filename\"";
        } else {
            $etag = sprintf('"%s"', md5($data));
            if (str_replace('W/', '', Request::header('if-none-match')) == $etag) {
                $data                     = null;
                $code                     = 304;
                $header['Content-Length'] = 0;
            }
            
            $header['Cache-Control'] = "max-age={$urlParameter->getLifetime()}, public";
            $header['Etag']          = $etag;
        }
        
        return Response::create($data)->code($code)->contentType($mimetype)->header($header);
    }
}