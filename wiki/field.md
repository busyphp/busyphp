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

由于`PHP8.0+`才支持注解，为了兼容`8.0`以下版本，目前仅支持使用标准注释块

### @var 字段类型声明

通过属性标准注释块中的 `@var` 声明，声明后获取或设置值将会得到期望的数据类型

注意：目前不支持联合类型

```php
/**
 * 字段名称
 * @var int
 */
public $id;
```

### @busy-field-validate 字段验证

在属性的标准注释块中声明 `@busy-field-validate`，支持多个，单个支持用`|`分割规则，支持自定义提示消息，请在规则后使用`#`号分割

使用 `\BusyPHP\Model::validate()` 进行自动校验

可以配合接口类 `\BusyPHP\interfaces\FieldObtainDataInterface`、`\BusyPHP\interfaces\FieldSetValueInterface` 辅助校验。

**自定义提示消息支持的变量**：
- `:attribute` 代表当前字段名称
- `:rule` 代表规则名称
- `:1` 范围中的第一个参数
- `:2` 范围中的第二个参数
- `:3` 范围中的第三个参数

```php
/**
 * 商品价格
 * @var float
 * @busy-field-validate require # :attribute不能为空
 * @busy-field-validate gt:0|lt:10
 */
public $price;
```

### @busy-field-name 字段名

开发过程中，由于系统升级等原因，可能会造成字段名称与属性名称不匹配，可以使用 `@busy-field-name` 对字段进行重命名，以保障升级迭代简洁、可靠。

重命名后，执行 `insert`、`update` 等操作将使用该名称。但使用 `json_encode` 或 `toArray` 时依旧使用原字段名

```php
/**
 * 商品价格
 * @var float
 * @busy-field-name amount
 */
public $price;
```

### @busy-field-filter 设置过滤

使用 `SET方法`、`Field::parse()`、`Field::copyData()` 时将对字段按照设定的回调方法进行过滤，支持英文逗号分割过滤回调

```php
/**
 * 商品名称
 * @var string
 * @busy-field-filter trim
 */
public $name;
```

### @busy-field-array 数组转字符串

`Model::insert()` `Model::update()` 时设置的值为数组，则按照该规则强制转换

**可选项**

- `json` - 转为JSON字符串
- `serialize` - 使用PHP`serialize`序列化
- `"合并分隔符" 左右是否填充分隔符` - 如：`"," true` 将转换为 `,1,2,3,`

```php
/**
 * 商品分类
 * @var string
 * @busy-field-array "," true
 */
public $cateIds;
```

### @busy-field-no-cast 不强制转换

不强制转换该属性的值，即设置什么得到什么，适用于 `@var`、`@busy-field-array`的声明

**可选项**

- `true` - 不强制转换
- `false` - 强制转换

```php
/**
 * 商品分类
 * @var string
 * @busy-field-no-cast
 */
public $cateIds;
```

### @busy-field-rename 输出重命名

对属性名进行重命名，使用 `Field::toArray()` 或 `json_encode()` 输出的键将为该名称

```php
/**
 * 商品分类
 * @var string
 * @busy-field-rename cate_list
 */
public $cateIds;
```

### @busy-field-ignore 忽略属性

使用 `Field::toArray()` `Field::obtain()` `Field::copyData()` 时被声明的属性将被丢弃

**可选项**

- `true` - 忽略
- `false` - 不忽略

```php
/**
 * 商品分类
 * @var string
 * @busy-field-ignore
 */
public $cateIds;
```

### @busy-field-plan-* 输出计划

用 `Field::plan()` 定制输出计划，对 `Field::toArray()`、`json_encode` 的输出结构将只输出被定制的字段，默认为`@busy-field-use-safe` 

**可选项**

`对字段进行重命名`

```php
/**
 * 商品分类
 * @var string
 * @busy-field-plan-safe
 */
public $id;

/**
 * 商品分类
 * @var string
 * @busy-field-plan-safe title
 * @busy-field-plan-test 
 */
public $name;

/**
 * 商品价格
 * @var string
 * @busy-field-rename amount
 * @busy-field-plan-test 
 */
public $price;

// 只输出safe标记的属性，name会被转为title
$info->plan()->toArray()

// 只输出safe标记的属性，price会被转为amount
$info->plan('test')->toArray()
```

## 声明信息结构类

通常继承自模型字段类

**示例**
```php
/**
 * 商品信息对象类
 */
class GoodsInfo extends GoodsField {
    /**
     * 对数据进行解析
     */
    protected function onParseAfter() {
        // TODO
    }
}
```