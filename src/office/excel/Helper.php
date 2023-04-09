<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel;

use BusyPHP\office\excel\export\ExportColumn;
use BusyPHP\office\excel\import\ImportColumn;

/**
 * Excel辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/7 19:18 Helper.php $
 */
class Helper
{
    /**
     * 生成A-ZZ字母
     * @return string[]
     */
    public static function letters() : array
    {
        static $list;
        
        if (!isset($list)) {
            $list = [];
            for ($i = 'A'; $i < 'ZZ'; $i++) {
                $list[] = $i;
            }
            
            $list[] = 'ZZ';
        }
        
        return $list;
    }
    
    
    /**
     * 填充字母并排序
     * @param ExportColumn[]|ImportColumn[] $columns
     * @return ExportColumn[]|ImportColumn[]
     */
    public static function fullLetterAndSortColumns(array $columns) : array
    {
        $letters    = self::letters();
        $deleteList = [];
        $columnList = [];
        $newColumns = [];
        foreach ($columns as $column) {
            $letter = $column->getLetter();
            if ($letter) {
                $newColumns[] = $column;
                if (false !== $index = array_search($letter, $letters, true)) {
                    $deleteList[] = $index;
                }
            } else {
                $columnList[] = $column;
            }
        }
        
        $newLetters = [];
        foreach ($letters as $i => $letter) {
            if (!in_array($i, $deleteList, true)) {
                $newLetters[] = $letter;
            }
        }
        
        foreach ($columnList as $i => $item) {
            $item->letter($newLetters[$i]);
            $newColumns[] = $item;
        }
        
        $columnMap = [];
        foreach ($newColumns as $column) {
            $columnMap[$column->getLetter()] = $column;
        }
        ksort($columnMap);
        unset($columnList, $newLetters, $letters);
        
        return array_values($columnMap);
    }
}