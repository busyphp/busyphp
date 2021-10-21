<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\setting;

use BusyPHP\App;
use BusyPHP\helper\FilterHelper;
use BusyPHP\helper\TransHelper;
use BusyPHP\model\Setting;

/**
 * 图形验证码配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/19 下午下午3:44 CaptchaSetting.php $
 */
class CaptchaSetting extends Setting
{
    private $client;
    
    
    /**
     * 获取数据解析器
     * @param mixed $data
     * @return mixed
     */
    protected function parseGet($data)
    {
        return $data;
    }
    
    
    /**
     * 设置数据解析器
     * @param mixed $data
     * @return mixed
     */
    protected function parseSet($data)
    {
        $data    = FilterHelper::trim($data);
        $clients = [];
        
        foreach ($data['clients'] ?? [] as $client => $vo) {
            $vo['curve']      = TransHelper::dataToBool($vo['curve'] ?? false);
            $vo['noise']      = TransHelper::dataToBool($vo['noise'] ?? false);
            $vo['bg_image']   = TransHelper::dataToBool($vo['bg_image'] ?? false);
            $clients[$client] = $vo;
        }
        
        $data['clients'] = $clients;
        
        return $data;
    }
    
    
    /**
     * 设置客户端
     * @param string $client
     * @return CaptchaSetting
     */
    public function setClient($client) : self
    {
        $this->client = $client;
        
        return $this;
    }
    
    
    /**
     * 获取值
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    protected function value($key, $default = null)
    {
        $clients = $this->get('clients', []);
        
        return $clients[$this->client ?: $this->app->getDirName()][$key] ?? $default;
    }
    
    
    /**
     * 是否绘制曲线
     * @return bool
     */
    public function isCurve() : bool
    {
        return (bool) $this->value('curve', false);
    }
    
    
    /**
     * 是否绘制杂点
     * @return bool
     */
    public function isNoise() : bool
    {
        return (bool) $this->value('noise', false);
    }
    
    
    /**
     * 是否使用背景图片
     * @return bool
     */
    public function isBgImage() : bool
    {
        return (bool) $this->value('bg_image', false);
    }
    
    
    /**
     * 获取验证码长度
     * @return int
     */
    public function getLength() : int
    {
        $num = (int) $this->value('length', 0);
        
        return $num < 1 ? 4 : $num;
    }
    
    
    /**
     * 获取过期分钟数
     * @return int
     */
    public function getExpireMinute() : int
    {
        $num = (int) $this->value('expire_minute', 0);
        
        return $num < 1 ? 10 : $num;
    }
    
    
    /**
     * 获取验证码混淆码
     * @return string
     */
    public function getToken() : string
    {
        $token = $this->value('token', '');
        
        return $token ?: 'BusyPHP';
    }
    
    
    /**
     * 获取背景色
     * @return string
     */
    public function getBgColor() : string
    {
        return (string) $this->value('bg_color', '');
    }
    
    
    /**
     * 获取验证码类型
     * @return int
     */
    public function getType() : int
    {
        return (int) $this->value('type', 0);
    }
    
    
    /**
     * 获取字体大小
     * @return int
     */
    public function getFontSize() : int
    {
        $num = (int) $this->value('font_size', 0);
        
        return $num < 1 ? 25 : $num;
    }
    
    
    /**
     * 获取验证码字体
     * @return string
     */
    public function getFont() : string
    {
        return (string) $this->value('font', '');
    }
    
    
    /**
     * 获取验证码字体文件
     * @param bool $isPath
     * @return string
     */
    public function getFontFile(bool $isPath = false) : string
    {
        $path = $this->value('font_file', '');
        
        return $isPath ? App::urlToPath($path) : $path;
    }
    
    
    /**
     * 获取自定义验证码字符
     * @return string
     */
    public function getCode() : string
    {
        return str_replace(["\r", "\n", "\t", " "], '', (string) $this->value('code', ''));
    }
}