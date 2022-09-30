<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\file\image;

use BusyPHP\exception\ParamInvalidException;
use BusyPHP\image\driver\Local;
use BusyPHP\image\driver\local\LocalImageStyleManagerInterface;
use BusyPHP\image\result\ImageStyleResult;
use BusyPHP\Model;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\facade\Filesystem;
use think\filesystem\Driver as FilesystemDriver;

/**
 * 图片样式模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/15 11:33 AM SystemFileImageStyle.php $
 * @method SystemFileImageStyleInfo getInfo($data, $notFoundMessage = null)
 * @method SystemFileImageStyleInfo findInfo($data = null, $notFoundMessage = null)
 * @method SystemFileImageStyleInfo[] selectList()
 * @method SystemFileImageStyleInfo[] buildListWithField(array $values, $key = null, $field = null)
 */
class SystemFileImageStyle extends Model implements LocalImageStyleManagerInterface
{
    protected $bindParseClass      = SystemFileImageStyleInfo::class;
    
    protected $dataNotFoundMessage = '图片样式不存在';
    
    protected $findInfoFilter      = 'trim';
    
    
    /**
     * 添加图片样式
     * @param SystemFileImageStyleField $data
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function createStyle(SystemFileImageStyleField $data) : string
    {
        if (!$data->id) {
            throw new ParamInvalidException('$data->id');
        }
        
        $data->content = json_encode($data->content, JSON_UNESCAPED_UNICODE);
        $this->addData($data, true);
        
        return $data->id;
    }
    
    
    /**
     * 获取预览图路径
     * @param string|FilesystemDriver $disk
     * @return string
     */
    public static function getPreviewImagePath($disk) : string
    {
        if (!$disk instanceof FilesystemDriver) {
            $disk = Filesystem::disk($disk);
        }
        
        $path = "system/preview.jpeg";
        if (!$disk->has($path)) {
            $disk->put($path, file_get_contents(__DIR__ . '/../../../../../../assets/images/preview.jpeg'));
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
        $result->rule    = Local::convertParameterToProcessRule(ImageStyleResult::convertContentToUrlParameter($info->content));
        
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
            $result->rule    = Local::convertParameterToProcessRule(ImageStyleResult::convertContentToUrlParameter($item->content));
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
        $this->deleteInfo($name);
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
        $data          = SystemFileImageStyleField::init();
        $data->id      = $name;
        $data->content = $content;
        $this->createStyle($data);
    }
    
    
    /**
     * 更新图片样式
     * @param string $name 图片样式
     * @param array  $content 样式规则
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function updateImageStyle(string $name, array $content)
    {
        $this->createImageStyle($name, $content);
    }
}