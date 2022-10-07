<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\file\classes;

use BusyPHP\model;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\model\Entity;
use RuntimeException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use Throwable;

/**
 * 附件分类模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午4:56 SystemFileClass.php $
 * @method SystemFileClassInfo getInfo(int $id, string $notFoundMessage = null)
 * @method SystemFileClassInfo|null findInfo(int $id = null, string $notFoundMessage = null)
 * @method SystemFileClassInfo[] selectList()
 * @method SystemFileClassInfo[] buildListWithField(array $values, string|Entity $key = null, string|Entity $field = null)
 * @method static string|SystemFileClass getClass()
 */
class SystemFileClass extends Model
{
    protected $dataNotFoundMessage = '文件分类不存在';
    
    protected $findInfoFilter      = 'intval';
    
    protected $bindParseClass      = SystemFileClassInfo::class;
    
    /** @var string 操作场景-用户设置 */
    public const SCENE_USER_SET = 'user_set';
    
    
    /**
     * @inheritDoc
     */
    final protected static function defineClass() : string
    {
        return self::class;
    }
    
    
    /**
     * 添加分类
     * @param SystemFileClassField $data
     * @return int
     * @throws DbException
     */
    public function createInfo(SystemFileClassField $data) : int
    {
        return (int) $this->validate($data, self::SCENE_CREATE)->addData();
    }
    
    
    /**
     * 修改分类
     * @param SystemFileClassField $data
     * @param string               $scene
     * @throws Throwable
     */
    public function updateInfo(SystemFileClassField $data, string $scene = self::SCENE_UPDATE)
    {
        $this->transaction(function() use ($data, $scene) {
            $info = $this->lock(true)->getInfo($data->id);
            $this->validate($data, $scene, $info)->saveData();
        });
    }
    
    
    /**
     * 删除分类
     * @param int $data
     * @return int
     * @throws Throwable
     */
    public function deleteInfo($data) : int
    {
        $id = (int) $data;
        
        return $this->transaction(function() use ($id) {
            $info = $this->getInfo($id);
            if ($info->system) {
                throw new RuntimeException('系统分类禁止删除');
            }
            
            return parent::deleteInfo($info->id);
        });
    }
    
    
    /**
     * 获取后台可显示的附件分类
     * @param string|bool $selectedValue 选中项，设为true则返回数组
     * @param bool|string $defaultText 默认文本，false则不输出
     * @param string      $defaultValue 默认值
     * @param string      $type 指定附件类型
     * @return string|SystemFileClassInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getAdminOptions($selectedValue = '', $defaultText = true, $defaultValue = '', $type = null)
    {
        $list  = $this->getList();
        $array = [];
        foreach ($list as $item) {
            if ($type && $item->type != $type) {
                continue;
            }
            
            $array[$item->type][] = $item;
        }
        
        if ($selectedValue === true) {
            return $array;
        }
        
        $options = '';
        if ($defaultText) {
            if (true === $defaultText) {
                $defaultText = '请选择分类';
            }
            
            $options = sprintf("<option value=\"%s\">%s</option>", $defaultValue, $defaultText);
        }
        foreach ($array as $type => $list) {
            $value    = sprintf("type:%s", $type);
            $name     = SystemFile::getClass()::getTypes($type);
            $selected = '';
            if ($selectedValue == $value) {
                $selected = ' selected';
            }
            
            $options .= sprintf("<optgroup label=\"%s\">", $name);
            $options .= sprintf("<option value=\"%s\"%s>所有%s</option>", $value, $selected, $name);
            
            /** @var SystemFileClassInfo $item */
            foreach ($list as $item) {
                $itemSelected = '';
                if ($item->var == $selectedValue) {
                    $itemSelected = ' selected';
                }
                $options .= sprintf("<option value=\"%s\"%s>%s</option>", $item->var, $itemSelected, $item->name);
            }
            
            $options .= '</optgroup>';
        }
        
        return $options;
    }
    
    
    /**
     * 获取分类缓存
     * @param bool $must
     * @return SystemFileClassInfo[]
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function getList($must = false) : array
    {
        $list = $this->getCache('list');
        if (!$list || $must) {
            $list = $this->order(SystemFileClassField::sort(), 'asc')
                ->order(SystemFileClassField::id(), 'desc')
                ->selectList();
            $list = ArrayHelper::listByKey($list, SystemFileClassField::var()->name());
            $this->setCache('list', $list);
        }
        
        return $list;
    }
    
    
    /**
     * 设置分类排序
     * @param array $data
     * @throws DbException
     */
    public function setSort(array $data)
    {
        $saveAll = [];
        foreach ($data as $id => $value) {
            $item       = SystemFileClassField::init();
            $item->id   = $id;
            $item->sort = $value;
            $saveAll[]  = $item;
        }
        
        if ($saveAll) {
            $this->saveAll($saveAll);
        }
    }
    
    
    /**
     * @inheritDoc
     * @throws
     */
    protected function onChanged($method, $id, $options)
    {
        $this->getList(true);
    }
    
    
    /**
     * @inheritDoc
     * @throws
     */
    public function onSaveAll()
    {
        $this->getList(true);
    }
}