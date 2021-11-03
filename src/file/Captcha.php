<?php
declare(strict_types = 1);

namespace BusyPHP\file;

use BusyPHP\App;
use BusyPHP\app\admin\setting\CaptchaSetting;
use BusyPHP\exception\VerifyException;
use think\facade\Session;
use think\Response;

/**
 * 验证码类
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/20 下午下午12:13 Captcha.php $
 */
class Captcha
{
    /** @var string 未填写验证码 */
    const VERIFY_EMPTY_CODE = 'empty_code';
    
    /** @var string 验证码错误 */
    const VERIFY_ERROR = 'error';
    
    /** @var string 验证码过期 */
    const VERIFY_EXPIRE = 'expire';
    
    /**
     * 混淆码
     * @var string
     */
    protected $token = 'BusyPHP';
    
    /**
     * 英文数字验证码字符
     * @var string
     */
    protected $chars = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';
    
    /**
     * 中文验证码字符
     * @var string
     */
    protected $zhChars = '们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借';
    
    /**
     * 过期时间
     * @var int
     */
    protected $expire = 600;
    
    /**
     * 是否使用中文验证码
     * @var bool
     */
    protected $zh = false;
    
    /**
     * 是否使用背景图
     * @var bool
     */
    protected $bgImage = false;
    
    /**
     * 字体大小(像素)
     * @var int
     */
    protected $fontSize = 25;
    
    /**
     * 是否绘制曲线
     * @var bool
     */
    protected $curve = false;
    
    /**
     * 是否添加杂点
     * @var bool
     */
    protected $noise = false;
    
    /**
     * 验证码高度
     * @var int
     */
    protected $height = 0;
    
    /**
     * 验证码宽度
     * @var int
     */
    protected $width = 0;
    
    /**
     * 验证码长度
     * @var int
     */
    protected $length = 4;
    
    /**
     * 验证码字体文件
     * @var string
     */
    protected $fontFile = '';
    
    /**
     * 验证码背景色
     * @var array
     */
    protected $bg = [243, 251, 254];
    
    /**
     * 验证成功后是否重置
     * @var bool
     */
    protected $reset = true;
    
    /**
     * 客户端
     * @var string
     */
    protected $app;
    
    /**
     * @var resource
     */
    private $resource;
    
    /**
     * @var int
     */
    private $color;
    
