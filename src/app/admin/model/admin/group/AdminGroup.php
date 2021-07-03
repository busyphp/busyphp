<?php

namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\exception\ParamInvalidException;
use BusyPHP\model;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\util\Arr;
use BusyPHP\helper\util\Transform;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 用户组模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/30 下午5:56 下午 AdminGroup.php $
 * @method AdminGroupInfo findInfo($data = null, $notFoundMessage = null)
 * @method AdminGroupInfo getInfo($data, $notFoundMessage = null)
 * @method AdminGroupInfo[] selectList()
 */
class AdminGroup extends Model
{
    protected $dataNotFoundMessage = '用户组权限不存在';
    
    protected $findInfoFilter      = 'intval';
    
    protected $bindParseClass      = AdminGroupInfo::class;
    
    
    /**
     * 获取用户组缓存
     * @param int $id
     * @return AdminGroupInfo
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getInfoByCache($id)
    {
        $groupList = $this->getList();
        if ($info = $groupList[$id] ?? false) {
            throw new DataNotFoundException($this->dataNotFoundMessage);
        }
        
        return $info;
    }
    
    
    /**
     * 获取会员组缓存列表
     * @param bool $must
     * @return AdminGroupInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getList($must = false)
    {
        $list = $this->getCache('list');
        if (!$list || $must) {
            $list = $this->order(AdminGroupField::id(), 'asc')->selectList();
            $list = Arr::listByKey($list, AdminGroupField::id()->field());
            $this->setCache('list', $list);
        }
        
        return $list;
    }
    
    
    /**
     * 添加用户组
     * @param AdminGroupField $insert
     * @return int
     * @throws DbException
     */
    public function insertData($insert)
    {
        return $this->addData($insert);
    }
    
    
    /**
     * 修改用户组
     * @param AdminGroupField $update $id
     * @throws ParamInvalidException
     * @throws DbException
     */
    public function updateData($update)
    {
        if ($update->id < 1) {
            throw new ParamInvalidException('id');
        }
        
        $this->whereEntity(AdminGroupField::id($update->id))->saveData($update);
    }
    
    
    /**
     * 删除用户组
     * @param int $data
     * @return int
     * @throws DataNotFoundException
     * @throws DbException
     * @throws VerifyException
     */
    public function deleteInfo($data) : int
    {
        $info = $this->getInfo($data);
        if ($info->isSystem) {
            throw new VerifyException('系统管理权限组禁止删除');
        }
        
        return parent::deleteInfo($data);
    }
    
    
    /**
     * 获取用户组选项
     * @param int         $selectedValue 当前选中值
     * @param bool|string $defaultText 默认选项名称 true或者不为空则输出选项
     * @param int         $defaultValue 模型选项值
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getSelectOptions($selectedValue = 0, $defaultText = true, $defaultValue = 0)
    {
        $options = Transform::arrayToOption($this->getList(), AdminGroupField::id(), AdminGroupField::name(), $selectedValue);
        if ($defaultText) {
            if (true === $defaultText) {
                $defaultText = '请选择';
            }
            $options = '<option value="' . $defaultValue . '">' . $defaultText . '</option>' . $options;
        }
        
        return $options;
    }
    
    
    /**
     * 更新缓存
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function updateCache()
    {
        $this->clearCache();
        $list      = $this->getList(true);
        $menuModel = SystemMenu::init();
        foreach ($list as $id => $r) {
            $menuModel->getAdminMenu($r['id'], true);
            $menuModel->getAdminNav($r['id'], true);
        }
    }
}