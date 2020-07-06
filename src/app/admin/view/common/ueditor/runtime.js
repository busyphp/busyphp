/**
 * 百度编辑器实例化程序
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2017 busy^life <busy.life@qq.com>
 * @version $Id: 2017-06-22 上午10:22 runtime.js busy^life $
 * @todo 1. word转存图片
 * @todo 2. 截图功能
 */
(function ($, window) {
	var fileConfig    = eval('(<?=$file_config?>)');
	var uEditorConfig = eval('(<?=$editor_config?>)');
	var defaultConfig = {
		wordCount              : false, // 默认关闭字数统计
		catchRemoteImageEnable : true,  // 默认抓取远程图片到本地
		zIndex                 : 1000,  // 默认编辑器层级
		scaleEnabled           : false, // 默认不可以拉伸
		autoHeightEnabled      : false, // 默认不允许自动长高
		autoFloatEnabled       : false,  // 默认toolbar的位置不动为关闭
		elementPathEnabled     : false, // 默认不显示元素路径
		enableAutoSave         : false, // 不自动保存
		xssFilterRules         : true, // 默认开启XXS过滤
		inputXssFilter         : true, // 输入XXS过滤
		outputXssFilter        : true, // 输出XXS过滤
		whitList               : { // XXS白名单规则
			a          : ['target', 'href', 'title', 'class', 'style'],
			abbr       : ['title', 'class', 'style'],
			address    : ['class', 'style'],
			area       : ['shape', 'coords', 'href', 'alt'],
			article    : [],
			aside      : [],
			audio      : ['autoplay', 'controls', 'loop', 'preload', 'src', 'class', 'style'],
			b          : ['class', 'style'],
			bdi        : ['dir'],
			bdo        : ['dir'],
			big        : [],
			blockquote : ['cite', 'class', 'style'],
			br         : [],
			caption    : ['class', 'style'],
			center     : [],
			cite       : [],
			code       : ['class', 'style'],
			col        : ['align', 'valign', 'span', 'width', 'class', 'style'],
			colgroup   : ['align', 'valign', 'span', 'width', 'class', 'style'],
			dd         : ['class', 'style'],
			del        : ['datetime'],
			details    : ['open'],
			div        : ['class', 'style'],
			dl         : ['class', 'style'],
			dt         : ['class', 'style'],
			em         : ['class', 'style'],
			font       : ['color', 'size', 'face'],
			footer     : [],
			h1         : ['class', 'style'],
			h2         : ['class', 'style'],
			h3         : ['class', 'style'],
			h4         : ['class', 'style'],
			h5         : ['class', 'style'],
			h6         : ['class', 'style'],
			header     : [],
			hr         : [],
			i          : ['class', 'style'],
			img        : ['src', 'alt', 'title', 'width', 'height', 'id', '_src', 'loadingclass', 'class', 'data-latex'],
			ins        : ['datetime'],
			li         : ['class', 'style'],
			mark       : [],
			nav        : [],
			ol         : ['class', 'style'],
			p          : ['class', 'style'],
			pre        : ['class', 'style'],
			s          : [],
			section    : [],
			small      : [],
			span       : ['class', 'style'],
			sub        : ['class', 'style'],
			sup        : ['class', 'style'],
			strong     : ['class', 'style'],
			table      : ['width', 'border', 'align', 'valign', 'class', 'style'],
			tbody      : ['align', 'valign', 'class', 'style'],
			td         : ['width', 'rowspan', 'colspan', 'align', 'valign', 'class', 'style'],
			tfoot      : ['align', 'valign', 'class', 'style'],
			th         : ['width', 'rowspan', 'colspan', 'align', 'valign', 'class', 'style'],
			thead      : ['align', 'valign', 'class', 'style'],
			tr         : ['rowspan', 'align', 'valign', 'class', 'style'],
			tt         : [],
			u          : [],
			ul         : ['class', 'style'],
			video      : ['autoplay', 'controls', 'loop', 'preload', 'src', 'height', 'width', 'class', 'style']
		}
	};

	// 基本配置
	window.UEDITOR_CONFIG = {
		UEDITOR_HOME_URL    : '{$url.plugin}',
		serverUrl           : '{:url("server")}',
		charset             : "utf-8",
		pageBreakTag        : '{$Think.config.app.VAR_CONTENT_PAGE}',
		listiconpath        : '',
		emotionLocalization : '',

		//图片上传配置
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

	// 执行自动实例化
	$("[data-ueditor]").each(function () {
		// 禁止重新实例化
		var $this = $(this);
		if ($this.data('editor')) {
			return true;
		}

		// 开始实例化
		var offset,
			id         = $.trim($this.attr('id') || ''),
			configName = $.trim($this.attr('data-ueditor')) || 'default',
			imageType  = $.trim($this.attr('role-image-mark') || '{$default_image}'),
			fileType   = $.trim($this.attr('role-file-mark') || '{$default_file}'),
			videoType  = $.trim($this.attr('role-video-mark') || '{$default_video}'),
			fileMark   = $.trim($this.attr('role-mark') || '');

		// 删除 bootstarp 表单样式
		$this.removeClass('form-control');

		// 没有ID则需要创建ID
		if (!id) {
			offset = $this.offset();
			id     = 'UEditor_' + parseInt(offset.left) + '_' + parseInt(offset.top);
			$this.attr('id', id);
		}

		// 上传图片配置
		var imageConfig = fileConfig[imageType] || {};
		var imageSuffix = imageConfig.suffix || [];
		for (var i = 0; i < imageSuffix.length; i++) {
			imageSuffix[i] = '.' + imageSuffix[i];
		}

		var config             = $.extend(defaultConfig, uEditorConfig[configName] || {});
		config.imageMaxSize    = imageConfig.size;
		config.imageAllowFiles = imageSuffix;
		config                 = $.extend(config, UEDITOR_CONFIG);

		// 初始化编辑器
		var ue = UE.getEditor(id, config);

		// 实例化
		ue.ready(function () {
			ue.execCommand('serverparam', {
				mark_file_type  : fileType,
				mark_image_type : imageType,
				mark_video_type : videoType,
				mark_value      : fileMark
			});
		});
		$this.data('editor', ue);
	});
})(jQuery, window);