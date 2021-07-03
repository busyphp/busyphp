<?php

namespace BusyPHP\app\admin\model\system\file\classes;

use BusyPHP\exception\ParamInvalidException;
use BusyPHP\exception\VerifyException;
use BusyPHP\model;
use BusyPHP\helper\util\Arr;
use BusyPHP\helper\util\Transform;
use BusyPHP\app\admin\model\system\file\SystemFile;
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
    protected $dataNotFoundMessage = '附件分类不存在';
    
    protected $findInfoFilter      = 'intval';
    
    protected $bindParseClass      = SystemFileClassInfo::class;
    
    
    /**
     * 添加分类
     * @param SystemFileClassField $insert
     * @return int
     * @throws DbException
     */
    public function insertData($insert)
    {
        return $this->addData($insert);
    }
    
    
    /**
     * 修改分类
     * @param SystemFileClassField $update
     * @throws DbException
     * @throws ParamInvalidException
     */
    public function updateData($update)
    {
        if ($update->id < 1) {
            throw new ParamInvalidException('id');
        }
        
        $this->saveData($update);
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
        if ($info->isSystem) {
            throw new VerifyException('系统分类组禁止删除');
        }
        
        return parent::deleteInfo($info->id);
    }
    
    
    /**
     * 获取后台可显示的附件分类
     * @param int    $selectedValue
     * @param bool   $defaultText
     * @param int    $defaultValue
     * @param string $type 指定附件类型
     * @return string
     */
    public function getAdminOptions($selectedValue = 0, $defaultText = true, $defaultValue = 0, $type = null)
    {
        $list  = $this->getListByCache();
        $array = [];
        foreach ($list as $i => $r) {
            if (!$r['admin_show'] || ($type && $r['type'] != $type)) {
                continue;
            }
            $array[] = $r;
        }
        
        
        $options = Transform::arrayToOption($array, 'var', 'name', $selectedValue);
        if ($defaultText) {
            if (true === $defaultText) {
                $defaultText = '请选择';
            }
            $options = '<option value="' . $defaultValue . '">' . $defaultText . '</option>' . $options;
        }
        
        return $options;
    }
    
    
    /**
     * 获取后台可显示的图片分类
     * @param int  $selectedValue
     * @param bool $defaultText
     * @param int  $defaultValue
     * @return string
     */
    public function getAdminImageOptions($selectedValue = 0, $defaultText = true, $defaultValue = 0)
    {
        return $this->getAdminOptions($selectedValue, $defaultText, $defaultValue, SystemFile::FILE_TYPE_IMAGE);
    }
    
    
    /**
     * 获取后台可显示的附件分类
     * @param int  $selectedValue
     * @param bool $defaultText
     * @param int  $defaultValue
     * @return string
     */
    public function getAdminFileOptions($selectedValue = 0, $defaultText = true, $defaultValue = 0)
    {
        return $this->getAdminOptions($selectedValue, $defaultText, $defaultValue, SystemFile::FILE_TYPE_FILE);
    }
    
    
    /**
     * 获取后台可显示的视频分类
     * @param int  $selectedValue
     * @param bool $defaultText
     * @param int  $defaultValue
     * @return string
     */
    public function getAdminVideoOptions($selectedValue = 0, $defaultText = true, $defaultValue = 0)
    {
        return $this->getAdminOptions($selectedValue, $defaultText, $defaultValue, SystemFile::FILE_TYPE_VIDEO);
    }
    
    
    /**
     * 获取分类缓存
     * @param bool $must
     * @return SystemFileClassInfo[]
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function getListByCache($must = false)
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
     * 通过key获取信息
     * @param $key
     * @return SystemFileClassInfo
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getInfoByKey($key)
    {
        $list = $this->getListByCache();
        $info = $list[$key] ?? null;
        if (!$info) {
            throw new DataNotFoundException('附件类型[' . $key . ']不存在');
        }
        
        return $info;
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
        $this->getListByCache(true);
    }
}