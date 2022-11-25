<?php
declare(strict_types = 1);

namespace BusyPHP;

use BusyPHP\exception\FileNotFoundException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\ClassHelper;
use BusyPHP\helper\TransHelper;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode as EndroidQrCode;
use think\cache\driver\File;
use think\facade\Request;
use think\facade\Route;
use think\Response;
use think\route\Url;

/**
 * 二维码生成类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/12 下午7:41 下午 QrCode.php $
 */
class QrCode
{
    // +----------------------------------------------------
    // + 识别率级别
    // +----------------------------------------------------
    /** @var string 还原率7％ */
    const LEVEL_LOW = 'L';
    
    /** @var string 还原率15% */
    const LEVEL_MEDIUM = 'M';
    
    /** @var string 还原率25% */
    const LEVEL_QUARTILE = 'Q';
    
    /** @var string 还原率30% */
    const LEVEL_HIGH = 'H';
    
    // +----------------------------------------------------
    // + 二维码格式
    // +----------------------------------------------------
    /** @var string PNG */
    const FORMAT_PNG = 'png';
    
    /** @var string SVG */
    const FORMAT_SVG = 'svg';
    
    /** @var string BINARY */
    const FORMAT_BINARY = 'binary';
    
    /** @var string ESP */
    const FORMAT_EPS = 'eps';
    
    /** @var string PDF */
    const FORMAT_PDF = 'pdf';
    
    /**
     * @var App
     */
    protected $app;
    
    /**
     * 是否下载
     * @var bool
     */
    protected $download = false;
    
    /**
     * 下载文件名
     * @var string
     */
    protected $filename = '';
    
