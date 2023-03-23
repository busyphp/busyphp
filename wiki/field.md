# 模型字段类

## 声明字段结构类

字段结构类通常用来表示该数据表的 `字段名称`、`字段类型`、`SET字段方法`、`字段实体`，以及`私有属性`。以进行 `insert`、`update` 等操作

注意：属性名称强烈建议按照 `驼峰` 方式命名

**必须继承类**

```php 
\BusyPHP\modal\Field
```

`字段实体/SET字段方法` 请通过类标准注释块声明，以符合IDE提示、便于后期维护。可通过辅助类打印后复制到类注释块中。

**示例**

```php
// 通过辅助方法打印实体和Set方法复制到类注释块中
\BusyPHP\helper\ModelHelper::printField(Goods::class);

/**
 * 商品模型字段
 * @method static \BusyPHP\model\Entity id(mixed $op = null, mixed $condition = null) 商品ID
 * @method static \BusyPHP\model\Entity name(mixed $op = null, mixed $condition = null) 商品名称
 * @method static \BusyPHP\model\Entity price(mixed $op = null, mixed $condition = null) 商品价格
 * @method $this setId(mixed $id) 设置商品ID
 * @method $this setName(mixed $name) 设置商品名称
 * @method $this setPrice(mixed $price) 设置商品价格
 */
class GoodsField extends \BusyPHP\model\Field {
    /**
     * 商品ID
     * @var int 
     */
    public $id;
    
    /**
     * 商品名称
     * @var string 
     */
    public $name;
    
    /**
     * 商品价格
     * @var float 
     */
    public $price;
}
```

## 注解实现

### 一、类`class`支持的注解

#### 1. #[BindModel] 给字段结构类绑定模型注解

```php
#[\BusyPHP\model\annotation\field\BindModel(
    model: \test\AdminGroup::class, // 指定该字段结构类所属的模型类
    alias: '' // 指定模型别名，用于 join 查询的时候自动加别名
)]
class TestField extends \BusyPHP\model\Field {

}
```

#### 2. #[AutoTimestamp] 定义模型自动写入创建/更新时间注解

```php
#[\BusyPHP\model\annotation\field\AutoTimestamp(
    type: \BusyPHP\model\annotation\field\AutoTimestamp::TYPE_INT, // 定义自动写时间的字段类型，true为自动获取，支持：int, date, timestamp, datetime
    format: 'Y-m-d H:i:s' // 定义字段输出格式
)]
class TestField extends \BusyPHP\model\Field {

}
```

#### 3. #[SoftDelete] 定义模型启用软删除注解

```php
#[\BusyPHP\model\annotation\field\SoftDelete(
    default: 0 // 定义删除字段的默认值
)]
class TestField extends \BusyPHP\model\Field {

}
```

#### 4. #[ToArrayFormat] 定义适用于to*方法的索引键输出格式注解

```php
#[\BusyPHP\model\annotation\field\ToArrayFormat(
    type: \BusyPHP\model\annotation\field\ToArrayFormat::TYPE_SNAKE
)]
class TestField extends \BusyPHP\model\Field {

}
```

### 二、类属性支持的注解

#### 1. #[Column] 定义字段的属性注解

字段类型通过属性标准注释块中的 `@var` 声明，声明后获取或设置值将会得到期望的数据类型，注意：目前不支持联合类型

```php
class TestField extends \BusyPHP\model\Field {
    /**
     * 商品属性
     * @var int
     */
    #[\BusyPHP\model\annotation\field\Column(
        field: '', // 真实的字段名称，不设置取属性名称并转换为小写+下划线风格
        primary: true, // 是否主键字段
        type: \BusyPHP\model\annotation\field\Column::TYPE_INT, // 字段真实类型
        title: '', // 字段名称，不设置自动获取标注注释块中的说明
        readonly: true, // 该字段是否只读，只能新增不能修改
        feature: \BusyPHP\model\annotation\field\Column::FEATURE_CREATE_TIME, // 设置字段特性以配合 #[AutoTimestamp] #[SoftDelete] 注解的功能
    )] 
    public $attr;
}
```

#### 2. #[Validator] 定义数据校验注解

使用 `\BusyPHP\Model::validate()` 进行自动校验

```php
\BusyPHP\Model::validate()
```

可以配合接口类 `\BusyPHP\interfaces\FieldGetModelDataInterface`、`\BusyPHP\interfaces\FieldSetValueInterface`
、`\BusyPHP\interfaces\ModelValidateInterface` 辅助校验。

```php
class TestField extends \BusyPHP\model\Field implements \BusyPHP\interfaces\FieldGetModelDataInterface, \BusyPHP\interfaces\FieldSetValueInterface, \BusyPHP\interfaces\ModelValidateInterface {
    /**
     * 商品属性
     * @var int
     */
    #[\BusyPHP\model\annotation\field\Validator(
        name: \BusyPHP\model\annotation\field\Validator::REQUIRE,
        msg: '请输入:attribute' 
    )] 
    #[\BusyPHP\model\annotation\field\Validator(
        name: \BusyPHP\model\annotation\field\Validator::MIN,
        rule: 1,
        msg: ':attribute不能小于:1' 
    )] 
    #[\BusyPHP\model\annotation\field\Validator(
        name: \BusyPHP\model\annotation\field\Validator::MAX,
        rule: 100,
        msg: ':attribute不能大于:1' 
    )] 
    public $attr;
}
```

