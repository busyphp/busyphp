<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\file\classes;

use BusyPHP\app\admin\setting\StorageSetting;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\Model;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\model\Entity;
use think\db\exception\DbException;
use Throwable;

/**
 * 附件分类模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午4:56 SystemFileClass.php $
 * @method SystemFileClassField getInfo(int $id, string $notFoundMessage = null)
 * @method SystemFileClassField|null findInfo(int $id = null)
 * @method SystemFileClassField[] selectList()
 * @method SystemFileClassField[] indexList(string|Entity $key = '')
 * @method SystemFileClassField[] indexListIn(array $range, string|Entity $key = '', string|Entity $field = '')
 */
class SystemFileClass extends Model implements ContainerInterface
{
    protected string $dataNotFoundMessage = '文件分类不存在';
    
    protected string $fieldClass          = SystemFileClassField::class;
    
    /** @var string 操作场景-用户设置 */
    public const SCENE_USER_SET = 'user_set';
    
    /** @var array 保护的关键词 */
    public const PROTECT_VAR = ['system', 'file', 'image', 'video', 'audio'];
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * 添加分类
     * @param SystemFileClassField $data
     * @return int
     * @throws DbException
     */
    public function create(SystemFileClassField $data) : int
    {
        return (int) $this->validate($data, static::SCENE_CREATE)->insert();
    }
    
    
    /**
     * 修改分类
     * @param SystemFileClassField $data
     * @param string               $scene
     * @throws Throwable
     */
    public function modify(SystemFileClassField $data, string $scene = self::SCENE_UPDATE)
    {
        $this->transaction(function() use ($data, $scene) {
            $info = $this->lock(true)->getInfo($data->id);
            $this->validate($data, $scene, $info)->update();
        });
    }
    
    
    /**
     * 获取分类缓存
     * @param bool $force
     * @return array<string, SystemFileClassField>
     */
    public function getList(bool $force = false) : array
    {
        return $this->rememberCacheByCallback('list', function() {
            $list = $this->order(SystemFileClassField::sort(), 'asc')
                ->order(SystemFileClassField::id(), 'desc')
                ->selectList();
            
            return ArrayHelper::listByKey($list, SystemFileClassField::var()->name());
        }, $force);
    }
    
    
    /**
     * 获取上传分类
     * @return array[]
     */
    public function getUploadCategory() : array
    {
        $setting = StorageSetting::instance();
        $list    = [
            '' => [
                'max_size'         => $setting->getMaxSize(),
                'allow_extensions' => $setting->getAllowExtensions(),
                'allow_mimetypes'  => $setting->getMimeType(),
                'type'             => '',
                'name'             => ''
            ]
        ];
        foreach ($this->getList() as $type => $item) {
            $list[$type] = [
                'max_size'         => $setting->getMaxSize($type),
                'allow_extensions' => $setting->getAllowExtensions($type),
                'allow_mimetypes'  => $setting->getMimeType($type),
                'type'             => $item->type,
                'name'             => $item->name
            ];
        }
        
        return $list;
    }
    
    
    /**
     * @inheritDoc
     */
    protected function onChanged($method, $id, $options)
    {
        $this->getList(true);
    }
    
    
    /**
     * @inheritDoc
     */
    public function onUpdateAll()
    {
        $this->getList(true);
    }
}