<?php
/**
 * 百度Ueditor编辑器配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午2:47 下午 ueditor.php $
 */
return [
    // 全部工具栏
    // fullscreen,source,|,undo,redo,|,bold,italic,underline,fontborder,strikethrough,superscript,subscript,removeformat,formatmatch,autotypeset,blockquote,pasteplain,|,forecolor,backcolor,insertorderedlist,insertunorderedlist,selectall,cleardoc,|,rowspacingtop,rowspacingbottom,lineheight,|,customstyle,paragraph,fontfamily,fontsize,|,directionalityltr,directionalityrtl,indent,|,justifyleft,justifycenter,justifyright,justifyjustify,|,touppercase,tolowercase,|,link,unlink,anchor,|,imagenone,imageleft,imageright,imagecenter,|,simpleupload,insertimage,scrawl,insertvideo,music,attachment,map,gmap,insertframe,insertcode,webapp,pagebreak,template,background,|,horizontal,date,time,spechars,snapscreen,wordimage,|,inserttable,deletetable,insertparagraphbeforetable,insertrow,deleterow,insertcol,deletecol,mergecells,mergeright,mergedown,splittocells,splittorows,splittocols,charts,|,print,preview,searchreplace,drafts,help
    
    //+--------------------------------------
    //| 默认配置
    //+--------------------------------------
    'default' => [
        // 工具栏定义
        'toolbars'                      => 'fullscreen,source,|,undo,redo,|,bold,italic,underline,fontborder,strikethrough,superscript,subscript,removeformat,formatmatch,autotypeset,blockquote,pasteplain,|,forecolor,backcolor,insertorderedlist,insertunorderedlist,selectall,cleardoc,|,rowspacingtop,rowspacingbottom,lineheight,|,customstyle,paragraph,fontfamily,fontsize,|,directionalityltr,directionalityrtl,indent,|,justifyleft,justifycenter,justifyright,justifyjustify,|,touppercase,tolowercase,|,link,unlink,anchor,|,imagenone,imageleft,imageright,imagecenter,|,simpleupload,insertimage,attachment,insertvideo,scrawl,|,map,horizontal,date,time,spechars,|,inserttable,deletetable,insertparagraphbeforetable,insertrow,deleterow,insertcol,deletecol,mergecells,mergeright,mergedown,splittocells,splittorows,splittocols,charts,|,print,preview,searchreplace',
        
        // 显示附件管理器，默认是显示
        'busyPHPAttachmentOnlineManage' => true,
        
        // 显示图片管理器，默认是显示
        'busyPHPImageOnlineManage'      => true,
        
        // 是否允许上传视频，默认是允许
        'busyPHPVideoCanUpload'         => true,
        
        // 开启抓取远程图片
        'catchRemoteImageEnable'        => true,
        
        // 自动长高
        'autoHeightEnabled'             => true,
    ],
    
    //+--------------------------------------
    //| 精简版
    //+--------------------------------------
    'small'   => [
        // 工具栏定义
        'toolbars'               => 'fullscreen,source,|,undo,redo,|,bold,italic,underline,fontborder,strikethrough,superscript,subscript,removeformat,formatmatch,autotypeset,blockquote,pasteplain,|,forecolor,backcolor,insertorderedlist,insertunorderedlist,|,paragraph,fontsize,|,indent,justifyleft,justifycenter,justifyright,justifyjustify,|,link,unlink,anchor,|,simpleupload,insertimage,scrawl,|,horizontal,spechars,|,inserttable,deletetable,|,print,preview,searchreplace',
        
        // 开启抓取远程图片
        'catchRemoteImageEnable' => true,
        
        // 自动长高
        'autoHeightEnabled'      => true,
    ],
    
    //+--------------------------------------
    //| 只允许有链接
    //+--------------------------------------
    'link'    => [
        // 工具栏定义
        'toolbars'               => 'undo,redo,|,bold,italic,underline,removeformat,formatmatch,autotypeset,forecolor,link,unlink',
        
        // 开启抓取远程图片
        'catchRemoteImageEnable' => false,
    ]
];