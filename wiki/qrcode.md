# 二维码

该功能扩展自 `endroid/qr-code`，定义了一些常见用法方便使用，可以满足大部分常用场景。

```php
\BusyPHP\QrCode // 二维码类
\BusyPHP\facade\QrCode // 二维码工厂类
```

## 输出二维码

```php
use BusyPHP\facade\QrCode;

class QrCodeController {
    public function index() {
        return QrCode::text("https://www.baidu.com")->response();
    }
}
```

## 在线二维码
```php
use BusyPHP\facade\QrCode;

class QrCodeController {
    public function index() {
        return '<img src="'. QrCode::text('https://www.baidu.com')->url() .'"/>';
    }
}
```