<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\file\image;

use BusyPHP\image\driver\Local;
use BusyPHP\image\driver\local\LocalImageStyleManagerInterface;
use BusyPHP\image\result\ImageStyleResult;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use League\Flysystem\FilesystemException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\facade\Filesystem;
use think\filesystem\Driver as FilesystemDriver;

/**
 * 图片样式模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/15 11:33 AM SystemFileImageStyle.php $
 * @method SystemFileImageStyleField getInfo(string $id, string $notFoundMessage = null)
 * @method SystemFileImageStyleField|null findInfo(string $id = null)
 * @method SystemFileImageStyleField[] selectList()
 * @method SystemFileImageStyleField[] indexList(string|Entity $key = '')
 * @method SystemFileImageStyleField[] indexListIn(array $range, string|Entity $key = '', string|Entity $field = '')
 */
class SystemFileImageStyle extends Model implements LocalImageStyleManagerInterface, ContainerInterface
{
    protected string $fieldClass          = SystemFileImageStyleField::class;
    
    protected string $dataNotFoundMessage = '图片样式不存在';
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * 添加图片样式
     * @param SystemFileImageStyleField $data
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function create(SystemFileImageStyleField $data) : string
    {
        $this->validate($data, static::SCENE_CREATE)->replace(true)->insert();
        
        return $data->id;
    }
    
    
    /**
     * 获取预览图路径
     * @param string|FilesystemDriver $disk
     * @return string
     * @throws FilesystemException
     */
    public static function getPreviewImagePath($disk) : string
    {
        if (!$disk instanceof FilesystemDriver) {
            $disk = Filesystem::disk($disk);
        }
        
        $path = "system/preview.jpeg";
        if (!$disk->fileExists($path)) {
            $disk->write($path, file_get_contents(__DIR__ . '/../../../../../../assets/images/preview.jpeg'));
        }
        
        return $path;
    }
    
    
    /**
     * 获取图片样式
     * @param string $name 图片样式
     * @return ImageStyleResult
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getImageStyle(string $name) : ImageStyleResult
    {
        $info            = $this->getInfo($name);
        $result          = new ImageStyleResult();
        $result->id      = $info->id;
        $result->content = $info->content;
        $result->rule    = Local::convertImageToProcessRule(ImageStyleResult::convertContentToImage($info->content));
        
        return $result;
    }
    
    
    /**
     * 查询图片样式
     * @return ImageStyleResult[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function selectImageStyle() : array
    {
        $list = [];
        foreach ($this->selectList() as $item) {
            $result          = new ImageStyleResult();
            $result->id      = $item->id;
            $result->content = $item->content;
            $result->rule    = Local::convertImageToProcessRule(ImageStyleResult::convertContentToImage($item->content));
            $list[]          = $result;
        }
        
        return $list;
    }
    
    
    /**
     * 删除图片样式
     * @param string $name 图片样式
     * @return void
     * @throws DbException
     */
    public function deleteImageStyle(string $name) : void
    {
        $this->remove($name);
    }
    
    
    /**
     * 创建图片样式
     * @param string $name 图片样式
     * @param array  $content 样式规则
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function createImageStyle(string $name, array $content) : void
    {
        $data = SystemFileImageStyleField::init();
        $data->setId($name);
        $data->setContent($content);
        $this->create($data);
    }
    
    
    /**
     * 更新图片样式
     * @param string $name 图片样式
     * @param array  $content 样式规则
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function updateImageStyle(string $name, array $content)
    {
        $this->createImageStyle($name, $content);
    }
}