**参数`msg`支持的变量**：

- `:attribute` 代表当前字段名称
- `:rule` 代表规则名称
- `:1` 范围中的第一个参数
- `:2` 范围中的第二个参数
- `:3` 范围中的第三个参数

#### 3. #[Ignore] 将属性标记为非字段注解

开发过程中，除了字段还需要很多自定义属性来补充数据，因此需要我们标记某个属性为非字段。

```php
class TestField extends \BusyPHP\model\Field {
    /**
     * 商品属性
     * @var int
     */
    public $attr;
    
    /**
     * 自定义属性
     * @var string
     */
    #[\BusyPHP\model\annotation\field\Ignore] 
    public $name;
}
```

#### 4. #[Separate] 字符串自动分割注解

使用 `setter方法`、`Field::parse()` 时有效

```php
class TestField extends \BusyPHP\model\Field {
    /**
     * 商品属性
     * @var array
     */
    #[\BusyPHP\model\annotation\field\Separate(
        separator: ',', // 将字符串切割为数组的分隔符
        full: true, // 写入数据库的时候左右是否保留分隔符
        unique: true, // 是否去重
        filter: true, // 数据过滤，支持回调过滤，默认使用 array_filter 过滤
    )] 
    public $attr;
}

TestField::init()->setAttr('1,2,3,4') // attr = [1,2,3,4]
TestField::init()->setAttr([1,2,3,4]) // attr = [1,2,3,4]
```

#### 5. #[Json] 自动JSON数据注解

使用 `setter方法`、`Field::parse()` 时有效

```php
class TestField extends \BusyPHP\model\Field {
    /**
     * 商品属性
     * @var array
     */
    #[\BusyPHP\model\annotation\field\Json(
        default: '[]', // 默认值
        flags: JSON_UNESCAPED_UNICODE
    )] 
    public $attr;
}

TestField::init()->setAttr('[1,2,3,4]') // attr = [1,2,3,4]
TestField::init()->setAttr([1,2,3,4]) // attr = [1,2,3,4]
```

#### 6. #[Serialize] 自动序列化数据注解

使用 `setter方法`、`Field::parse()` 时有效

```php
class TestField extends \BusyPHP\model\Field {
    /**
     * 商品属性
     * @var array
     */
    #[\BusyPHP\model\annotation\field\Serialize] 
    public $attr;
}
```

#### 7. #[Filter] 数据过滤注解

使用 `setter方法`、`Field::parse()` 时有效

```php
class TestField extends \BusyPHP\model\Field {
    /**
     * 商品属性
     * @var string
     */
    #[\BusyPHP\model\annotation\field\Filter(
        filter: 'trim', // 过滤方法，支持回调
        args: '过滤方法参数1',
        '过滤方法参数2...'
    )] 
    public $attr;
}
```

#### 8. #[ValueBindField] 绑定指定字段值注解

使用 `setter方法`、`Field::parse()` 时有效

下面例子中为 attr 赋值后，formatAttr 将自动填充 attr 的值，配合 #[Filter] 注解，可以快速实现自定义属性值的呈现

```php
class TestField extends \BusyPHP\model\Field {
    /**
     * 商品属性
     * @var int
     */
    public $attr;
    
    /**
     * 格式化的商品属性 
     * @var string 
     */
    #[\BusyPHP\model\annotation\field\Ignore]
    #[\BusyPHP\model\annotation\field\ValueBindField(
        field: [self::class, 'attr'], // 定义绑定的字段，支持字符串及回调
    )] 
    #[\BusyPHP\model\annotation\field\Filter(
        filter: [self::class, 'filterFormatAttr']
    )]
    public $formatAttr;
    
    /**
     * @param int $value
     * @return string
     */
    public static function filterFormatAttr(int $value) {
        $map = [1 => '白色', 2 => '黑色'];
        
        return $map[$value];
    }
}
```

#### 9. #[ToArrayRename] 定义适用于to*方法对索引键重命名注解

```php
class TestField extends \BusyPHP\model\Field {
    /**
     * 商品属性
     * @var string
     */
    #[\BusyPHP\model\annotation\field\ToArrayRename(
        name: 'attr1', // 重命名名称
        scene: 'api1' // 输出场景名称
    )] 
    public $attr;
}
```

#### 10. #[ToArrayHidden] 定义适用于to*方法对索引键隐藏注解

```php
class TestField extends \BusyPHP\model\Field {
    /**
     * 商品属性
     * @var string
     */
    #[\BusyPHP\model\annotation\field\ToArrayHidden(
        scene: 'api1' // 输出场景名称
    )] 
    public $attr;
}
```