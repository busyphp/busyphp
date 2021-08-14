// 全局常量
window.UPLOADIFY_STAMP   = '{$stamp}';
window.UPLOADIFY_SID     = '{$sid}';
window.UPLOADIFY_URL     = '{:url("Common.Action/upload")}';
window.UPLOADIFY_SWF_URL = '{$skin.lib}webuploader/Uploader.swf';
window.FILE_CLASS        = eval('({:json_encode($file_class)})');
window.FILE_LIBRARY_URL  = '{:url("System.File/library?mark_type=__MARK_TYPE__&mark_value=__MARK_VALUE__")}';
window.FILE_PICKER       = '{:url("System.File/picker?mark_type=_type_&mark_value=_value_")}';

(window.busyAdmin || {}).uploadOptions = {
    stamp   : '{$stamp}',
    sid     : '{$sid}',
    url     : '{:url("Common.Action/upload")}',
    library : '{:url("System.File/library?mark_type=__MARK_TYPE__&mark_value=__MARK_VALUE__")}',
    picker  : '{:url("System.File/picker?mark_type=_type_&mark_value=_value_&extensions=_extensions_")}',
    swf     : '{$skin.lib}webuploader/Uploader.swf',
    config  : eval('({:json_encode($file_class)})'),
};

(function ($, window) {

    /**
     * 后台系统文件选择器
     * @param markType
     * @param markValue
     * @param callback
     * @constructor
     */
    var AdminFileSelect = function (markType, markValue, callback) {
        busyAdmin.dialog.iframe(window.FILE_LIBRARY_URL.replace('__MARK_TYPE__', (markType || '')).replace('__MARK_VALUE__', (markValue || '')), function (data) {
            if (!data) {
                return;
            }

            if (typeof callback === 'function') {
                return callback(data);
            }
        });
    };

    var ADMIN_FILE_PROGRESS_TEMPLATE = '<div class="webuploader-item" id="[itemId]">\
	<a class="close" data-id="close">&times;</a>\
	<div class="webuploader-thumb" data-id="thumb"></div>\
	<div class="webuploader-body">\
		<div class="title">[fileName]</div>\
		<div class="progress">\
			<div class="progress-bar progress-bar-success progress-bar-striped" data-id="progress" style="width: 0">\
				<span class="progress-number" data-id="speed">0%</span>\
			</div>\
		</div>\
		<div class="foot">\
			<div class="size pull-right">[fileSize]</div>\
			<div class="status pull-left" data-id="status"></div>\
			<div class="clearfix"></div>\
		</div>\
	</div>\
</div>';

    var AdminFileUpload = function ($element) {
        this.$element = $element;
        this.init();
    };

    /**
     * 初始化
     * @return {AdminFileUpload}
     */
    AdminFileUpload.prototype.init = function () {
        this.$wrap           = this.$element.parent('.input-group');
        this.data            = this.$element.data();
        this.id              = this.$element.attr('id');
        this.data.markType   = this.data.markType || '';
        this.data.descTarget = this.data.descTarget || '';
        if (!this.data.markType.length) {
            alert('bp:file缺少附件分类');
            return this;
        }

        this.fileConfig = window.FILE_CLASS[this.data.markType] || false;
        if (!this.fileConfig) {
            alert('没有该分类配置[' + this.data.markType + ']');
            return this;
        }

        // 回调
        this.targetCallback     = null;
        this.descTargetCallback = null;
        this.result             = {};

        // 是否支持上传
        this.$uploadElement  = this.$wrap.find('[data-module="upload"]');
        this.$uploadQueueBox = null;
        this.uploadQueueId   = this.id + 'QueueBox';
        this.canUpload       = this.$uploadElement.length > 0;
        this.uploader        = null;
        this.uploaderOptions = {};
        this.uploaderChunked = false;
        this.upload();

        // 是否支持选择
        this.$selectElement = this.$wrap.find('[data-module="select"]');
        this.canSelect      = this.$selectElement.length > 0;
        this.select();

        $(this.data.target).prop('disabled', false).attr('disabled', false).removeClass('disabled');

        return this;
    };

    /**
     * 附件选择
     * @return {AdminFileUpload}
     */
    AdminFileUpload.prototype.select = function () {
        if (!this.canUpload) {
            return this;
        }

        var me = this;
        this.$selectElement.on('click', function () {
            new AdminFileSelect(me.data.markType, me.data.markValue, function (data) {
                var extension = data.extension || '';
                var url       = data.fileUrl || '';

                if (false !== me.fileConfig && me.fileConfig.suffix.length > 0) {
                    if (-1 === (',' + me.fileConfig.suffix + ',').indexOf(',' + extension + ',')) {
                        $.dialog.alert('请选择有效的' + me.fileConfig.name);
                        return;
                    }
                }

                me.result = {
                    isUpload  : false,
                    isSelect  : true,
                    file_id   : data.fileId || 0,
                    file_url  : url,
                    extension : extension,
                    name      : data.name || '',
                    filename  : data.filename || '',
                    size      : data.size || 0,
                    isThumb   : false,
                    thumb     : {}
                };
                me.targetFile(url, extension);
            });
        });

        this.$selectElement.attr('disabled', false).prop('disabled', false).removeClass('disabled');
    };

    /**
     * 附件上传
     * @return {AdminFileUpload}
     */
    AdminFileUpload.prototype.upload = function () {
        if (!this.canUpload) {
            return this;
        }

        // 创建列队容器
        var me               = this;
        this.$uploadQueueBox = $('<div class="webuploader-queue" id="' + this.uploadQueueId + '"/>').hide();
        this.$wrap.after(this.$uploadQueueBox);

        // 配置
        this.uploaderOptions = {
            swf                 : window.UPLOADIFY_SWF_URL,
            server              : window.UPLOADIFY_URL,
            auto                : true,
            fileVal             : 'upload',
            method              : 'POST',
            dnd                 : this.$wrap[0],
            disableGlobalDnd    : true,
            chunked             : true,
            chunkSize           : 5242880,
            threads             : 3,
            fileSingleSizeLimit : this.fileConfig.size <= 0 ? undefined : this.fileConfig.size,
            duplicate           : false,
            compress            : false,
            pick                : {
                id       : '#' + this.$uploadElement.attr('id'),
                multiple : false
            },
            formData            : {
                stamp      : window.UPLOADIFY_STAMP,
                sid        : window.UPLOADIFY_SID,
                mark_type  : this.data.markType || '',
                mark_value : this.data.markValue || ''
            },
            accept              : {
                title      : '选择' + this.fileConfig.name,
                extensions : this.fileConfig.suffix,
                mimeTypes  : this.fileConfig.mime
            }
        };

        // 实例化上传
        this.uploader = new WebUploader.Uploader(this.uploaderOptions);

        // 事件
        // 当文件被加入队列以后触发
        this.uploader.on('fileQueued', function (file) {
            // 解析列队
            var $item, item = ADMIN_FILE_PROGRESS_TEMPLATE.replace(/\[itemId\]/g, me.getQueueId(file));
            item            = item.replace(/\[fileName\]/g, file.name);
            item            = item.replace(/\[fileSize\]/g, WebUploader.Base.formatSize(file.size, 2, ['B', 'KB', 'MB', 'GB', 'TB']));

            // 插入列队
            me.$uploadQueueBox.html(item).show();
            $item = me.getQueueItem(file);

            // 创建缩略图
            var $thumb = $item.find('[data-id="thumb"]');
            this.makeThumb(file, function (error, src) {
                if (error) {
                    return;
                }
                $thumb.append('<img src="' + src + '" width="60" height="60"/>');
            }, 60, 60);

            // 取消绑定
            $item.find('[data-id="close"]').on('click', function () {
                me.uploader.removeFile(file, true);
            });

            me.setDisabled(true);
            me.uploaderChunked = false;
        });

        // 当文件被移除队列后触发
        this.uploader.on('fileDequeued', function (file) {
            this.reset();
            me.setQueueStatus(file, false, '成功取消上传');
            me.setDisabled(false);
        });

        // 当某个文件的分块在发送前触发，主要用来询问是否要添加附带参数，大文件在开起分片上传的前提下此事件可能会触发多次。
        this.uploader.on('uploadBeforeSend', function (object, data, headers) {
            me.uploaderChunked = object.chunks !== 1;
        });

        // 某个文件开始上传前触发，一个文件只会触发一次。
        this.uploader.on('uploadStart', function (file) {
            this.option('formData', {guid : file.source.uid, is_complete : 0});
        });

        // 上传过程中触发，携带上传进度
        this.uploader.on('uploadProgress', function (file, percentage) {
            me.setQueueProgress(file, (percentage * 100).toFixed(2));
        });

        // 当文件上传成功时触发
        this.uploader.on('uploadSuccess', function (file, json) {
            this.reset();

            // 上传成功
            if (json.status) {
                if (me.uploaderChunked) {
                    console.log("分片上传完成");
                    var params         = me.uploader.option('formData');
                    params.is_complete = 1;
                    params.filename    = file.name;
                    $.post(me.uploader.option('server'), params, function (data) {
                        if (data.status) {
                            me.result = {
                                isUpload  : true,
                                isSelect  : false,
                                file_id   : data.data.file_id,
                                file_url  : data.data.file_url,
                                extension : data.data.extension,
                                name      : data.data.name,
                                filename  : data.data.filename,
                                size      : data.data.size,
                                isThumb   : json.data.isThumb,
                                thumb     : json.data.thumb
                            };
                            me.setQueueStatus(file, true, '上传成功');
                            me.targetFile(data.data.file_url, data.data.extension);
                        } else {
                            me.setQueueStatus(file, false, data.info);
                        }
                    }, 'json');
                } else {
                    me.result = {
                        isUpload  : true,
                        isSelect  : false,
                        file_id   : json.data.file_id,
                        file_url  : json.data.file_url,
                        extension : json.data.extension,
                        name      : json.data.name,
                        filename  : json.data.filename,
                        size      : json.data.size,
                        isThumb   : json.data.isThumb,
                        thumb     : json.data.thumb
                    };
                    me.setQueueStatus(file, true, '上传成功');
                    me.targetFile(json.data.file_url, json.data.extension);
                }
            }

            // 上传失败
            else {
                me.setQueueStatus(file, false, json.info);
            }
        });

        // 当文件上传出错时触发
        this.uploader.on('uploadError', function (file, reason) {
            this.reset();
            me.setQueueStatus(file, false, $.parseUploaderError(reason));
        });

        // 不管成功或者失败，文件上传完成时触发
        this.uploader.on('uploadComplete', function (file) {
            me.setDisabled(false);
            this.reset();
        });

        // 当validate不通过时，会以派送错误事件的形式通知调用者。
        this.uploader.on('error', function (code) {
            $.dialog.alert($.parseUploaderCode(code));
        });

        this.$uploadElement.removeClass('disabled').prop('disabled', false).attr('disabled', false);

        this.resetUpload();

        // 描述
        this.targetDesc();
        return this;
    };

    /**
     * 设置是否禁用按钮
     * @param status
     * @return {AdminFileUpload}
     */
    AdminFileUpload.prototype.setDisabled = function (status) {
        if (status) {
            this.uploader.disable();
            if (this.canUpload) {
                this.$uploadElement.addClass('disabled').prop('disabled', true);
            }
            if (this.canSelect) {
                this.$selectElement.addClass('disabled').prop('disabled', true);
            }
        } else {
            this.uploader.enable();
            if (this.canUpload) {
                this.$uploadElement.removeClass('disabled').prop('disabled', false);
            }
            if (this.canSelect) {
                this.$selectElement.removeClass('disabled').prop('disabled', false);
            }
        }
        return this;
    };

    /**
     * 获取列队ID
     * @return string
     */
    AdminFileUpload.prototype.getQueueId = function (file) {
        return this.id + 'QueueItem' + file.id;
    };

    /**
     * 获取列队
     * @param file
     * @return {jQuery}
     */
    AdminFileUpload.prototype.getQueueItem = function (file) {
        return $('#' + this.getQueueId(file));
    };

    /**
     * 设置列队进度状态
     * @param file
     * @param percentage
     * @return {AdminFileUpload}
     */
    AdminFileUpload.prototype.setQueueProgress = function (file, percentage) {
        var $item = this.getQueueItem(file);
        $item.find('[data-id="speed"]').text(percentage + '%');
        $item.find('[data-id="progress"]').css({'width' : percentage + '%'});
        $item.find('[data-id="status"]').text('上传中...');

        return this;
    };

    /**
     * 设置列队状态
     * @param file
     * @param status
     * @param message
     * @param isRemove
     * @return {AdminFileUpload}
     */
    AdminFileUpload.prototype.setQueueStatus = function (file, status, message, isRemove) {
        var $item   = this.getQueueItem(file),
            timeout = 1000;

        isRemove = typeof isRemove === 'boolean' ? isRemove : true;
        $item.find('[data-id="close"]').remove();
        if (status) {
            $item.find('[data-id="status"]').text(message);
        } else {
            timeout = 3000;
            $item.find('[data-id="status"]').text(message);
            $item.removeClass('webuploader-item-success').addClass('webuploader-item-error');
        }

        if (isRemove) {
            setTimeout(function () {
                $item.fadeOut(300);
            }, timeout);
        }

        return this;
    };

    /**
     * 触发上传完成回调
     * @param fileUrl
     * @param extension
     * @return {AdminFileUpload}
     */
    AdminFileUpload.prototype.targetFile = function (fileUrl, extension) {
        if (typeof this.targetCallback === 'function') {
            if (false === this.targetCallback.call(this, fileUrl, extension)) {
                return this;
            }
        }

        if (!this.data.target) {
            return this;
        }

        var images = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
        if (-1 !== images.indexOf(extension.toLocaleLowerCase())) {
            $(this.data.target).each(function () {
                switch (this.tagName) {
                    case 'INPUT' :
                    case 'TEXTAREA' :
                        $(this).val(fileUrl);
                        break;
                    case 'BUTTON' :
                        $(this).text(fileUrl);
                        break;
                    case 'IMG' :
                        $(this).attr('src', fileUrl);
                        break;
                    case 'A' :
                        $(this).attr('href', fileUrl);
                        break;
                    default:
                        $(this).html('<img src="' + fileUrl + '"/>');
                }
            });
        } else {
            $(this.data.target).each(function () {
                switch (this.tagName) {
                    case 'INPUT' :
                    case 'TEXTAREA' :
                        $(this).val(fileUrl);
                        break;
                    case 'A' :
                        $(this).attr('href', fileUrl);
                        break;
                    default:
                        $(this).html(fileUrl);
                }
            });
        }

        return this;
    };

    /**
     * 触发描述回调
     * @return {AdminFileUpload}
     */
    AdminFileUpload.prototype.targetDesc = function () {
        if (typeof this.descTargetCallback === 'function') {
            if (false === this.descTargetCallback.call(this)) {
                return this;
            }
        }

        if (!this.data.descTarget || false === this.fileConfig) {
            return this;
        }

        var desc = $.parseUploaderDesc(this.fileConfig);
        $(this.data.descTarget).each(function () {
            if (!$(this).data('source')) {
                $(this).data('source', $(this).html());
            }

            $(this).html($(this).data('source') + desc);
        });

        return this;
    };

    /**
     * 绑定上传成功回调
     * @param callback
     * @return {AdminFileUpload}
     */
    AdminFileUpload.prototype.bindTarget = function (callback) {
        this.targetCallback = callback;
        return this;
    };

    /**
     * 绑定描述回调
     * @param callback
     * @return {AdminFileUpload}
     */
    AdminFileUpload.prototype.bindDescTarget = function (callback) {
        this.descTargetCallback = callback;
        return this;
    };

    /**
     * 刷新上传容器
     * @return {AdminFileUpload}
     */
    AdminFileUpload.prototype.resetUpload = function () {
        if (this.canUpload && this.uploader != null) {
            this.uploader.refresh();
        }
        return this;
    };

    /**
     * todo 重置
     * @return {AdminFileUpload}
     */
    AdminFileUpload.prototype.reset = function () {
        this.destroy();
        this.init();
        this.resetUpload();

        return this;
    };

    /**
     * todo 销毁
     * @return {AdminFileUpload}
     */
    AdminFileUpload.prototype.destroy = function () {
        if (this.canUpload) {
            this.uploader.destroy();
            $('#' + this.uploadQueueId).remove();
        }

        if (this.canSelect) {
            this.$selectElement.off('click');
            this.$selectElement.attr('disabled', true).prop('disabled', true).addClass('disabled');
        }

        $(this.data.target).prop('disabled', true).attr('disabled', true).addClass('disabled');
        $(this.data.descTarget).each(function () {
            $(this).html($(this).data('source'));
        });

        return this;
    };

    /**
     * 手动触发上传
     * @return {AdminFileUpload}
     */
    $.fn.adminFileUpload = function () {
        if (true === this.data('isAdminFileUpload')) {
            return this.data('AdminFileUpload');
        }

        var upload = new AdminFileUpload($(this));
        this.data('isAdminFileUpload', true);
        this.data('AdminFileUpload', upload);
        return upload;
    };

    /**
     * 附件选择器
     * @param markType
     * @param markValue
     * @param callback
     * @return {AdminFileSelect}
     */
    $.adminFileSelect = function (markType, markValue, callback) {
        return new AdminFileSelect(markType, markValue, callback);
    };

    /**
     * 解析Uploader Error 错误
     * @param reason
     * @return {string}
     */
    $.parseUploaderError = function (reason) {
        switch (reason) {
            case 'server'    :
                reason = '内部服务器发生错误.';
                break;
            case 'http'        :
                reason = '请求地址错误.';
                break;
            case 'abort'    :
                reason = '请求被阻止.';
                break;
            case 'timeout'    :
                reason = '请求超时.';
                break;
        }
        return reason;
    };

    /**
     * 解析Uploader Code 错误
     * @param code
     * @return {string}
     */
    $.parseUploaderCode = function (code) {
        switch (code) {
            case 'Q_EXCEED_NUM_LIMIT' :
                code = '文件总数量超出限制.';
                break;
            case 'Q_EXCEED_SIZE_LIMIT' :
                code = '文件总大小超出限制.';
                break;
            case 'Q_TYPE_DENIED' :
                code = '文件类型有误.';
                break;
            case 'F_EXCEED_SIZE' :
                code = '文件大小超出限制.';
                break;
            case 'F_DUPLICATE' :
                code = '选择文件重复.';
                break;
            default:
                code = '未知错误: ' + code;
        }
        return code;
    };

    /**
     * 解析上传描述
     * @param fileConfig
     * @return {string}
     */
    $.parseUploaderDesc = function (fileConfig) {
        // 缩图
        var desc = [];
        if (fileConfig.isThumb) {
            // 包含源文件
            if (fileConfig.hasSource) {
                desc.push('宽高不限, 系统会自动生成 <code>' + fileConfig.width + '*' + fileConfig.height + '</code>的缩略图');
            } else {
                if (fileConfig.width > 0 && fileConfig.height > 0) {
                    desc.push('宽高限制: <code>' + fileConfig.width + ' * ' + fileConfig.height + '</code>');
                } else if (fileConfig.width > 0) {
                    desc.push('宽高限制: <code>' + fileConfig.width + ' * 高不限</code>');
                } else if (fileConfig.height > 0) {
                    desc.push('宽高限制: <code>宽不限 * ' + fileConfig.height + '</code>');
                }
                desc.push('超出该尺寸系统会按照该尺寸进行缩放或裁剪');
            }
        }

        // 大小
        if (fileConfig.size > 0) {
            desc.push('大小限制: <code>' + WebUploader.Base.formatSize(fileConfig.size, 1, ['B', 'KB', 'MB', 'GB', 'TB']) + '</code>');
        } else {
            desc.push('大小不限');
        }

        // 限制格式
        if (fileConfig.suffix.length) {
            desc.push('格式限制: <code>' + fileConfig.suffix + '</code>');
        } else {
            desc.push('格式不限');
        }

        return desc.join(', ', desc);
    };

    // 自动触发
    $('[data-auto="file"]').each(function () {
        if ($(this).data('init') === 0) {
            return true;
        }

        $(this).adminFileUpload();
    });
})(jQuery, window);