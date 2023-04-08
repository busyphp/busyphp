<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\field;

use Attribute;

/**
 * 导入注解
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/8 21:50 Import.php $
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Import
{
    /**
     * 字母
     * @var string
     */
    private string $letter;
    
    /**
     * 过滤方式
     * @var callable|string|null
     */
    private $filter;
    
    /**
     * 字符切割选项
     * @var string|array
     */
    private string|array $split;
    
    /**
     * 字符剔除左右选项
     * @var string|array
     */
    private string|array $trim;
    
    /**
     * 时间格式化选项
     * @var string
     */
    private string $dateFormat;
    
    
    /**
     * 构造函数
     * @param string               $letter 导入字母列
     * @param string|callable|null $filter 过滤方式
     * @param string|array         $split 字符切割选项
     * @param string|array         $trim 字符剔除左右选项
     * @param string               $dateFormat 时间格式化选项
     */
    public function __construct(string $letter = '', string|callable|null $filter = null, string|array $split = ',', string|array $trim = '', $dateFormat = 'Y-m-d H:i:s')
    {
        $this->letter     = $letter;
        $this->filter     = $filter;
        $this->split      = $split;
        $this->trim       = $trim;
        $this->dateFormat = $dateFormat;
    }
    
    
    /**
     * @return string
     */
    public function getDateFormat() : string
    {
        return $this->dateFormat;
    }
    
    
    /**
     * @return callable|string|null
     */
    public function getFilter() : callable|string|null
    {
        return $this->filter;
    }
    
    
    /**
     * @return string
     */
    public function getLetter() : string
    {
        return strtoupper($this->letter);
    }
    
    
    /**
     * @return array{delimiter: string, min: int, limit: ?int, replaces: array}
     */
    public function getSplit() : array
    {
        if (!is_array($this->split)) {
            $this->split = [
                'delimiter' => $this->split,
            ];
        }
        
        return $this->split;
    }
    
    
    /**
     * @return array
     */
    public function getTrim() : array
    {
        if (!is_array($this->trim)) {
            if ($this->trim) {
                $this->trim = (array) $this->trim;
            } else {
                $this->trim = [];
            }
        }
        
        return $this->trim;
    }
}