    /**
     * 配置
     * @var array
     */
    protected $options = [
        'lifetime'    => 0,
        'text'        => '',
        'margin'      => 0,
        'size'        => 300,
        'logo'        => '',
        'logo_width'  => 0,
        'logo_height' => 0,
        'level'       => '',
        'format'      => ''
    ];
    
    
    /**
     * QRCode constructor.
     * @param string $text 二维码文本
     */
    public function __construct($text = '')
    {
        $this->app = App::getInstance();
        $this->text($text);
    }
    
    
    /**
     * 设置文本
     * @param string $text
     * @return $this
     */
    public function text(string $text) : self
    {
        $this->options['text'] = $text;
        
        return $this;
    }
    
    
    /**
     * 设置还原率级别
     * @param string $level
     * @return $this
     */
    public function level(string $level) : self
    {
        $this->options['level'] = $level;
        
        
        return $this;
    }
    
    
    /**
     * 设置空白间距
     * @param int $margin
     * @return $this
     */
    public function margin(int $margin) : self
    {
        $this->options['margin'] = $margin;
        
        return $this;
    }
    
    
    /**
     * 设置二维码尺寸
     * @param int $size
     * @return $this
     */
    public function size(int $size) : self
    {
        $this->options['size'] = $size;
        
        return $this;
    }
    
    
    /**
     * 设置LOGO路径
     * @param string $logo logo 路径
     * @param int    $width 宽度
     * @param int    $height 宽度
     * @return $this
     */
    public function logo(string $logo, int $width = 0, int $height = 0) : self
    {
        $this->options['logo']        = $logo;
        $this->options['logo_width']  = $width;
        $this->options['logo_height'] = $height;
        
        return $this;
    }
    
    
    /**
     * 设置输出类型
     * @param string $format
     * @return $this
     */
    public function format(string $format) : self
    {
        $format = strtolower($format);
        $format = in_array($format, array_keys(self::getFormats())) ? $format : self::FORMAT_PNG;
        
        $this->options['format'] = $format;
        
        return $this;
    }
    
    
    /**
     * 设置是否下载 与 {@see QrCode::cache()} 互斥
     * @param string $filename 文件名
     * @return $this
     */
    public function download(string $filename = '') : self
    {
        $this->download = true;
        $this->filename = $filename;
        
        $this->options['lifetime'] = 0;
        
        return $this;
    }
    
    
    /**
     * 设置缓存多少秒 与 {@see QrCode::download()} 互斥
     * @param int $lifetime 过期秒数
     * @return $this
     */
    public function cache(int $lifetime) : self
    {
        $this->download = false;
        
        $this->options['lifetime'] = $lifetime;
        
        return $this;
    }
    
    
    /**
     * 准备 EndroidQrCode 参数
     * @return EndroidQrCode
     */
    protected function prepareEndroidQrCode() : EndroidQrCode
    {
        $driver = new EndroidQrCode();
        $driver->setEncoding('UTF-8');
        $driver->setRoundBlockSize(true, EndroidQrCode::ROUND_BLOCK_SIZE_MODE_SHRINK);
        $driver->setText($this->options['text']);
        $driver->setMargin($this->options['margin']);
        $driver->setSize($this->options['size']);
        
        // LOGO
        if ($this->options['logo'] !== '') {
            if (!is_file($this->options['logo'])) {
                throw new FileNotFoundException($this->options['logo']);
            }
            $driver->setLogoPath($this->options['logo']);
            
            if ($this->options['logo_width'] > 0) {
                $driver->setLogoWidth($this->options['logo_width']);
            }
            
            if ($this->options['logo_height'] > 0) {
                $driver->setLogoHeight($this->options['logo_height']);
            }
        }
        
        // 输出类型
        if ($this->options['format'] !== '') {
            if ($this->options['format'] == 'pdf') {
                $this->options['format'] = 'fpdf';
            }
            
            $driver->setWriterByName($this->options['format']);
        }
        
        // 二维码识别率
        switch ($this->options['level']) {
            case self::LEVEL_LOW:
                $driver->setErrorCorrectionLevel(ErrorCorrectionLevel::LOW());
            break;
            case self::LEVEL_MEDIUM:
                $driver->setErrorCorrectionLevel(ErrorCorrectionLevel::MEDIUM());
            break;
            case self::LEVEL_QUARTILE:
                $driver->setErrorCorrectionLevel(ErrorCorrectionLevel::QUARTILE());
            break;
            case static::LEVEL_HIGH:
            default:
                $driver->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
            break;
        }
        
        return $driver;
    }
    
    
    /**
     * 响应
     * @return Response
     */
    public function response() : Response
    {
        $qrcode       = $this->prepareEndroidQrCode();
        $writer       = $qrcode->getWriter();
        $download     = $this->download || $writer->getName() == self::FORMAT_EPS;
        $lifetime     = $this->options['lifetime'];
        $cacheControl = "max-age=$lifetime, public";
        
        // 浏览器缓存
        $currentEtag = sprintf('"%s"', md5(json_encode($this->options)));
        $headerEtag  = str_replace('W/', '', Request::header('if-none-match'));
        if (!$download && $headerEtag == $currentEtag) {
            return Response::create(null)
                ->code(304)
                ->contentType($qrcode->getContentType())
                ->header([
                    'Content-Length' => 0,
                    'Cache-Control'  => $cacheControl,
                    'Etag'           => $currentEtag,
                ]);
        }
        
        // 文件缓存
        $cacheOptions = ['path' => $this->app->getRuntimeRootPath('app/general/qrcode')];
        $cacheDriver  = $this->app->make(File::class, [$cacheOptions], true);
        
        // 删除旧缓存
        if ($headerEtag) {
            $cacheDriver->delete($headerEtag);
        }
        if (!$data = $cacheDriver->get($currentEtag)) {
            $cacheDriver->set($currentEtag, $data = $qrcode->writeString(), $lifetime);
        }
        
        // 下载
        $header = ['Content-Length' => strlen($data)];
        if ($download) {
            $header['Content-Disposition'] = sprintf("attachment; filename=\"%s.%s\"", rawurlencode($this->filename ?: date('YmdHis')), $writer::getSupportedExtensions()[0]);
        } else {
            $header['Cache-Control'] = $cacheControl;
            $header['Etag']          = $currentEtag;
        }
        
        return Response::create($data)
            ->code(200)
            ->contentType($qrcode->getContentType())
            ->header($header);
    }
    
    
    /**
     * 构建数据
     * @param bool $dataUri 是否构建为 DATA－URI 数据
     * @return string
     */
    public function build(bool $dataUri = false) : string
    {
        $driver = $this->prepareEndroidQrCode();
        
        return $dataUri ? $driver->writeDataUri() : $driver->writeString();
    }
    
    
    /**
     * 保存到指定路径
     * @param string $path
     */
    public function save(string $path)
    {
        $this->prepareEndroidQrCode()->writeFile($path);
    }
    
    
    /**
     * 通过HTTP响应
     * @return $this
     */
    public function http() : self
    {
        $process = $this->app->request->param('process', '', 'trim');
        $process = explode(',', $process);
        foreach ($process as $item) {
            $item = trim($item);
            if (!$item) {
                continue;
            }
            
            $args  = ArrayHelper::split('/', $item, 2);
            $key   = strtolower(trim(array_shift($args)));
            $value = trim(array_shift($args));
            if (!$key) {
                continue;
            }
            
            switch ($key) {
                case 'text' :
                    $value = TransHelper::base64decodeUrl($value);
                    if ($value) {
                        $this->text($value);
                    }
                break;
                case 'level':
                    if ($value) {
                        $this->level($value);
                    }
                break;
                case 'size':
                    $value = (int) $value;
                    if ($value > 0) {
                        $this->size($value);
                    }
                break;
                case 'margin':
                    $value = (int) $value;
                    if ($value > 0) {
                        $this->margin($value);
                    }
                break;
                case 'logo':
                    $value = TransHelper::base64decodeUrl('/' . ltrim($value, '/'));
                    if ($value !== '' && is_file($value = App::urlToPath($value))) {
                        $map    = ArrayHelper::oneToTwo($args);
                        $width  = $map->get('w', 0, 'intval');
                        $height = $map->get('h', 0, 'intval');
                        $this->logo($value, $width, $height);
                    }
                break;
                case 'cache':
                    $value = (int) $value;
                    if ($value > 0) {
                        $this->cache($value);
                    }
                break;
                case 'down':
                    $map = ArrayHelper::oneToTwo($args);
                    $this->download(TransHelper::base64decodeUrl($map->get('f', '', 'trim')));
                break;
            }
        }
        
        $this->format($this->app->request->param('format/s', '', 'trim'));
        
        return $this;
    }
    
    
    /**
     * 生成URL
     * @return Url
     */
    public function url() : Url
    {
        $params   = [];
        $params[] = "text/" . TransHelper::base64encodeUrl($this->options['text']);
        
        if ($this->options['level']) {
            $params[] = "level/{$this->options['level']}";
        }
        if ($this->options['margin'] > 0) {
            $params[] = "margin/{$this->options['margin']}";
        }
        if ($this->options['size'] > 0) {
            $params[] = "size/{$this->options['size']}";
        }
        if ($this->options['logo']) {
            $args = ["logo/" . TransHelper::base64encodeUrl($this->options['logo'])];
            if ($this->options['logo_width'] > 0) {
                $args[] = "w/{$this->options['logo_width']}";
            }
            if ($this->options['logo_height'] > 0) {
                $args[] = "h/{$this->options['logo_height']}";
            }
            $params[] = implode('/', $args);
        }
        
        if ($this->options['lifetime'] > 0) {
            $params[] = "cache/{$this->options['lifetime']}";
        }
        
        if ($this->download) {
            $args = ["down/1"];
            if ($this->filename !== '') {
                $args[] = "f/" . TransHelper::base64encodeUrl($this->filename);
            }
            $params[] = implode('/', $args);
        }
        
        $format  = $this->options['format'] ?: self::FORMAT_PNG;
        $process = implode(',', $params);
        
        return Route::buildUrl('/general/qrcode', ['process' => $process])->suffix($format);
    }
    
    
    /**
     * 获取还原率
     * @return array
     */
    public static function getLevels() : array
    {
        return ClassHelper::getConstAttrs(self::class, 'LEVEL_', ClassHelper::ATTR_NAME);
    }
    
    
    /**
     * 获取输出类型
     * @return array
     */
    public static function getFormats() : array
    {
        return ClassHelper::getConstAttrs(self::class, 'FORMAT_', ClassHelper::ATTR_NAME);
    }
}