    /**
     * @var CaptchaSetting
     */
    private $setting;
    
    
    /**
     * Captcha constructor.
     * @param string $app 客户端
     */
    public function __construct(string $app = '')
    {
        $this->setting = CaptchaSetting::init();
        $this->setting->setClient($app ?: App::getInstance()->getDirName());
        $this->curve($this->setting->isCurve());
        $this->noise($this->setting->isNoise());
        $this->bgImage($this->setting->isBgImage());
        $this->length($this->setting->getLength());
        $this->expire($this->setting->getExpireMinute() * 60);
        $this->fontSize($this->setting->getFontSize());
        $this->token($this->setting->getToken());
        
        // 背景颜色
        $bgColor = $this->setting->getBgColor();
        if ($bgColor) {
            $this->bgColor($bgColor);
        }
        
        // 验证码类型
        switch ($this->setting->getType()) {
            // 纯英文
            case 1:
                $this->chars('abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY');
            break;
            // 纯数字
            case 2:
                $this->chars('0123456789');
            break;
            // 中文
            case 3:
                $this->zh(true);
            break;
        }
        
        // 验证码字符
        $code = $this->setting->getCode();
        if ($code) {
            if ($this->zh) {
                $this->zhChars($code);
            } else {
                $this->chars($code);
            }
        }
        
        // 验证码字体
        $font = $this->setting->getFont();
        if ($font) {
            if (0 === strpos($font, 'zh_')) {
                $path = __DIR__ . DIRECTORY_SEPARATOR . 'captcha' . DIRECTORY_SEPARATOR . 'zhttfs' . DIRECTORY_SEPARATOR;
                $this->fontFile($path . substr($font, 3) . '.ttf');
            } else {
                $path = __DIR__ . DIRECTORY_SEPARATOR . 'captcha' . DIRECTORY_SEPARATOR . 'ttfs' . DIRECTORY_SEPARATOR;
                $this->fontFile($path . $font . '.ttf');
            }
        }
        
        // 自定义字体
        $fontFile = $this->setting->getFontFile(true);
        if ($fontFile) {
            $this->fontFile($fontFile);
        }
    }
    
    
    /**
     * 设置混淆码
     * @param string $token
     * @return Captcha
     */
    public function token(string $token) : Captcha
    {
        $this->token = $token;
        
        return $this;
    }
    
    
    /**
     * 设置英文数字字符
     * @param string $chars
     * @return Captcha
     */
    public function chars(string $chars) : Captcha
    {
        $this->chars = $chars;
        
        return $this;
    }
    
    
    /**
     * 设置中文字符
     * @param string $zhChars
     * @return Captcha
     */
    public function zhChars(string $zhChars) : Captcha
    {
        $this->zhChars = $zhChars;
        
        return $this;
    }
    
    
    /**
     * 设置过期时间
     * @param int $expire
     * @return Captcha
     */
    public function expire(int $expire) : Captcha
    {
        $this->expire = $expire;
        
        return $this;
    }
    
    
    /**
     * 设置是否使用中文字符
     * @param bool $zh
     * @return Captcha
     */
    public function zh(bool $zh) : Captcha
    {
        $this->zh = $zh;
        
        return $this;
    }
    
    
    /**
     * 设置是否使用背景图
     * @param bool $bgImage
     * @return Captcha
     */
    public function bgImage(bool $bgImage) : Captcha
    {
        $this->bgImage = $bgImage;
        
        return $this;
    }
    
    
    /**
     * 设置字体大小
     * @param int $fontSize
     * @return Captcha
     */
    public function fontSize(int $fontSize) : Captcha
    {
        $this->fontSize = $fontSize;
        
        return $this;
    }
    
    
    /**
     * 设置是否绘制线条
     * @param bool $curve
     * @return Captcha
     */
    public function curve(bool $curve) : Captcha
    {
        $this->curve = $curve;
        
        return $this;
    }
    
    
    /**
     * 设置是否添加杂点
     * @param bool $noise
     * @return Captcha
     */
    public function noise(bool $noise) : Captcha
    {
        $this->noise = $noise;
        
        return $this;
    }
    
    
    /**
     * 设置验证码高度
     * @param int $height
     * @return Captcha
     */
    public function height(int $height) : Captcha
    {
        $this->height = $height;
        
        return $this;
    }
    
    
    /**
     * 设置验证码宽度
     * @param int $width
     * @return Captcha
     */
    public function width(int $width) : Captcha
    {
        $this->width = $width;
        
        return $this;
    }
    
    
    /**
     * 设置验证码长度
     * @param int $length
     * @return Captcha
     */
    public function length(int $length) : Captcha
    {
        $this->length = $length;
        
        return $this;
    }
    
    
    /**
     * 设置验证码字体文件
     * @param string $fontFile
     * @return Captcha
     */
    public function fontFile(string $fontFile) : Captcha
    {
        $this->fontFile = $fontFile;
        
        return $this;
    }
    
    
    /**
     * 设置背景颜色
     * @param array $bg
     * @return Captcha
     */
    public function bg(array $bg) : Captcha
    {
        $this->bg = $bg;
        
        return $this;
    }
    
    
    /**
     * 设置背景颜色16进制格式
     * @param string $color
     * @return Captcha
     */
    public function bgColor(string $color) : Captcha
    {
        $color = ltrim($color, '#');
        $r     = hexdec(substr($color, 0, 2));
        $g     = hexdec(substr($color, 2, 2));
        $b     = hexdec(substr($color, 4, 2));
        
        return $this->bg([$r, $g, $b]);
    }
    
    
    /**
     * 校验验证码
     * @param string $code 用户验证码
     * @param string $id 验证码标识
     */
    public function check(string $code, string $id = '')
    {
        if (empty($code)) {
            throw new VerifyException('请输入验证码', self::VERIFY_EMPTY_CODE);
        }
        
        $key  = $this->hash($this->token) . $id;
        $data = Session::get($key);
        if (empty($data)) {
            throw new VerifyException('验证码不正确', self::VERIFY_ERROR);
        }
        
        if (time() - $data['verify_time'] > $this->expire) {
            Session::delete($key);
            
            throw new VerifyException('验证码已过期', self::VERIFY_EXPIRE);
        }
        
        if ($this->hash(strtoupper($code)) == $data['verify_code']) {
            $this->reset && Session::delete($key);
            
            return;
        }
        
        throw new VerifyException('验证码不正确', self::VERIFY_EXPIRE);
    }
    
    
    /**
     * 清理验证码
     * @param string $id 验证码标识
     */
    public function clear(string $id = '')
    {
        Session::delete($this->hash($this->token) . $id);
    }
    
    
    /**
     * 输出验证码并把验证码的值保存的session中
     * 验证码保存到session的格式为： array('verify_code' => '验证码值', 'verify_time' => '验证码创建时间');
     * @access public
     * @param string $id 要生成验证码的标识
     * @return Response
     */
    public function entry($id = '')
    {
        // 宽高计算
        $this->width || $this->width = $this->length * $this->fontSize * 1.5 + $this->length * $this->fontSize / 2;
        $this->height || $this->height = $this->fontSize * 2.5;
        $this->width  = (int) $this->width;
        $this->height = (int) $this->height;
        
        // 创建画布
        $this->resource = imagecreate($this->width, $this->height);
        imagecolorallocate($this->resource, $this->bg[0], $this->bg[1], $this->bg[2]);
        
        // 验证码文字颜色
        $this->color = imagecolorallocate($this->resource, mt_rand(1, 150), mt_rand(1, 150), mt_rand(1, 150));
        
        // 字体文件
        $ttfPath = __DIR__ . DIRECTORY_SEPARATOR . 'captcha' . DIRECTORY_SEPARATOR . ($this->zh ? 'zhttfs' : 'ttfs') . DIRECTORY_SEPARATOR;
        if (empty($this->fontFile) || !is_file($this->fontFile)) {
            $ttfList        = glob($ttfPath . '*.ttf');
            $this->fontFile = $ttfList[array_rand($ttfList)];
        }
        
        // 绘制背景图
        if ($this->bgImage) {
            $this->drawBackground();
        }
        
        // 绘杂点
        if ($this->noise) {
            $this->drawNoise();
        }
        
        // 绘干扰线
        if ($this->curve) {
            $this->drawCurve();
        }
        
        // 绘验证码
        $code   = [];      // 验证码
        $codeNX = 0;       // 验证码第N个字符的左边距
        
        // 中文验证码
        if ($this->zh) {
            for ($i = 0; $i < $this->length; $i++) {
                $code[$i] = iconv_substr($this->zhChars, (int) floor(mt_rand(0, mb_strlen($this->zhChars, 'utf-8') - 1)), 1, 'utf-8');
                imagettftext($this->resource, $this->fontSize, mt_rand(-40, 40), intval($this->fontSize * ($i + 1) * 1.5), intval($this->fontSize + mt_rand(10, 20)), $this->color, $this->fontFile, $code[$i]);
            }
        } else {
            for ($i = 0; $i < $this->length; $i++) {
                $code[$i] = $this->chars[mt_rand(0, strlen($this->chars) - 1)];
                $codeNX   += mt_rand(intval($this->fontSize * 1.2), intval($this->fontSize * 1.6));
                imagettftext($this->resource, $this->fontSize, mt_rand(-40, 40), $codeNX, intval($this->fontSize * 1.6), $this->color, $this->fontFile, $code[$i]);
            }
        }
        
        // 保存验证码
        $key                 = $this->hash($this->token);
        $code                = $this->hash(strtoupper(implode('', $code)));
        $data                = [];
        $data['verify_code'] = $code;  // 把校验码保存到session
        $data['verify_time'] = time(); // 验证码创建时间
        Session::set($key . $id, $data);
        
        ob_start();
        imagepng($this->resource);
        $content = ob_get_clean();
        imagedestroy($this->resource);
        
        return Response::create($content, 'html', 200)->header([
            'Content-Length' => strlen($content),
            'Cache-Control'  => 'private, max-age=0, no-store, no-cache, must-revalidate',
            'Pragma'         => 'no-cache',
        ])->contentType('image/png');
    }
    
    
    /**
     * 画一条由两条连在一起构成的随机正弦函数曲线作干扰线(你可以改成更帅的曲线函数)
     * 高中的数学公式咋都忘了涅，写出来
     *   正弦型函数解析式：y=Asin(ωx+φ)+b
     *   各常数值对函数图像的影响：
     *      A：决定峰值（即纵向拉伸压缩的倍数）
     *      b：表示波形在Y轴的位置关系或纵向移动距离（上加下减）
     *      φ：决定波形与X轴位置关系或横向移动距离（左加右减）
     *      ω：决定周期（最小正周期T=2π/∣ω∣）
     */
    private function drawCurve()
    {
        $py = 0;
        
        // 曲线前部分
        $A   = mt_rand(1, intval($this->height / 2));                  // 振幅
        $b   = mt_rand(intval(-$this->height / 4), intval($this->height / 4)); // Y轴方向偏移量
        $f   = mt_rand(intval(-$this->height / 4), intval($this->height / 4)); // X轴方向偏移量
        $T   = mt_rand($this->height, $this->width * 2);      // 周期
        $w   = (2 * M_PI) / $T;
        $px1 = 0;  // 曲线横坐标起始位置
        $px2 = mt_rand(intval($this->width / 2), intval($this->width * 0.8)); // 曲线横坐标结束位置
        
        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if ($w != 0) {
                // y = Asin(ωx+φ) + b
                $py = intval($A * sin($w * $px + $f) + $b + $this->height / 2);
                $i  = (int) ($this->fontSize / 5);
                while ($i > 0) {
                    // 这里(while)循环画像素点比 imageTtfText 和 imageString 用字体大小一次画出（不用这while循环）性能要好很多
                    imagesetpixel($this->resource, $px + $i, $py + $i, $this->color);
                    $i--;
                }
            }
        }
        
