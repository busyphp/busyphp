<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\import;

use BusyPHP\helper\ClassHelper;
use BusyPHP\model\annotation\field\Column as FieldColumn;
use BusyPHP\model\Entity;
use BusyPHP\office\excel\export\ExportColumn as ExportColumn;
use BusyPHP\office\excel\import\parameter\ImportFilterParameter;
use Closure;

/**
 * 导入列配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/7 16:44 ImportColumn.php $
 */
class ImportColumn
{
    /** @var string 剔除左右内容 */
    public const FILTER_TRIM = ':trim';
    
    /** @var string 切分数组 */
    public const FILTER_SPLIT = ':split';
    
    /** @var string 将EXCEL时间转为时间戳 */
    public const FILTER_TIMESTAMP = ':timestamp';
    
    /** @var string 将EXCEL时间转为日期字符 */
    public const FILTER_DATE = ':date';
    
    /** @var string 转为INT */
    public const FILTER_INT = ':int';
    
    /** @var string 转为float */
    public const FILTER_FLOAT = ':float';
    
    /** @var string 转为bool */
    public const FILTER_BOOL = ':bool';
    
    /**
     * 字母
     * @var string
     */
    protected string $letter;
    
    /**
     * 字段名称
     * @var string|Entity
     */
    protected string|Entity $field;
    
    /**
     * 数据过滤
     * @var callable|string|null
     */
    protected mixed $filter;
    
    /**
     * 剔除的左右字符串
     * @var string[]
     */
    protected array $trimCharacters = [];
    
    /**
     * 切分数组时替换为分隔符的字符
     * @var array
     */
    protected array $splitReplaces = [];
    
    /**
     * 切分数组的分隔符
     * @var string
     */
    protected string $splitDelimiter = ',';
    
    /**
     * 至少需要切分的数组长度
     * @var int
     */
    protected int $splitMin = 0;
    
    /**
     * 最多允许切分的数组长度
     * @var int|null
     */
    protected ?int $splitLimit = null;
    
