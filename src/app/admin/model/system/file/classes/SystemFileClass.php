<?php

namespace BusyPHP\app\admin\model\system\file\classes;

use BusyPHP\exception\VerifyException;
use BusyPHP\exception\SQLException;
use BusyPHP\model;
use BusyPHP\helper\util\Arr;
use BusyPHP\helper\util\Filter;
use BusyPHP\helper\util\Transform;
use BusyPHP\app\admin\model\system\file\SystemFile;

/**
 * 附件分类模型
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2017 busy^life <busy.life@qq.com>
 * @version $Id: 2017-06-06 下午2:14 FileClassModels.php busy^life $
 */
class SystemFileClass extends Model
{
    /**
     * 获取附件分类信息
     * @param int $id
     * @return array
     * @throws SQLException
     */
    public function getInfo($id)
    {
        return parent::getInfo(floatval($id), '附件分类不存在');
    }
    
    
    /**
     * 添加分类
     * @param SystemFileClassField $insert
     * @return int
     * @throws SQLException
     */
    public function insertData($insert)
    {
        if (false === $result = $this->addData($insert)) {
            throw new SQLException('添加分类失败', $this);
        }
        
        return $result;
    }
    
    
    /**
     * 修改分类
     * @param SystemFileClassField $update
     * @throws VerifyException
     * @throws SQLException
     */
    public function updateData($update)
    {
        if ($update->id < 1) {
            throw new VerifyException('缺少参数id', 'id');
        }
        
        if (false === $result = $this->saveData($update)) {
            throw new SQLException('修改分类失败', $this);
        }
    }
    
    
    /**
     * 删除分类
     * @param int $id
     * @return int
     * @throws SQLException
     * @throws VerifyException
     */
    public function del($id)
    {
        $info = $this->getInfo($id);
        if ($info['is_system']) {
            throw new VerifyException('系统分类组禁止删除');
        }
        
        return parent::del($id, '删除附件分类失败');
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
     * 解析数据
     * @param array $list
     * @return array
     */
    public static function parseList($list)
    {
        return parent::parseList($list, function($list) {
            foreach ($list as $i => $r) {
                // 是否系统分类
                $r['is_system'] = intval($r['is_system']) > 0;
                // 前端是否显示
                $r['home_show'] = intval($r['home_show']) > 0;
                // 前端允许上传
                $r['home_upload'] = intval($r['home_upload']) > 0;
                // 前端允许登录上传
                $r['home_login'] = intval($r['home_login']) > 0;
                // 后端显示
                $r['admin_show'] = intval($r['admin_show']) > 0;
                // KB
                $r['size'] = Filter::min(intval($r['size']), -1);
                // 允许后缀是否继承系统设置
                $r['suffix_is_inherit'] = $r['suffix'] <= -1;
                // 允许上传大小是否继承系统设置
                $r['size_is_inherit'] = $r['size'] <= -1;
                // 类型
                $r['type_name'] = SystemFile::getTypes($r['type']);
                // 是否附件
                $r['is_file'] = $r['type'] == SystemFile::FILE_TYPE_FILE;
                // 是否图片
                $r['is_image'] = $r['type'] == SystemFile::FILE_TYPE_IMAGE;
                // 是否视频
                $r['is_video'] = $r['type'] == SystemFile::FILE_TYPE_VIDEO;
                // 是否缩放图片
                $r['is_thumb'] = $r['is_image'] && intval($r['is_thumb']) > 0;
                // 是否加水印
                $r['watermark'] = $r['is_image'] && intval($r['watermark']) > 0;
                // 缩图后是否删除源文件
                $r['delete_source'] = $r['is_image'] && intval($r['delete_source']) > 0;
                
                $list[$i] = $r;
            }
            
            return $list;
        });
    }
    
    
    /**
     * 获取分类缓存
     * @param bool $must
     * @return array
     */
    public function getListByCache($must = false)
    {
        $list = $this->getCache('list');
        if (!$list || $must) {
            $list = $this->order('sort ASC,id DESC')->selecting();
            $list = self::parseList($list);
            $list = Arr::listByKey($list, 'var');
            $this->setCache('list', $list);
        }
        
        return $list;
    }
    
    
    /**
     * 通过key获取信息
     * @param $key
     * @return array
     * @throws VerifyException
     */
    public function getInfoByKey($key)
    {
        $list = SystemFileClass::init()->getListByCache();
        $list = Arr::listByKey($list, 'var');
        
        $info = isset($list[$key]) ? $list[$key] : [];
        if (!$info) {
            throw new VerifyException('附件类型[' . $key . ']不存在', $key);
        }
        
        return $info;
    }
    
    
    /**
     * 设置分类排序
     * @param int $id
     * @param int $value
     */
    public function setSort($id, $value)
    {
        $save       = SystemFileClassField::init();
        $save->sort = floatval($value);
        $this->one(floatval($id))->saveData($save);
    }
    
    
    protected function onChanged($method, $id, $options)
    {
        $this->getListByCache(true);
    }
}