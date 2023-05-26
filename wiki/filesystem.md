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

## 基本文件上传

文件上传基于`Filesystem`实现，并实现了多种数据格式的上传，以满足常见开发需求。

```php
<?php
use BusyPHP\facade\Uploader;
use BusyPHP\uploader\driver\local\LocalData;
use BusyPHP\uploader\driver\Base64;
use BusyPHP\uploader\driver\base64\Base64Data;
use BusyPHP\uploader\driver\Content;
use BusyPHP\uploader\driver\content\ContentData;
use BusyPHP\uploader\driver\Move;
use BusyPHP\uploader\driver\move\MoveData;
use BusyPHP\uploader\driver\Remote;
use BusyPHP\uploader\driver\remote\RemoteData
use BusyPHP\uploader\driver\Part;
use BusyPHP\uploader\driver\part\PartAbortData;
use BusyPHP\uploader\driver\part\PartPrepareData;
use BusyPHP\uploader\driver\part\PartPutData;
use BusyPHP\uploader\driver\part\PartMergeData;
use \BusyPHP\uploader\driver\part\exception\PartPreparedException;
use \BusyPHP\uploader\driver\part\exception\PartAbortedException;
use \BusyPHP\uploader\driver\part\exception\PartPuttedException;

// 本地上传
$res = Uploader::upload(new LocalData());

// Base64上传
$res = Uploader::driver(Base64::class)->upload(new Base64Data());

// 字符串上传
$res = Uploader::driver(Content::class)->upload(new ContentData());

// 将文件移动至上传目录
$res = Uploader::driver(Move::class)->upload(new MoveData());

// 将远程文件下载至上传目录
$res = Uploader::driver(Remote::class)->upload(new RemoteData());

// 分块上传
if ($action === 'prepare') {
    $data = new PartPrepareData();
} elseif ($action === 'put') {
    $data = new PartPutData();
} elseif ($action === 'marge') {
    $data = new PartMergeData();
} elseif ($action === 'abort') {
    $data = new PartAbortData();
}
try {
    $res = Uploader::driver(Part::class)->upload($data);
} catch (PartPreparedException $e) {
    // 拦截分块上传已预备完成异常，获取上传ID
    $e->getUploadId();
} catch (PartPuttedException $e) {
    // 拦截单个分块上传已完成异常，获取上传结果
    $e->getResult();
} catch (PartAbortedException $e) {
    // 拦截已终止上传异常
    // ...
}

```

## 上传文件至管理系统
```php
<?php
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\model\system\file\SystemFileUploadData;
use BusyPHP\app\admin\model\system\file\SystemFileFrontPrepareUploadData;

// 单文件上传，前端流程简单，支持秒传
// 秒传验证缺点，需要将文件上传至服务器进行验证，如果是秒传的话额外消耗一次流量
SystemFile::init()->upload(new SystemFileUploadData());


// 单文件/大文件分块上传，前端流程复杂
// 秒传验证优点，前端计算出文件md5提供给服务端进行秒传验证
class Test extends \BusyPHP\Controller {

    // 1. 准备上传
    // 获取前端提供的参数
    public function prepare() {
        $data = new SystemFileFrontPrepareUploadData();
        $result = SystemFile::init()->frontPrepareUpload($data);
        
        return json([
            // 判断是否已经秒传完毕，如果没有则可以进入第2步：文件上传
            'need_upload' => !$result->getInfo()->fast,
            
            // 获取预上传URL，一般使用云存储才有意义，本地上传需要自己定义
            'server_url' => $result->getServerUrl(),
            
            // 获取预上传文件ID，在 upload 和 done 方法中回传
            'upload_id' => $result->getUploadId(),
            
            // 返回文件ID，在 upload 和 done 方法中回传
            'id' => $result->getInfo()->id,
        ])
    }
    
    // 2.本地上传单个文件或分块数据
    // 仅限本地磁盘系统，如果是云存储
    public function upload() {
        $id = $this->param('id/d');
        $uploadId = $this->param('upload_id/d');
        $partNumber = $this->param('part_number/d');
        $file = $this->request->file('file');
        
        $result = SystemFile::init()->frontLocalUpload($id, $file, $uploadId, $partNumber);
        
        return json([
            // 返回已上传的分块数据或文件数据 在 done 方法中回传
            'part' => $result
        ]);
    }
    
    // 3. 完成上传
    public function done() {
        $id = $this->param('id/d');
        $uploadId = $this->param('upload_id/d');
        $parts = $this->param('parts/a');
        
        SystemFile::init()->frontDoneUpload($id, $uploadId, $parts);
    }
}
```