    /**
     * 日期格式
     * @var string
     */
    protected string $dateFormat = 'Y-m-d H:i:s';
    
    
    /**
     * 快速实例化
     * @param string                                                   $letter 所在列的字母
     * @param Entity|string                                            $field 数据键或字段实体
     * @param string|callable|Closure(ImportFilterParameter):void|null $filter 过滤方式
     * @return static
     */
    public static function init(string $letter = '', Entity|string $field = '', string|callable|null|Closure $filter = null) : static
    {
        return new static($letter, $field, $filter);
    }
    
    
    /**
     * 构造函数
     * @param string                                                   $letter 所在列的字母
     * @param Entity|string                                            $field 数据键或字段实体
     * @param string|callable|Closure(ImportFilterParameter):void|null $filter 过滤方式
     */
    public function __construct(string $letter = '', Entity|string $field = '', string|callable|null|Closure $filter = null)
    {
        $this->field  = $field;
        $this->filter = $filter;
        $this->letter($letter);
        
        if ($this->field instanceof Entity) {
            $attr = $this->field->getPropertyAttr();
            
            // 使用导入注解
            if ($import = $attr['import']) {
                if (!$this->letter) {
                    $this->letter($import->getLetter());
                }
                if (!$this->filter) {
                    $this->filter = $import->getFilter();
                }
                if ($dateFormat = $import->getDateFormat()) {
                    $this->date($dateFormat);
                }
                if ($trims = $import->getTrim()) {
                    $this->trim($trims);
                }
                $splits = $import->getSplit();
                if (isset($splits['replaces']) && $splits['replaces']) {
                    $this->splitReplaces = $splits['replaces'];
                }
                if (isset($splits['min']) && $splits['min'] > 0) {
                    $this->splitMin = $splits['min'];
                }
                if (isset($splits['limit'])) {
                    $this->splitLimit = $splits['limit'];
                }
                if (isset($splits['delimiter']) && $splits['delimiter'] !== '') {
                    $this->splitDelimiter = $splits['delimiter'];
                }
            }
            
            // 使用导出注解
            $export = $attr['export'];
            if (!$this->letter && $export && $export->isImport()) {
                $this->letter($export->getLetter());
            }
            
            if (!$this->filter) {
                $type = $attr['field_type'];
                if (!$type || $type == FieldColumn::TYPE_DEFAULT) {
                    $type = $attr['var_type'];
                }
                switch ($type) {
                    case ClassHelper::CAST_INT:
                        $this->filter = self::FILTER_INT;
                    break;
                    case ClassHelper::CAST_FLOAT:
                        $this->filter = self::FILTER_FLOAT;
                    break;
                    case ClassHelper::CAST_BOOL:
                        $this->filter = self::FILTER_BOOL;
                    break;
                    case FieldColumn::TYPE_TIMESTAMP:
                        $this->filter = self::FILTER_TIMESTAMP;
                    break;
                    case FieldColumn::TYPE_DATETIME:
                    case FieldColumn::TYPE_DATE:
                        $this->filter = self::FILTER_DATE;
                        if (!$this->dateFormat && $type === FieldColumn::TYPE_DATE) {
                            $this->dateFormat = 'Y-m-d';
                        }
                    break;
                }
            }
            
            if (!$this->filter && $export && $export->getFilter() === ExportColumn::FILTER_DATE) {
                $this->filter = self::FILTER_TIMESTAMP;
            }
            
            $this->field = $this->field->name();
        }
    }
    
    
    /**
     * 设置切分数组参数
     * @param string   $delimiter 切割符号
     * @param array    $replaces 要替换为切割符号的字符
     * @param int      $min 至少需要切分的数组长度
     * @param int|null $limit 切割限制次数
     * @return static
     */
    public function split(string $delimiter = ',', array $replaces = [], int $min = 0, ?int $limit = null) : static
    {
        $this->splitReplaces  = $replaces;
        $this->splitDelimiter = $delimiter;
        $this->splitMin       = $min;
        $this->splitLimit     = $limit;
        
        return $this;
    }
    
    
    /**
     * 设置日期格式
     * @param string $format
     * @return $this
     */
    public function date(string $format) : static
    {
        $this->dateFormat = $format;
        
        return $this;
    }
    
    
    /**
     * @return string
     */
    public function getDateFormat() : string
    {
        return $this->dateFormat;
    }
    
    
    /**
     * @return string
     */
    public function getSplitDelimiter() : string
    {
        return $this->splitDelimiter;
    }
    
    
    /**
     * @return string[]
     */
    public function getSplitReplaces() : array
    {
        return $this->splitReplaces;
    }
    
    
    /**
     * @return int
     */
    public function getSplitMin() : int
    {
        return $this->splitMin;
    }
    
    
    /**
     * @return int|null
     */
    public function getSplitLimit() : ?int
    {
        return $this->splitLimit;
    }
    
    
    /**
     * 设置剔除左右的字符
     * @param string[] $characters
     * @return static
     */
    public function trim(array $characters) : static
    {
        $this->trimCharacters = $characters;
        
        return $this;
    }
    
    
    /**
     * @return string[]
     */
    public function getTrimCharacters() : array
    {
        return $this->trimCharacters;
    }
    
    
    /**
     * @return string
     */
    public function getField() : string
    {
        if ($this->field === '') {
            $this->field = $this->letter;
        }
        
        return $this->field;
    }
    
    
    /**
     * 设置字母
     * @param string $letter
     * @return static
     */
    public function letter(string $letter) : static
    {
        $this->letter = strtoupper($letter);
        
        return $this;
    }
    
    
    /**
     * @return string
     */
    public function getLetter() : string
    {
        return $this->letter;
    }
    
    
    /**
     * @return callable|string|null
     */
    public function getFilter() : callable|string|null
    {
        return $this->filter;
    }
}