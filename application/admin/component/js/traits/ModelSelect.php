<?php

namespace BusyPHP\app\admin\component\js\traits;

use BusyPHP\Model;
use BusyPHP\model\Field;
use BusyPHP\Request;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 模型是否查询扩展信息特征类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/14 08:36 ModelExtend.php $
 * @property Request $request
 * @property Model   $model
 */
trait ModelSelect
{
    /**
     * @var bool
     */
    private $extend;
    
    
    /**
     * 设置是否查询扩展数据
     * @param bool $extend
     * @return $this
     */
    public function setExtend(bool $extend)
    {
        $this->extend = $extend;
        
        return $this;
    }
    
    
    /**
     * 是否查询扩展数据
     * @return bool
     */
    public function isExtend() : bool
    {
        if (is_null($this->extend)) {
            $this->extend = $this->request->param('extend/b', false);
        }
        
        return $this->extend;
    }
    
    
    /**
     * 执行查询
     * @return Field[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function modelSelect()
    {
        if ($this->isExtend()) {
            return $this->model->selectExtendList();
        } else {
            return $this->model->selectList();
        }
    }
}