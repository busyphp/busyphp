<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\file\classes;

use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\model;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\model\Entity;
use RuntimeException;
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
     * 删除分类
     * @param int $id
     * @return int
     * @throws Throwable
     */
    public function remove(int $id) : int
    {
        return $this->transaction(function() use ($id) {
            $info = $this->lock(true)->getInfo($id);
            if ($info->system) {
                throw new RuntimeException('系统分类禁止删除');
            }
            
            return $this->delete($info->id);
        });
    }
    
    
    /**
     * 获取后台可显示的附件分类
     * @param string|bool $selectedValue 选中项，设为true则返回数组
     * @param bool|string $defaultText 默认文本，false则不输出
     * @param string      $defaultValue 默认值
     * @param null        $type 指定附件类型
     * @return string|array<string, SystemFileClassField>
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
            $name     = SystemFile::class()::getTypes($type);
            $selected = '';
            if ($selectedValue == $value) {
                $selected = ' selected';
            }
            
            $options .= sprintf("<optgroup label=\"%s\">", $name);
            $options .= sprintf("<option value=\"%s\"%s>所有%s</option>", $value, $selected, $name);
            
            /** @var SystemFileClassField $item */
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