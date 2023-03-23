# 文件系统

内置的文件系统驱动仅支持本地文件操作，支持三方平台需要安装额外的扩展。

```php
// 文件系统类
\think\Filesystem::class  

// 文件系统工厂类
\think\facade\Filesystem::class  

// 文件系统辅助类
\BusyPHP\helper\FilesystemHelper::class 
```

## 配置

`config/filesystem.php`

```php
return [
    // 默认磁盘
    'default' => env('filesystem.driver', 'local'),
    // 磁盘列表
    'disks'   => [
        // 临时文件驱动
        'local' => [
            // 磁盘类型
            'type' => 'local',
            // 磁盘路径
            'root' => app()->getRuntimePath() . 'uploads',
        ],
        
        // 公共文件驱动
        'public' => [
            // 磁盘类型
            'type'       => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath() . 'public/uploads',
            // 磁盘路径对应的外部URL路径
            'url'        => '/uploads',
            // 可见性
            'visibility' => 'public',
        ],
        
        // 更多的磁盘配置信息
    ]
];
```

## 文件操作

```php
use \think\facade\Filesystem;

// 判断 public/uploads/logo.png 是否存在
Filesystem::disk('public')->fileExists('logo.png');

// 创建并写入字符到文件 public/uploads/a.txt
Filesystem::disk('public')->write('a.txt', 'test');

// 创建并写入Stream到文件 public/uploads/a.txt
Filesystem::disk('public')->writeStream('a.txt', $stream);

// 读取字符串 public/uploads/a.txt 
Filesystem::disk('public')->read('a.txt');

// 读取Stream public/uploads/a.txt 
Filesystem::disk('public')->readStream('a.txt');

// 删除文件 public/uploads/a.txt 
Filesystem::disk('public')->delete('a.txt');

// 移动文件 public/uploads/a.txt 至 public/uploads/b.txt
Filesystem::disk('public')->move('a.txt', 'b.txt');

// 复制文件 public/uploads/a.txt 为 public/uploads/a1.txt
Filesystem::disk('public')->copy('a.txt', 'a1.txt');

// 创建文件夹 public/uploads/a/
Filesystem::disk('public')->createDirectory('a');

// 删除文件夹 public/uploads/a/
Filesystem::disk('public')->deleteDirectory('a');

// 获取文件夹内的文件或文件夹 public/uploads/a/
Filesystem::disk('public')->listContents('a');
```

## 文件上传

文件上传基于`Filesystem`实现，并实现了多种数据格式的上传，以满足常见开发需求。

```php
// 本地上传
\BusyPHP\upload\driver\LocalUpload::class;

// Base64上传
\BusyPHP\upload\driver\Base64Upload::class;

// 字符串上传
\BusyPHP\upload\driver\ContentUpload::class;

// 移动上传
\BusyPHP\upload\driver\MoveUpload::class;

// 分块上传
\BusyPHP\upload\driver\PartUpload::class;

// 下载远程文件
\BusyPHP\upload\driver\RemoteUpload::class;
```

### 本地上传

```php
$driver = \BusyPHP\Upload::init(new \BusyPHP\upload\parameter\LocalParameter($_FILES['file']))->save();
```