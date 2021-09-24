// UEditor运行配置
window.UEDITOR_CONFIG = {
    busyFileConfig : eval('(<?=$file_config?>)'),

    UEDITOR_HOME_URL    : '{$skin.lib}ueditor/',
    serverUrl           : '{:url("server")}',
    pageBreakTag        : '{$Think.config.app.VAR_CONTENT_PAGE}',
    listiconpath        : '',
    emotionLocalization : '',

    // 图片上传配置
    imageActionName     : 'upload',
    imageFieldName      : 'upload',
    imageUrlPrefix      : '',
    imageCompressEnable : false,
    imageCompressBorder : 1600,

    // 截图工具上传
    snapscreenActionName : 'snapscreen',
    snapscreenUrlPrefix  : '',

    // 抓取远程图片配置
    catcherLocalDomain : ["127.0.0.1", "localhost", "{$_SERVER.HTTP_HOST}"],
    catcherActionName  : 'remote',
    catcherFieldName   : 'upload',
    catcherUrlPrefix   : '',

    // 重新定义dialog页面
    iframeUrlMap : {
        insertimage : '{:url("insert_image")}',
        insertvideo : '{:url("insert_video")}',
        attachment  : '{:url("insert_attachment")}',
        scrawl      : '{:url("scrawl")}',
        wordimage   : '{:url("word_image")}'
    }
};