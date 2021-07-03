BusyPHP使用说明
===============

`BusyPHP` 框架基于 `ThinkPHP6.0` 进行的开发，所以 `ThinkPHP6.0` 中的所有内置方都可以继续使用，具体请参考 [官方手册](https://www.kancloud.cn/manual/thinkphp6_0/1037479) 

除了使用 `ThinkPHP6.0` 内置规则外除了特殊情况，必须遵守本使用说明文档中的条例

## 模型

> 定义模型需要继承 `BusyPHP\Model` 
> 模型名称就是数据表名称，请使用驼峰命名方式创建到 `core/model` 目录下

### 说明
~~~php
<?php
namespace core\model;
use BusyPHP\exception\AppException;use BusyPHP\Model;

// Test就是数据表名称，如数据表命名为 busy_test
class Test extends Model {
    // 查找单条信息
    public function getInfo($id) {
        return parent::getInfo($id, '这里申明什么信息不存在，如新闻不存在');
    }
    
    // 基本信息解析，一般用来解析状态、时间等字段
    public static function parseList($list){
        return parent::parseList($list, function($list) {
            foreach ($list as $i => $r) {
                // 这里可以扩展数据
                $r['test'] = 1;
                $list[$i] = $r;
            }
            
            return $list;
        });
    }
    
    // 扩展信息解析，一般用来解析外键关联的数据
    public static function parseExtendList($list,$isOnly = false){
        return parent::parseExtendList($list,$isOnly, function($list) {
            foreach ($list as $i => $r) {
                // 这里可以扩展数据
                $r['test'] = 1;
                $list[$i] = $r;
            }
            
            return $list;      
        });
    }

    // 单条信息删除
    public function del($id){
        $this->startTrans();
        try {
            if (!$this->lock(true)->findData($id)) {
                throw new AppException('信息不存在');
            }           

            // 通过$id删除其他关联数据
            // ...            

            parent::deleteInfo($id);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            
            throw $e;
        }
    }
    
    // 发生增加、修改、删除的时候触发
    // 一般用在缓存管理的时候
    public function onChanged(string $method,$id,array $options){
        switch ($method) {
            // 新增触发
            case Model::CHANGED_INSERT:
            break;
    
            // 更新触发
            case Model::CHANGED_UPDATE:
            break;
    
            // 删除触发
            case Model::CHANGED_DELETE:
            break;
        }
    }
    
    // 数据发生改变的时候触发，增加或修改
    // 一般用在缓存管理的时候
    public function onAfterWrite($id,array $options){
        
    }
}
~~~