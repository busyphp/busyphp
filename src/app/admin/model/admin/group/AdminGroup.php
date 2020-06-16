<?php

namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\model;
use BusyPHP\exception\SQLException;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\util\Arr;
use BusyPHP\helper\util\Transform;
use BusyPHP\app\admin\model\system\menu\SystemMenu;

/**
 * 用户组模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/30 下午5:56 下午 AdminGroup.php $
 */
class AdminGroup extends Model
{
    /**
     * 获取用户组权限
     * @param int $id
     * @return array
     * @throws SQLException
     */
    public function getInfo($id)
    {
        return parent::getInfo($id, '用户组权限不存在');
    }
    
    
    /**
     * 获取用户组缓存
     * @param int $groupId
     * @return array
     * @throws SQLException
     */
    public function getInfoByCache($groupId)
    {
        $groupList = $this->getList();
        if (!isset($groupList[$groupId]) || !$groupList[$groupId]) {
            throw new SQLException('用户组权限不存在', $this);
        }
        
        return $groupList[$groupId];
    }
    
    
    /**
     * 获取会员组缓存列表
     * @param bool $must
     * @return array
     */
    public function getList($must = false)
    {
        $list = $this->getCache('list');
        if (!$list || $must) {
            $list = $this->order('id ASC')->selecting();
            $list = self::parseList($list);
            $list = Arr::listByKey($list, 'id');
            $this->setCache('list', $list);
        }
        
        return $list;
    }
    
    
    /**
     * 添加用户组
     * @param AdminGroupField $insert
     * @return int
     * @throws SQLException
     */
    public function insertData($insert)
    {
        $groupId = $this->addData($insert);
        if (!$groupId) {
            throw new SQLException('添加失败', $this);
        }
        
        return $groupId;
    }
    
    
    /**
     * 修改用户组
     * @param AdminGroupField $update $id
     * @throws SQLException
     */
    public function updateData($update)
    {
        if (false === $result = $this->saveData($update)) {
            throw new SQLException('修改失败', $this);
        }
    }
    
    
    /**
     * 删除用户组
     * @param int $id
     * @throws VerifyException
     * @throws SQLException
     */
    public function del($id)
    {
        $info = $this->getInfo($id);
        if ($info['is_system']) {
            throw new VerifyException('系统管理权限组禁止删除');
        }
        
        parent::del($id, '删除管理权限组失败');
    }
    
    
    /**
     * 获取用户组选项
     * @param int         $selectedValue 当前选中值
     * @param bool|string $defaultText 默认选项名称 true或者不为空则输出选项
     * @param int         $defaultValue 模型选项值
     * @return string
     */
    public function getSelectOptions($selectedValue = 0, $defaultText = true, $defaultValue = 0)
    {
        $options = Transform::arrayToOption($this->order('id ASC')->selecting(), 'id', 'name', $selectedValue);
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
    
    
    /**
     * 解析数据列表
     * @param array $list
     * @return array
     */
    public static function parseList($list)
    {
        foreach ($list as $i => $r) {
            $r['is_system']  = Transform::dataToBool($r['is_system']);
            $r['rule_array'] = explode(',', $r['rule']);
            $r['rule_array'] = is_array($r['rule_array']) ? $r['rule_array'] : [];
            $list[$i]        = $r;
        }
        
        return parent::parseList($list);
    }
}