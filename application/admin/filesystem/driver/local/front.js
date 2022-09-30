/**
 * 初始化异步回调
 * @param {{}} options 配置
 */
exports.asyncInit = function (options) {
}

/**
 * 异步上传文件前回调(还没有切割分块)
 * @param {busyAdmin.UploadFile} file 文件数据
 * @param {busyAdmin.UploadPrepareResult} result 准备上传返回数据结构
 */
exports.asyncBeforeSendFile = function (file, result) {
    this.options.server = result.server_url;
}

/**
 * 异步文件发送前回调(如果有分块，此时可以处理了)
 * @param {busyAdmin.UploadBlock} block 块数据
 */
exports.asyncBeforeSend = function (block) {

};

/**
 * 同步文件发送前回调(如果有分块，此时可以处理了)
 * @param {busyAdmin.UploadBlock} block 块数据
 * @param {{}} params HTTP参数
 * @param {{}} headers HTTP头
 */
exports.syncBeforeSend = function (block, params, headers) {
    var file   = block.file;
    var result = file.prepareResult;

    // 分块上传
    params.file_id = result.file_id;
    if (result.upload_id) {
        params.upload_id   = result.upload_id;
        params.part_number = block.chunk + 1;
    }
};

/**
 * 每一个分块或文件上传结果解析，返回false代表上传失败
 * @param {busyAdmin.UploadBlock} block 块数据
 * @param {{_raw: string}} response 响应内容
 * @param {(result: {}) => void} resultCallback 响应结果回调
 * @param {(errorMsg: string) => void} errorCallback 响应错误回调
 * @return {boolean}
 */
exports.syncUploadAccept = function (block, response, resultCallback, errorCallback) {
    var file   = block.file;
    var result = file.prepareResult;
    var status;
    busyAdmin.response.parse(response._raw, {
        url     : this.options.server,
        type    : this.options.method,
        data    : $.param(file._requestParams || {}),
        headers : file._requestHeaders || {}
    }, function (response) {
        status           = true;
        response._result = response.result;

        // 分块上传
        if (result.upload_id) {
            file.doneParts = file.doneParts || [];
            file.doneParts.push({
                part_number : block.chunk + 1,
                etag        : block.transport.getResponseHeaders().etag || ''
            });
        }
    }, function (response) {
        errorCallback(response.message);
        status = false;
    });

    return status;
}

/**
 * 异步所有文件上传完毕回调
 * @param {busyAdmin.UploadFile} file 文件数据
 * @param {{_raw: string, _result: {}|null}} result 响应内容
 */
exports.asyncAfterSendFile = function (file, result) {
}