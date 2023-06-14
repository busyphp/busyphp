<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\annotation\MenuNode;
use BusyPHP\app\admin\annotation\MenuRoute;
use BusyPHP\app\admin\component\common\SimpleForm;
use BusyPHP\app\admin\component\js\driver\Table;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\model\system\file\SystemFileField;
use BusyPHP\app\admin\setting\StorageSetting;
use BusyPHP\helper\AppHelper;
use BusyPHP\model\ArrayOption;
use think\db\exception\DbException;
use think\Response;
use Throwable;

/**
 * 文件管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午2:17 下午 FileController.php $
 */
#[MenuRoute(path: 'system_file', class: true)]
class FileController extends InsideController
{
    /**
     * 文件模型
     * @var SystemFile
     */
    protected SystemFile $model;
    
    /**
     * 文件模型字段类
     * @var string|SystemFileField
     */
    protected mixed $field;
    
    
    protected function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model = SystemFile::init();
        $this->field = $this->model->getFieldClass();
    }
    
    
    /**
     * 文件管理
     * @return Response
     */
    #[MenuNode(menu: true, parent: '#system_manager', icon: 'fa fa-file-text', sort: -70)]
    public function index() : Response
    {
        $type = $this->param('type/s', 'trim');
        if ($table = Table::initIfRequest()) {
            return $table
                ->model($this->model)
                ->query(function(SystemFile $model, ArrayOption $option) use ($type) {
                    $option->deleteIfEmpty('client');
                    $option->deleteIfEmpty('disk');
                    $option->deleteIfEmpty('class_type');
                    
                    if ($type) {
                        $option->set('type', $type);
                    }
                    
                    // 时间
                    if ($time = $option->pull('time')) {
                        $model->whereTimeIntervalRange($this->field::createTime(), $time, ' - ', true);
                    }
                    
                    if ($uploadType = $option->pull('upload_type', 0, 'intval')) {
                        switch ($uploadType) {
                            case 1:
                                $model->whereComplete()->where($this->field::fast(0));
                            break;
                            case 2:
                                $model->whereComplete()->where($this->field::fast(1));
                            break;
                            case 3:
                                $model->whereComplete(false);
                            break;
                        }
                    }
                    
                    $this->indexTableQuery($model, $option);
                })
                ->response();
        }
        
        $this->assignIndexData();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 自定义列表查询条件
     * @param SystemFile  $model
     * @param ArrayOption $option
     */
    protected function indexTableQuery(SystemFile $model, ArrayOption $option)
    {
    }
    
    
    /**
     * 赋值列表模版数据
     */
    protected function assignIndexData()
    {
        // 客户端选项
        $clientOptions = AppHelper::getList();
        array_unshift($clientOptions, ['name' => '不限', 'dir' => '']);
        $clientOptions[] = ['name' => AppHelper::CLI_CLIENT_NAME, 'dir' => AppHelper::CLI_CLIENT_KEY];
        $clientOptions   = array_map(function($item) {
            return [
                'name' => $item['name'],
                'dir'  => $item['dir']
            ];
        }, $clientOptions);
        $this->assign('client_options', $clientOptions);
        
        // 分类选项
        $cateOptions = SystemFileClass::init()->getList();
        array_unshift($cateOptions, [
            'name' => '不限',
            'var'  => ''
        ]);
        $this->assign('cate_options', $cateOptions);
        
        // 类型选项
        $types = $this->model::getTypes();
        $types = [
                '' => [
                    'name' => '全部',
                    'icon' => 'fa fa-ellipsis-h'
                ]
            ] + $types;
        
        $this->assign('types', $types);
        $this->assign('type', $this->param('type/s', 'trim'));
        
        // 磁盘选项
        $disks = StorageSetting::class()::getDisks();
        array_unshift($disks, [
            'name' => '不限',
            'type' => ''
        ]);
        $this->assign('disks', $disks);
        $this->assign('upload_types', ['不限', '普通', '秒传', '上传中']);
    }
    
    
    /**
     * 上传文件
     * @return Response
     */
    #[MenuNode(menu: false, parent: '/index', sort: -100)]
    public function upload() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 删除文件
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -90)]
    public function delete() : Response
    {
        SimpleForm::init()->batch($this->param('id/a', 'intval'), '请选择要删除的文件', function(int $id) {
            $this->model->remove($id);
        });
        
        $this->log()->record(self::LOG_DELETE, '删除文件');
        
        return $this->success('删除成功');
    }
    
    
    /**
     * 清理重复文件
     * @return Response
     * @throws DbException
     */
    #[MenuNode(menu: false, parent: '/index', sort: -80)]
    public function clear_repeat() : Response
    {
        $invalid = $this->model->clearInvalid();
        $repeat  = $this->model->clearRepeat();
        $this->log()->record(self::LOG_DELETE, '清理重复文件');
        
        return $this->success("成功清理{$invalid}个无效文件，{$repeat}个重复文件");
    }
    
    
    /**
     * 清理无效文件
     * @return Response
     * @throws DbException
     */
    #[MenuNode(menu: false, parent: '/index', sort: -70)]
    public function clear_invalid() : Response
    {
        $total = $this->model->clearInvalid();
        $this->log()->record(self::LOG_DELETE, '清理无效文件');
        
        return $this->success("成功清理{$total}个文件");
    }
}