        // 曲线后部分
        $A   = mt_rand(1, intval($this->height / 2)); // 振幅
        $f   = mt_rand(intval(-$this->height / 4), intval($this->height / 4)); // X轴方向偏移量
        $T   = mt_rand($this->height, $this->width * 2); // 周期
        $w   = (2 * M_PI) / $T;
        $b   = $py - $A * sin($w * $px + $f) - $this->height / 2;
        $px1 = $px2;
        $px2 = $this->width;
        
        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if ($w != 0) {
                $py = intval($A * sin($w * $px + $f) + $b + $this->height / 2); // y = Asin(ωx+φ) + b
                $i  = (int) ($this->fontSize / 5);
                while ($i > 0) {
                    imagesetpixel($this->resource, $px + $i, $py + $i, $this->color);
                    $i--;
                }
            }
        }
    }
    
    
    /**
     * 画杂点
     * 往图片上写不同颜色的字母或数字
     */
    private function drawNoise()
    {
        $codeSet = '2345678abcdefhijkmnpqrstuvwxyz';
        for ($i = 0; $i < 10; $i++) {
            $noiseColor = imagecolorallocate($this->resource, mt_rand(150, 225), mt_rand(150, 225), mt_rand(150, 225));
            for ($j = 0; $j < 5; $j++) {
                imagestring($this->resource, 5, mt_rand(-10, $this->width), mt_rand(-10, $this->height), $codeSet[mt_rand(0, 29)], $noiseColor);
            }
        }
    }
    
    
    /**
     * 绘制背景图片
     * 注：如果验证码输出图片比较大，将占用比较多的系统资源
     */
    private function drawBackground()
    {
        $path   = __DIR__ . DIRECTORY_SEPARATOR . 'captcha' . DIRECTORY_SEPARATOR . 'bgs' . DIRECTORY_SEPARATOR;
        $bgs    = glob("{$path}*.*");
        $bgFile = $bgs[array_rand($bgs)];
        
        [$width, $height] = getimagesize($bgFile);
        $bgImage = imagecreatefromjpeg($bgFile);
        imagecopyresampled($this->resource, $bgImage, 0, 0, 0, 0, $this->width, $this->height, $width, $height);
        imagedestroy($bgImage);
    }
    
    
    /**
     * 加密验证码
     * @param string $str
     * @return string
     */
    private function hash(string $str) : string
    {
        $key = substr(md5($this->token), 5, 8);
        $str = substr(md5($str), 8, 10);
        
        return md5($key . $str);
    }
}