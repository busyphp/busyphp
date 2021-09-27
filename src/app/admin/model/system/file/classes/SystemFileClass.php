<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\file\classes;

use BusyPHP\exception\ParamInvalidException;
use BusyPHP\exception\VerifyException;
use BusyPHP\model;
use BusyPHP\helper\util\Arr;
use BusyPHP\app\admin\model\system\file\SystemFile;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 附件分类模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午4:56 SystemFileClass.php $
 * @method SystemFileClassInfo findInfo($data = null, $notFoundMessage = null)
 * @method SystemFileClassInfo getInfo($data, $notFoundMessage = null)
 * @method SystemFileClassInfo[] selectList()
 */
class SystemFileClass extends Model
{
    protected $dataNotFoundMessage = '文件分类不存在';
    
    protected $findInfoFilter      = 'intval';
    
    protected $bindParseClass      = SystemFileClassInfo::class;
    
    
    /**
     * 添加分类
     * @param SystemFileClassField $insert
     * @return int
     * @throws DbException
     * @throws VerifyException
     */
    public function insertData($insert)
    {
        $this->checkRepeat($insert->var);
        
        return $this->addData($insert);
    }
    
    
    /**
     * 修改分类
     * @param SystemFileClassField $update
     * @throws ParamInvalidException
     * @throws Exception
     */
    public function updateData($update)
    {
        if ($update->id < 1) {
            throw new ParamInvalidException('id');
        }
        
        $this->startTrans();
        try {
            $info = $this->lock(true)->getInfo($update->id);
            if ($info->system) {
                $update->system = null;
                $update->var    = $info->var;
                $update->type   = $info->type;
            }
            
            $this->checkRepeat($update->var, $update->id);
            $this->saveData($update);
            
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            
            throw $e;
        }
    }
    
    
    /**
     * 查重
     * @param string $var
     * @param int    $id
     * @throws VerifyException
     */
    protected function checkRepeat($var, $id = 0)
    {
        $this->whereEntity(SystemFileClassField::var($var));
        if ($id > 0) {
            $this->whereEntity(SystemFileClassField::id('<>', $id));
        }
        
        if ($this->count() > 0) {
            throw new VerifyException('该文件分类标识已存在', 'var');
        }
    }
    
    
    /**
     * 删除分类
     * @param int $data
     * @return int
     * @throws VerifyException
     * @throws DbException
     */
    public function deleteInfo($data) : int
    {
        $info = $this->getInfo($data);
        if ($info->system) {
            throw new VerifyException('系统分类禁止删除');
        }
        
        return parent::deleteInfo($info->id);
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
            
            $options = '<option value="' . $defaultValue . '">' . $defaultText . '</option>';
        }
        foreach ($array as $type => $list) {
            $value    = "type:{$type}";
            $name     = SystemFile::getTypes($type);
            $selected = '';
            if ($selectedValue == $value) {
                $selected = ' selected';
            }
            
            $options .= '<optgroup label="' . $name . '">';
            $options .= '<option value="' . $value . '"' . $selected . '>所有' . $name . '</option>';
            
            /** @var SystemFileClassInfo $item */
            foreach ($list as $item) {
                $itemSelected = '';
                if ($item->var == $selectedValue) {
                    $itemSelected = ' selected';
                }
                $options .= '<option value="' . $item->var . '"' . $itemSelected . '>' . $item->name . '</option>';
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
    public function getList($must = false)
    {
        $list = $this->getCache('list');
        if (!$list || $must) {
            $list = $this->order(SystemFileClassField::sort(), 'asc')
                ->order(SystemFileClassField::id(), 'desc')
                ->selectList();
            $list = Arr::listByKey($list, SystemFileClassField::var());
            $this->setCache('list', $list);
        }
        
        return $list;
    }
    
    
    /**
     * 设置分类排序
     * @param int $id
     * @param int $value
     * @throws DbException
     */
    public function setSort($id, $value)
    {
        $this->whereEntity(SystemFileClassField::id($id))->setField(SystemFileClassField::sort(), $value);
    }
    
    
    /**
     * @param string $method
     * @param        $id
     * @param array  $options
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function onChanged($method, $id, $options)
    {
        $this->getList(true);
    }
}