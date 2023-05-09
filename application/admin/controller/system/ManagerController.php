<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\annotation\MenuNode;
use BusyPHP\app\admin\annotation\MenuRoute;
use BusyPHP\app\admin\component\common\SimpleForm;
use BusyPHP\app\admin\component\js\driver\Select;
use BusyPHP\app\admin\component\js\driver\select\SelectOption;
use BusyPHP\app\admin\component\js\driver\Table;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClassField;
use BusyPHP\app\admin\model\system\file\image\SystemFileImageStyle;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\setting\AdminSetting;
use BusyPHP\app\admin\setting\CaptchaSetting;
use BusyPHP\app\admin\setting\PublicSetting;
use BusyPHP\app\admin\setting\StorageSetting;
use BusyPHP\facade\Image;
use BusyPHP\helper\AppHelper;
use BusyPHP\helper\FileHelper;
use BusyPHP\helper\FilesystemHelper;
use BusyPHP\image\result\ImageStyleResult;
use BusyPHP\model\ArrayOption;
use League\Flysystem\FilesystemException;
use ReflectionException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\facade\Filesystem;
use think\Response;
use Throwable;

/**
 * 系统管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/18 下午下午3:50 SystemManager.php $
 */
#[MenuRoute(path: 'system_manager', class: true)]
class ManagerController extends InsideController
{
    /**
     * @var string
     */
    protected $disk;
    
    
    protected function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $disk       = $this->param('disk/s', 'trim');
        $this->disk = $disk ?: FilesystemHelper::STORAGE_PUBLIC;
    }
    
    
    /**
     * 写入菜单
     * @return array
     * @throws Throwable
     */
    protected function assignNav() : array
    {
        $navs = SystemMenu::init()->getChildList('system_manager/index', true, true);
        $this->assign('nav', $navs);
        
        return $navs;
    }
    
    
    /**
     * 系统设置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    #[MenuNode(menu: true, parent: '#system_manager', icon: 'fa fa-cogs', sort: 1)]
    public function index() : Response
    {
        if ($this->isPost()) {
            $data = $this->post('data/a');
            PublicSetting::instance()->set($data);
            $this->log()->record(self::LOG_UPDATE, '系统基本设置');
            $this->updateCache();
            
            return $this->success('设置成功');
        }
        
        $this->assign([
            'info' => PublicSetting::instance()->get(),
            'nav'  => $this->assignNav()
        ]);
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 管理面板
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function admin() : Response
    {
        if ($this->isPost()) {
            $data = $this->post('data/a');
            AdminSetting::instance()->set($data);
            $this->log()->record(self::LOG_UPDATE, '管理面板设置');
            $this->updateCache();
            
            return $this->success('设置成功');
        }
        
        $info                     = AdminSetting::instance()->get();
        $info['watermark']['txt'] = ($info['watermark']['txt'] ?? '') ?: "登录人：{username}\r\n内部系统，严禁拍照，截图\r\n{time}";
        $this->assign('info', $info);
        $this->assignNav();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 存储设置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function storage() : Response
    {
        $setting = StorageSetting::instance();
        if ($this->isPost()) {
            $setting->set($this->post('data/a'));
            $this->log()->record(self::LOG_UPDATE, '存储设置');
            $this->updateCache();
            
            return $this->success('设置成功');
        }
        
        $this->assign('clients', AppHelper::getList());
        $this->assign('disks', $setting::getDisks());
        $this->assign('info', $setting->get());
        $this->assignNav();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 文件分类
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function file_class() : Response
    {
        // 分类列表数据
        if ($table = Table::initIfRequest()) {
            return $table
                ->model(SystemFileClass::init())
                ->query(function(SystemFileClass $model, ArrayOption $option) {
                    $model->order(SystemFileClassField::sort(), 'asc');
                    $model->order(SystemFileClassField::id(), 'desc');
                })
                ->response();
        }
        
        // 分类列表
        $this->assign('file_class', SystemFileClass::init()->order('sort ASC')->selectList());
        $this->assign('type', SystemFile::class()::getTypes());
        $this->assign('info', StorageSetting::instance()->get());
        $this->assignNav();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 添加文件分类
     * @return Response
     * @throws DbException
     */
    #[MenuNode(menu: false, parent: '/file_class')]
    public function file_class_add() : Response
    {
        if ($this->isPost()) {
            SystemFileClass::init()->create(SystemFileClassField::init($this->post()));
            $this->log()->record(self::LOG_INSERT, '增加文件分类');
            
            return $this->success('添加成功');
        }
        
        $this->assign('types', SystemFile::class()::getTypes());
        $this->assign('disks', StorageSetting::class()::getDisks());
        $this->assign('image_type', SystemFile::class()::FILE_TYPE_IMAGE);
        $this->assign('info', [
            'type' => ''
        ]);
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 修改文件分类
     * @return Response
     * @throws DbException
     * @throws DataNotFoundException
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/file_class')]
    public function file_class_edit() : Response
    {
        if ($this->isPost()) {
            SystemFileClass::init()->modify(SystemFileClassField::init($this->post()));
            $this->log()->record(self::LOG_UPDATE, '修改文件分类');
            
            return $this->success('修改成功');
        }
        
        $info = SystemFileClass::init()->getInfo($this->get('id'));
        $this->assign('types', SystemFile::class()::getTypes());
        $this->assign('disks', StorageSetting::class()::getDisks());
        $this->assign('image_type', SystemFile::class()::FILE_TYPE_IMAGE);
        $this->assign('info', $info);
        
        
        return $this->insideDisplay('file_class_add');
    }
    
    
    /**
     * 删除文件分类
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/file_class')]
    public function file_class_delete() : Response
    {
        $model = SystemFileClass::init();
        SimpleForm::init()
            ->batch($this->param('id/a', 'intval'), '请选择要删除的文件分类', function(int $id) use ($model) {
                $model->delete($id);
            });
        
        $this->log()->record(self::LOG_DELETE, '删除文件分类');
        
        return $this->success('删除成功');
    }
    
    
    /**
     * 排序文件分类
     * @throws DbException
     */
    #[MenuNode(menu: false, parent: '/file_class')]
    public function file_class_sort() : Response
    {
        SimpleForm::init(SystemFileClass::init())->sort('sort', SystemFileClassField::sort());
        $this->log()->record(self::LOG_UPDATE, '排序文件分类');
        
        return $this->success('排序成功');
    }
    
    
    /**
     * 图片样式
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function image_style() : Response
    {
        if ($table = Table::initIfRequest()) {
            $list = array_values(Filesystem::disk($this->disk)->image()->selectStyleByCache());
            foreach ($list as $item) {
                $item['disk'] = $this->disk;
            }
            
            return $table->list($list)->response();
        }
        
        $this->assign('disks', StorageSetting::class()::getDisks());
        $this->assign('disk', $this->disk);
        $this->assignNav();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 添加图片样式
     * @return Response
     * @throws ReflectionException
     */
    #[MenuNode(menu: false, parent: '/image_style')]
    public function image_style_add() : Response
    {
        if ($this->isPost()) {
            Filesystem::disk($this->disk)->image()->createStyle($this->post('id/s', 'trim'), $this->post('content/a'));
            
            $this->log()->record(self::LOG_INSERT, '添加图片样式');
            
            return $this->success('添加成功');
        }
        
        $image = Filesystem::disk($this->disk)->image();
        $this->assign('info', ['content' => ImageStyleResult::filterContext($image, ImageStyleResult::fillContent())]);
        $this->assign('disk', $this->disk);
        $this->assign('font_list', $image->getFontList());
        $this->assign('font_default', $image->getDefaultFontPath());
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 修改图片样式
     * @return Response
     */
    #[MenuNode(menu: false, parent: '/image_style')]
    public function image_style_edit() : Response
    {
        if ($this->isPost()) {
            Filesystem::disk($this->disk)->image()->updateStyle($this->post('id/s', 'trim'), $this->post('content/a'));
            
            $this->log()->record(self::LOG_INSERT, '修改图片样式');
            
            return $this->success('修改成功');
        }
        
        $image = Filesystem::disk($this->disk)->image();
        $this->assign('info', ImageStyleResult::filterContext($image, $image->getStyleByCache($this->get('id/s', 'trim'))));
        $this->assign('disk', $this->disk);
        $this->assign('font_list', $image->getFontList());
        $this->assign('font_default', $image->getDefaultFontPath());
        
        return $this->insideDisplay('image_style_add');
    }
    
    
    /**
     * 删除图片样式
     */
    #[MenuNode(menu: false, parent: '/image_style')]
    public function image_style_delete() : Response
    {
        $driver = Filesystem::disk($this->disk)->image();
        SimpleForm::init()->batch('id', '请选择要删除的图片样式', function(string $id) use ($driver) {
            $driver->deleteStyle($id);
        });
        
        $this->log()->record(self::LOG_DELETE, '删除图片样式');
        
        return $this->success('删除成功');
    }
    
    
    /**
     * 上传图片样式的水印图片
     * @return Response
     */
    public function image_style_upload_watermark() : Response
    {
        $this->request->setRequestIsAjax();
        $file = $this->request->file('file');
        FileHelper::checkFilesize(StorageSetting::instance()->getMaxSize(), $file->getSize());
        
        return $this->success([
            'file_url' => Filesystem::disk($this->disk)->image()->uploadWatermark($file)
        ]);
    }
    
    
    /**
     * 选择图片样式
     * @return Response
     */
    public function image_style_select() : Response
    {
        return Select::init()
            ->list(
                Filesystem::disk($this->disk)->image()->selectStyleByCache(),
                function(SelectOption $node, ImageStyleResult $item) {
                    $node->setId($item->id);
                    $node->setText($item->id);
                }
            )
            ->response();
    }
    
    
    /**
     * 预览图片样式
     * @return Response
     * @throws FilesystemException
     */
    public function image_style_preview() : Response
    {
        return $this->redirect(
            Image::path(SystemFileImageStyle::class()::getPreviewImagePath($this->disk))
                ->disk($this->disk)
                ->style($this->get('id/s', 'trim'))
                ->url()
        );
    }
    
    
    /**
     * 图形验证码
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function captcha() : Response
    {
        $setting = CaptchaSetting::instance();
        if ($this->isPost()) {
            $data = $this->post('data/a');
            $setting->set($data);
            $this->log()->record(self::LOG_UPDATE, '图形验证码设置');
            $this->updateCache();
            
            return $this->success('设置成功');
        }
        
        $this->assign('clients', AppHelper::getList());
        $this->assign('info', $setting->get());
        $this->assignNav();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 清理缓存
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '#system_manager')]
    public function cache_clear() : Response
    {
        $this->clearCache();
        $this->log()->record(self::LOG_DEFAULT, '清理缓存');
        
        return $this->success('清理缓存成功');
    }
    
    
    /**
     * 缓存加速
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '#system_manager')]
    public function cache_create() : Response
    {
        $this->updateCache();
        $this->log()->record(self::LOG_DEFAULT, '生成缓存');
        
        return $this->success('生成缓存成功');
    }
}