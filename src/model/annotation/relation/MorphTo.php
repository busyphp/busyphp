<?php
declare(strict_types = 1);
namespace BusyPHP\model\annotation\relation;

use Attribute;
use BusyPHP\helper\StringHelper;
use BusyPHP\Model;
use BusyPHP\model\annotation\relation\morph\MorphToRelation;
use BusyPHP\model\Entity;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 多态关联注解
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/5/29 10:11 MorphTo.php $
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class MorphTo extends Relation
{
    protected string|array $morph;
    
    /**
     * @var array<string,MorphToRelation>
     */
    protected array $relations;
    
    /**
     * @var string
     */
    protected string $typeKey;
    
    /**
     * @var string
     */
    protected string $foreignKey;
    
    
    /**
     * 构造函数
     * @param string|array                                                                                                                                                                                $morph 多态字段，支持2种方式定义: <p>
     * - 传入字符串：表示多态字段的前缀，多态字段使用 [prefix]_type 和 [prefix]_id <br />
     * - 传入数组：表示使用['多态类型字段名','多态ID字段名']
     * - 默认：默认为当前属性名作为多态字段的前缀
     * </p>
     * @param array<string,string>|array<string|int,array{type: ?mixed, model:class-string<Model>,localKey: ?string|callable, condition: ?string|array|callable, order: ?string|array}>|MorphToRelation[] $relations 多态类型对模型的关系
     */
    public function __construct(string|array $morph = '', array $relations = [])
    {
        $this->morph = $morph;
        
        $relationList = [];
        foreach ($relations as $type => $relation) {
            if ($relation instanceof MorphToRelation) {
                $relationList[$relation->getType()] = $relation;
            } elseif (is_array($relation)) {
                $trueType                = $relation['type'] ?? $type;
                $relationList[$trueType] = new MorphToRelation(
                    $trueType,
                    $relation['model'],
                    $relation['localKey'] ?? '',
                    $relation['condition'] ?? '',
                    $relation['order'] ?? 'id DESC'
                );
            } else {
                $relationList[$type] = new MorphToRelation($type, (string) $relation);
            }
        }
        
        $this->relations = $relationList;
    }
    
    
    protected function parseMorph()
    {
        if (!isset($this->typeKey) || !isset($this->foreignKey)) {
            if (!$this->morph) {
                $propertyName = StringHelper::snake($this->propertyName);
                $typeKey      = $propertyName . '_type';
                $foreignKey   = $propertyName . '_id';
            } elseif (is_array($this->morph)) {
                [$typeKey, $foreignKey] = $this->morph;
                if ($obj = Entity::tryCallable($typeKey)) {
                    $typeKey = (string) $obj;
                }
                if ($obj = Entity::tryCallable($foreignKey)) {
                    $foreignKey = (string) $obj;
                }
            } else {
                $typeKey    = $this->morph . '_type';
                $foreignKey = $this->morph . '_id';
            }
            
            $this->typeKey    = $typeKey;
            $this->foreignKey = $foreignKey;
        }
    }
    
    
    /**
     * @inheritDoc
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function handle(Model $model, array &$list)
    {
        $this->parseMorph();
        
        $typeMap = [];
        foreach ($list as $item) {
            $type             = $item[$this->typeKey];
            $typeId           = $item[$this->foreignKey];
            $typeMap[$type][] = $typeId;
        }
        
        $typeData = [];
        foreach ($typeMap as $type => $typeIds) {
            $relation = $this->relations[$type] ?? null;
            if (null === $relation) {
                continue;
            }
            
            $typeData[$type] = $relation->query($typeIds);
        }
        
        foreach ($list as &$item) {
            $type   = $item[$this->typeKey];
            $typeId = $item[$this->foreignKey];
            
            $item[$this->propertyName] = $typeData[$type][$typeId] ?? null;
        }
    }
}
