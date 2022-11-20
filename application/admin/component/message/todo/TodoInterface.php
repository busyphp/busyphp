<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\message\todo;

/**
 * 待办接口类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/19 17:12 TodoInterface.php $
 */
interface TodoInterface
{
    /**
     * 获取待办数
     * @param TodoTotalParameter $parameter
     * @return int
     */
    public function getAdminTodoTotal(TodoTotalParameter $parameter) : int;
    
    
    /**
     * 获取待办数据
     * @param TodoListParameter $parameter
     * @return TodoNode[]
     */
    public function getAdminTodoList(TodoListParameter $parameter) : array;
    
    
    /**
     * 点击待办反馈
     * @param TodoReadParameter $parameter
     */
    public function setAdminTodoRead(TodoReadParameter $parameter);
}