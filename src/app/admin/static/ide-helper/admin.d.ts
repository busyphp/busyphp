declare namespace busyAdmin {
    //+======================================================================
    //+ 核心库名称
    //+======================================================================
    var sys: {
        /**
         * 核心对象
         */
        core: string,

        /**
         * jQuery库
         * @see https://jquery.com/
         */
        jquery: string,

        /**
         * Pace库
         * @see https://www.bootcdn.cn/pace/
         */
        pace: string,
    }


    //+======================================================================
    //+ 三方库名称
    //+======================================================================
    var lib: {
        artTemplate: "artTemplate"
        audio: "audio"
        autocomplete: "autocomplete"
        bootstrap: "bootstrap"
        bootstrapTable: "bootstrapTable"
        bootstrapTableExport: "bootstrapTableExport"
        bootstrapTableFixedColumns: "bootstrapTableFixedColumns"
        bootstrapTablePrint: "bootstrapTablePrint"
        bootstrapTableReorderColumns: "bootstrapTableReorderColumns"
        bootstrapTableReorderRows: "bootstrapTableReorderRows"
        bootstrapTableResizable: "bootstrapTableResizable"
        bootstrapTableStickyHeader: "bootstrapTableStickyHeader"
        bootstrapTableTree: "bootstrapTableTree"
        ckEditor5Classic: "ckEditor5Classic"
        clipboard: "clipboard"
        colorPicker: "colorPicker"
        depend: "depend"
        dragtable: "dragtable"
        font: "font"
        goog: "goog"
        highlight: "highlight"
        i18n: "i18n"
        image: "image"
        imageViewer: "imageViewer"
        jqueryUi: "jqueryUi"
        json: "json"
        layer: "layer"
        marked: "marked"
        md5: "md5"
        mdown: "mdown"
        moment: "moment"
        noext: "noext"
        propertyParser: "propertyParser"
        select2: "select2"
        socketio: "socketio"
        sortable: "sortable"
        sticky: "sticky"
        text: "text"
        toastr: "toastr"
        tree: "tree"
        treeTable: "treeTable"
        treegrid: "treegrid"
        ueditor: "ueditor"
        uploadConfig: "uploadConfig"
        validate: "validate"
        video: "video"
        vue: "vue"
        gwm: "gwm"
        webUploader: "webUploader"
        zeroClipboard: "zeroClipboard"
        ionRangeSlider: "ionRangeSlider"
        bootstrapStarRating: "bootstrapStarRating"
    };


    //+======================================================================
    //+ 事件名称
    //+======================================================================
    var e: {
        //+======================================================================
        //+ App
        //+======================================================================
        /**
         * 页面准备完成触发，此时基本元素还没有完全展示出来
         */
        appReady: string,
        /**
         * 应用销毁前执行
         */
        appBeforeDestroy: string,
        /**
         * 页面准备就绪触发，基本元素都已经完全展示出来
         */
        appReadies: string,
        /**
         * 页面离开前触发，单页返回false阻止离开页面，原生通过 修改 event.returnValue 阻止页面离开
         */
        routeBeforeLeave: string,
        /**
         * 单页发起请求前触发，返回false阻止请求
         */
        appReqBefore: string,
        /**
         * 单页请求完成触发，返回false阻止成功和失败执行
         */
        appReqComplete: string,
        /**
         * 单页请求错误触发，返回false阻止渲染错误页面
         */
        appReqError: string,
        /**
         * 单页请求成功触发，返回false阻止渲染页面
         */
        appReqSuccess: string,
        /**
         * 单页渲染完成前触发，此时原来的页面内容还未消失，返回false阻止继续渲染
         */
        appRenderBefore: string,
        /**
         * 单页渲染完毕触发，此时插件可能还没有准备完成
         */
        appRenderSuccess: string,
        /**
         * 单页渲染完成触发(所有插件准备完成后)
         */
        appComplete: string,
        /**
         * 插件准备完成触发
         */
        appPluginReady: string,
        /**
         * 退出登录成功
         */
        appLoginOuted: string,
        /**
         * 登录成功
         */
        appLoginSucceed: string,
        /**
         * 单页前进后退触发
         */
        routePopState: string,
        /**
         * 单页前进后退决定不调度后触发
         */
        routeNoDispatch: string,

        //+======================================================================
        //+ Request
        //+======================================================================
        /**
         * 请求前回调
         */
        requestBefore: string,
        /**
         * 请求完成回调
         */
        requestComplete: string,
        /**
         * 请求成功回调
         */
        requestSuccess: string,
        /**
         * 请求失败回调
         */
        requestError: string,

        //+======================================================================
        //+ Form
        //+======================================================================
        /**
         * 提交前验证事件，会在confirm对话框显示前触发
         */
        formCheck: string,
        /**
         * 请求前触发
         */
        formBefore: string,
        /**
         * 请求完成触发
         */
        formComplete: string,
        /**
         * 请求成功触发
         */
        formSuccess: string,
        /**
         * 请求失败触发
         */
        formError: string,

        //+======================================================================
        //+ Modal
        //+======================================================================
        /**
         * 模态框内容渲染前触发
         */
        modalRenderBefore: string,
        /**
         * 模态框准备完成，显示前触发
         */
        modalReady: string,
        /**
         * 模态框刚显示触发
         */
        modalShow: string,
        /**
         * 模态框完全显示出来触发
         */
        modalShown: string,
        /**
         * 模态框隐藏触发
         */
        modalHide: string,
        /**
         * 模态框完全隐藏触发
         */
        modalHided: string,
        /**
         * 确定按钮点击回调
         */
        modalOk: string,
        /**
         * 取消按钮点击回调
         */
        modalCancel: string,

        //+======================================================================
        //+ Table
        //+======================================================================
        /**
         * 当用选中一行时触发
         */
        tableCheck: string,
        /**
         * 选中所有行时触发
         */
        tableCheckAll: string,
        /**
         * 选中某些行时触发
         */
        tableCheckSome: string,
        /**
         * 点击单元格时触发
         */
        tableClickCell: string,
        /**
         * 点击行时触发
         */
        tableClickRow: string,
        /**
         * 单击详细信息图标折叠详细信息视图时触发
         */
        tableCollapseRow: string,
        /**
         * 当切换列可见时触发
         */
        tableColumnSwitch: string,
        /**
         * 双击单元格时触发
         */
        tableDblClickCell: string,
        /**
         * 双击行时触发
         */
        tableDblClickRow: string,
        /**
         * 单击详细信息图标以展开详细信息视图时触发
         */
        tableExpandRow: string,
        /**
         * 加载远程数据错误时触发
         */
        tableLoadError: string,
        /**
         * 加载远程数据成功时触发
         */
        tableLoadSuccess: string,
        /**
         * 更改页码或页面大小时触发
         */
        tablePageChange: string,
        /**
         * 在表格结构数据初始化完成后触发
         */
        tableDataInit: string,
        /**
         * 在表格主体准备完成后触发
         */
        tableBodyReady: string,
        /**
         * 在表格footer准备完成后触发
         */
        tableFooterReady: string,
        /**
         * 在表格header准备完成后触发
         */
        tableHeaderReady: string,
        /**
         * 在表格初始化完成之前触发
         */
        tablePreBody: string,
        /**
         * 刷新表格后触发
         */
        tableRefresh: string,
        /**
         * 刷新选项后初始化表之前触发
         */
        tableRefreshOptions: string,
        /**
         * 重置视图后触发
         */
        tableReset: string,
        /**
         * 搜索表时触发
         */
        tableSearch: string,
        /**
         * 滚动表格时触发
         */
        tableScroll: string,
        /**
         * 对列进行排序时触发
         */
        tableSort: string,
        /**
         * 切换表格视图时触发
         */
        tableToggle: string,
        /**
         * 取消选中时触发
         */
        tableUncheck: string,
        /**
         * 取消全部选中是触发
         */
        tableUncheckAll: string,
        /**
         * 取消选中某些行时触发
         */
        tableUncheckSome: string,
        /**
         * 表格表尾按钮点击事件
         */
        tableFooterBtnClick: string,


        //+======================================================================
        //+ Tree
        //+======================================================================
        /**
         * 所有事件绑定后触发
         */
        treeInit: string,
        /**
         * 加载开始之前触发
         */
        treeLoading: string,
        /**
         * 销毁前触发
         */
        treeDestroy: string,
        /**
         * 首次加载根节点后触发
         */
        treeLoaded: string,
        /**
         * 所有节点加载完毕后触发
         */
        treeReady: string,
        /**
         * 加载节点后触发
         */
        treeLoadNode: string,
        /**
         * 加载所有节点完成后触发
         */
        treeLoadAll: string,
        /**
         * 插入新数据时触发
         */
        treeInsert: string,
        /**
         * 节点重绘后触发
         */
        treeRedraw: string,
        /**
         * 当一个节点即将被打开时触发
         */
        treeBeforeOpen: string,
        /**
         * 打开节点时触发（如果有动画，则尚未完成）
         */
        treeOpenNode: string,
        /**
         * 当节点打开且动画完成时触发
         */
        treeAfterOpen: string,
        /**
         * 当节点关闭时触发（如果有动画还没有完成）
         */
        treeCloseNode: string,
        /**
         * 当节点关闭且动画完成时触发
         */
        treeAfterClose: string,
        /**
         * 执行openAll方法后触发
         */
        treeOpenAll: string,
        /**
         * 执行closeAll方法后触发
         */
        treeCloseAll: string,
        /**
         * 启用节点时触发
         */
        treeEnableNode: string,
        /**
         * 节点被禁用时触发
         */
        treeDisableNode: string,
        /**
         * 隐藏节点时触发
         */
        treeHideNode: string,
        /**
         * 显示节点时触发
         */
        treeShowNone: string,
        /**
         * 当所有节点都隐藏时触发
         */
        treeHideAll: string,
        /**
         * 显示所有节点时触发
         */
        treeShowAll: string,
        /**
         * 当用户点击或交互节点时触发
         */
        treeClickNode: string,
        /**
         * 当节点悬停时触发
         */
        treeHoverNode: string,
        /**
         * 当节点不再悬停时触发
         */
        treeDeHoverNode: string,
        /**
         * 选择节点时触发
         */
        treeSelectNode: string,
        /**
         * 选择更改时触发
         */
        treeChanged: string,
        /**
         * 取消选择节点时触发
         */
        treeDeSelectNode: string,
        /**
         * 选择所有节点时触发
         */
        treeSelectAll: string,
        /**
         * 取消选择所有节点时触发
         */
        treeDeSelectAll: string,
        /**
         * set_state完成时触发
         */
        treeSetState: string,
        /**
         * 刷新完成时触发
         */
        treeRefresh: string,
        /**
         * 节点刷新时触发
         */
        treeRefreshNode: string,
        /**
         * 当节点 id 值改变时触发
         */
        treeSetId: string,
        /**
         * 当节点文本值更改时触发
         */
        treeSetText: string,
        /**
         * 创建节点时触发
         */
        treeCreateNode: string,
        /**
         * 重命名节点时触发
         */
        treeRenameNode: string,
        /**
         * 删除节点时触发
         */
        treeDeleteNode: string,
        /**
         * 移动节点时触发
         */
        treeMoveNode: string,
        /**
         * 复制节点时触发
         */
        treeCopyNode: string,
        /**
         * 当节点被添加到缓冲区进行移动时触发
         */
        treeCut: string,
        /**
         * 当节点被添加到缓冲区进行复制时触发
         */
        treeCopy: string,
        /**
         * 调用粘贴时触发
         */
        treePaste: string,
        /**
         * 当复制/剪切缓冲区被清除时触发
         */
        treeClearBuffer: string,
        /**
         * 当节点的复选框被禁用时触发
         */
        treeDisableCheckbox: string,
        /**
         * 当节点的复选框被禁用时触发
         */
        treeEnableCheckbox: string,
        /**
         * 选中节点时触发（仅当复选框设置中的 tie_selection 为 false 时）
         */
        treeCheckNode: string,
        /**
         * 取消选中节点时触发（仅当复选框设置中的 tie_selection 为 false 时）
         */
        treeUnCheckNode: string,
        /**
         * 检查所有节点时触发（仅当复选框设置中的 tie_selection 为 false 时）
         */
        treeCheckAll: string,
        /**
         * 取消选中所有节点时触发（仅当复选框设置中的 tie_selection 为 false 时）
         */
        treeUnCheckAll: string,


        //+======================================================================
        //+ ColorPicker
        //+======================================================================
        /**
         * 创建颜色选择器时触发
         */
        colorPickerCreate: string,
        /**
         * 颜色选择器显示时触发
         */
        colorPickerShow: string,
        /**
         * 颜色选择器隐藏时触发
         */
        colorPickerHide: string,
        /**
         * 颜色改变时触发
         */
        colorPickerChange: string,
        /**
         * 颜色选择器禁用时触发
         */
        colorPickerDisable: string,
        /**
         * 颜色选择器启用时触发
         */
        colorPickerEnable: string,
        /**
         * 颜色选择器销毁时触发
         */
        colorPickerDestroy: string,

        //+======================================================================
        //+ FilePicker
        //+======================================================================
        /**
         * 文件选择完毕后触发
         */
        filePickerSuccess: string,

        //+======================================================================
        //+ Upload
        //+======================================================================
        /**
         * 上传按钮初始完毕后触发
         */
        uploadReady: string,
        /**
         * 文件校验不通过是出发
         */
        uploadFileError: string,
        /**
         * 文件加入列队触发
         */
        uploadFileQueued: string,
        /**
         * 一批文件加入列队触发
         */
        uploadFilesQueued: string,
        /**
         * 文件被移除队列后触发
         */
        uploadFileDequeued: string,
        /**
         * 上传流程开始时触发
         */
        uploadBegin: string,
        /**
         * 上传流程被暂停时触发
         */
        uploadStop: string,
        /**
         * 所有文件上传完毕时触发
         */
        uploadFinished: string,
        /**
         * 一个文件开始上传触发
         */
        uploadStart: string,
        /**
         * 上传完成触发
         */
        uploadComplete: string,
        /**
         * 上传进度触发
         */
        uploadProgress: string,
        /**
         * 上传成功触发
         */
        uploadSuccess: string,
        /**
         * 上传失败触发
         */
        uploadError: string,
        /**
         * 当uploader被重置的时候触发
         */
        uploadReset: string,
        /**
         * 文件上传成功或文件库选择后触发
         */
        uploadResult: string,
        /**
         * 内容值发生改变的时候触发
         */
        uploadChange: string,

        //+======================================================================
        //+ SelectPicker
        //+======================================================================
        /**
         * 每当选择或删除选项时触发
         */
        selectPickerChange: string,
        /**
         * 关闭前触发
         */
        selectPickerBeforeClose: string,
        /**
         * 关闭后触发
         */
        selectPickerClose: string,
        /**
         * 打开前触发
         */
        selectPickerBeforeOpen: string,
        /**
         * 打开后触发
         */
        selectPickerOpen: string,
        /**
         * 选择前触发
         */
        selectPickerBeforeSelect: string,
        /**
         * 选择后触发
         */
        selectPickerSelect: string,
        /**
         * 取消选择前触发
         */
        selectPickerBeforeUnselect: string,
        /**
         * 取消选择后触发
         */
        selectPickerUnselect: string,
        /**
         * 清空前触发
         */
        selectPickerBeforeClear: string,
        /**
         * 清空后触发
         */
        selectPickerClear: string,


        //+======================================================================
        //+ Shuttle
        //+======================================================================
        /**
         * 穿梭时回调
         */
        shuttleChange: string,


        //+======================================================================
        //+ DatePicker
        //+======================================================================
        /**
         * 日期控件显示完成触发
         */
        datePickerShow: string,
        /**
         * 日期控件隐藏完成触发
         */
        datePickerHide: string,
        /**
         * 点击非日期控件区域的时候触发
         */
        datePickerOutsideClick: string,
        /**
         * 日历显示完成时触发
         */
        datePickerShowCalendar: string,
        /**
         * 日历隐藏完成时触发
         */
        datePickerHideCalendar: string,
        /**
         * 点击确定时触发
         */
        datePickerApply: string,
        /**
         * 点击取消时触发
         */
        datePickerCancel: string,
        /**
         * 点击清空时触发
         */
        datePickerClear: string,
        /**
         * 触发显示日历的非input元素内容发生改变时触发
         */
        datePickerChange: string,

        //+======================================================================
        //+ Copy
        //+======================================================================
        /**
         * 复制成功触发
         */
        copySuccess: string,
        /**
         * 复制失败触发
         */
        copyError: string,
        /**
         * 解析触发
         */
        copyDecode: string,

        //+======================================================================
        //+ FormVerify
        //+======================================================================
        /**
         * 自定创建错误标签事件，返回false阻止系统创建标签
         */
        verifyErrorPlacement: string,

        //+======================================================================
        //+ iconPicker
        //+======================================================================
        /**
         * 选中图标
         */
        iconPickerSelect: string,
        /**
         * 清理图标
         */
        iconPickerClear: string,

        //+======================================================================
        //+ LinkagePicker
        //+======================================================================
        /**
         * 刚显示触发
         */
        linkagePickerShow: string,
        /**
         * 完全显示出来触发
         */
        linkagePickerShown: string,
        /**
         * 隐藏触发
         */
        linkagePickerHide: string,
        /**
         * 完全隐藏触发
         */
        linkagePickerHided: string,
        /**
         * 选择内容发生改变触发
         */
        linkagePickerChanged: string,
        /**
         * 内容被清空触发
         */
        linkagePickerClear: string,

        //+======================================================================
        //+ RangeSlider
        //+======================================================================
        /**
         * 滑块初始化时触发
         */
        rangeSliderStart: string,
        /**
         * 值改变时触发
         */
        rangeSliderChange: string,
        /**
         * 滑块完成时触发
         */
        rangeSliderFinish: string,
        /**
         * 更新时触发
         */
        rangeSliderUpdate: string,
    }

    /**
     * 生成事件名称
     */
    var ee: (name: string, group: string) => string;

    /**
     * 添加自动实例化的方法和选择器
     * @param {String} selector
     * @param {Function} callback
     */
    var autoInit: (selector: string, callback: (() => void)) => void;

    /**
     * 添加Require模块
     * @param name 模块名称
     * @param path 模块路径
     * @param shim 模块依赖
     * @return busyAdmin
     */
    var addMod: (name: string, path: string, shim?: any) => void;

    /**
     * 初始化
     * @param config 配置
     */
    var init: (config: {
        /**
         * 项目根地址
         */
        root: string,
        /**
         * 插件根地址
         */
        moduleRoot: string,
        /**
         * 版本号
         */
        version: string,

        /**
         * 应用配置
         */
        app: {
            /**
             * 应用信息URL
             */
            url: string,
            /**
             * 错误图片地址
             */
            errorImgUrl: string,
            /**
             * 是否保持同级导航只有一个展开
             */
            navSingleHold: boolean,
            /**
             * 是否开启多标签
             */
            multiPage: boolean,
            /**
             * 是否记录页面缓存
             */
            cache: boolean,
            /**
             * 记录页面缓存长度
             */
            cacheLength: number,
            /**
             * 滚动记录长度
             */
            scrollCacheLength: number
        }
    }) => void;

    /**
     * 准备完成回调
     */
    var ready: (callback: () => void) => void;

    /**
     * 获取项目跟地址
     */
    var root: () => string;

    /**
     * 获取模块根目录
     */
    var moduleRoot: () => string;

    /**
     * 获取Skin根目录
     */
    var skinRoot: () => string;

    /**
     * 获取CSS根目录
     */
    var cssRoot: () => string;

    /**
     * 获取JS根目录
     */
    var jsRoot: () => string;

    /**
     * 获取images根目录
     */
    var imagesRoot: () => string;

    /**
     * 获取配置
     */
    var getConfig: (key: string, defaults: any) => any;

    /**
     * Require
     */
    var require: (modules: [string], callback: () => void) => void;

    /**
     * Define
     */
    var define: (name: string | [string] | (() => void), modules?: [string] | (() => void), callback?: () => void) => void;


    /**
     * 语言包文件
     */
    var locale: {
        plugin: {}
    };

    /**
     * 组件
     */
    var plugins: {
        Audio: object,
        AudioViewer: object,
        Autocomplete: object,
        Catalog: object,
        ChangeVerify: object,
        CheckboxRadio: object,
        ColorPicker: object,
        Copy: object,
        DatePicker: object,
        Editor: object,
        FilePicker: object,
        Form: object,
        High: object,
        IconPicker: object,
        ImageViewer: object,
        Markdown: object,
        Modal: object,
        Request: object,
        SearchBar: object,
        SelectPicker: object,
        Shuttle: object,
        Table: object,
        Tree: object,
        Upload: object,
        Verify: object,
        VideoViewer: object,
        Random: object,
        LinkagePicker: object,
    };

    /**
     * 自定义数据
     */
    var data: {};

    //+======================================================================
    //+ App
    //+======================================================================
    var app: BusyAdminApp


    interface BusyAdminFrame {
        /** 自身jQuery对象 */
        target: any,
        /** 宽度 */
        width: number,
        /** 高度 */
        height: number,
        /** 距离顶部 */
        top: number,
        /** 距离左侧 */
        left: number,
        /** 左填充 */
        paddingLeft: number,
        /** 右填充 */
        paddingRight: number,
        /** 上填充 */
        paddingTop: number,
        /** 下填充 */
        paddingBottom: number,
    }

    interface BusyAdminApp {
        /**
         * 准备完成
         * @param callback
         */
        ready(callback: (() => any));

        /**
         * 关闭侧边栏导航
         */
        closeSideNav();

        /**
         * 打开侧边栏导航
         */
        openSideNav();

        /**
         * 设为迷你版菜单
         * @param once 是否只展开一次，默认不是
         */
        setToMiniNav(once?: boolean);

        /**
         * 设为默认版菜单
         */
        setToDefaultNav();

        /**
         * 获取内容区域frame
         */
        getContentFrame(): BusyAdminFrame;

        /**
         * 验证某个jQuery节点是否在Content中
         * @param $element
         */
        isInContent($element: any): boolean;

        /**
         * 分析页面内容
         */
        extractContainer(data: string): {
            /**
             * 页面标题
             */
            title: string,
            /**
             * 应用标题
             */
            appTitle: string,
            /**
             * 主内容
             */
            content: object,
            /**
             * 主内容面包屑
             */
            contentHeader: object
        };

        /**
         * 获取跟路径
         */
        getRoot(): string;

        /**
         * 通过URL获取页面PATH
         * @param url
         */
        getPath(url: string): string;

        /**
         * 进去全屏模式
         */
        requestFullscreen();

        /**
         * 退出全屏模式
         */
        exitFullscreen();

        /**
         * 是否进入了全屏模式
         */
        isFullscreen(): boolean;

        /**
         * 响应内容转HTML
         * @param response
         * @param callback
         */
        responseToHtml(response: BusyAdminResponseResult, callback: ((html: string) => void));

        /**
         * 是否迷你导航
         */
        isMiniNav(): boolean;

        /**
         * 将导航设为高亮
         * @param path
         */
        setNavActive(path: string);

        /**
         * 将菜单滚动至选中项位置
         */
        scrollToNav();

        /**
         * 移动设备导航栏是否打开
         */
        sideIsOpen(): boolean;

        /**
         * 是否移动设备尺寸
         */
        isMobileSize(): boolean;

        /**
         * 重新加载应用信息
         */
        loadInfo(): void;

        /**
         * 触发窗口刷新
         */
        triggerResize(): void;

        /**
         * 获取用户ID
         */
        getUserId(): number;

        /**
         * 获取用户名
         */
        getUsername(): string;

        /**
         * 获取用户信息
         */
        getUserInfo(): BusyAdminUserInfo;

        /**
         * 获取APP全局信息
         */
        getData(): BusyAdminAppData;

        /**
         * 获取APP自定义数据
         */
        getUseData(): {};

        /**
         * 触发全局事件
         * @param event 事件名称
         * @param args 参数
         */
        trigger(event: string, args?: any): BusyAdminApp;

        /**
         * 监听全局事件
         * @param event
         * @param callback
         */
        on(event: string, callback: (() => void)): BusyAdminApp;

        /**
         * 监听一次全局事件
         * @param event
         * @param callback
         */
        once(event: string, callback: (() => void)): BusyAdminApp;

        /**
         * 卸载监听全局事件
         * @param event
         */
        off(event: string): BusyAdminApp;

        /**
         * 是否登录
         */
        isLogin(): boolean;
    }

    interface BusyAdminAppData {
        user_id: number,
        username: string,
        menu_default: string,
        menu_list: [BusyAdminMenuItem],
        message_agency: boolean,
        message_notice: boolean,
        user: BusyAdminUserInfo,
        user_dropdowns: [{
            attr: [object],
            icon: string,
            text: string,
        }],
        data: {}
    }

    interface BusyAdminUserInfo {
        id: number,
        username: string,
        phone: string,
        email: string,
        qq: string,
        group_names: [string],
        group_rule_ids: [number],
        group_rule_paths: [string],
        system: boolean,
        theme: {
            nav_mode: number,
            nav_single_hold: number,
            skin: string,
        },
    }

    interface BusyAdminMenuItem {
        disabled: boolean,
        hash: string,
        hide: boolean,
        hides: [string],
        icon: string,
        id: number,
        name: string,
        param_list: [string],
        params: string,
        parent_hash: string,
        parent_path: string,
        path: string,
        sort: number,
        system: boolean,
        target: string,
        top_path: string,
        top_url: string,
        url: string,
        child: [BusyAdminMenuItem]
    }


    //+======================================================================
    //+ HTTP响应部分
    //+======================================================================
    var response: BusyAdminResponse;

    /**
     * 响应结构
     */
    interface BusyAdminResponseResult {
        /** 状态 */
        status: boolean,
        /** 消息 */
        message: string,
        /** 跳转地址 */
        url: string,
        /** 错误代码 */
        code: number,
        /** 返回内容 */
        result: any
    }

    /**
     * 响应数据解析器回调
     * @param response 请求返回的内容
     */
    type BusyAdminResponseParseCallback = (response: string) => BusyAdminResponseResult;

    /**
     * 绑定错误代码回调 返回false阻止继续向下传递参数
     * @param response 响应数据结构
     */
    type BusyAdminResponseBindCallback = (response: BusyAdminResponseResult) => boolean;

    /**
     * 响应回调
     * @param response 响应数据结构
     * @param type 响应类型
     * @return boolean 返回false阻止向上执行
     */
    type BusyAdminResponseCallback = (response: BusyAdminResponseResult, type: number) => boolean;


    interface BusyAdminResponse {
        /**
         * 错误类型
         */
        type: {
            /** 请求成功 */
            success: 0,
            /** HTTP错误 */
            http: 1,
            /** 解析错误 */
            parse: 2,
            /** 逻辑错误 */
            logic: 3
        },

        /**
         * 响应返回结构
         */
        result: BusyAdminResponseResult

        /**
         * 执行解析数据
         * @param response 响应内容
         * @param ajaxSettings jQuery ajax settings
         * @param success 成功回调
         * @param error 失败回调
         * @param complete 完成回调，比成功失败优先执行，返回false阻止触发成功失败回调
         */
        parse(response: string | object, ajaxSettings?: object, success?: BusyAdminResponseCallback, error?: BusyAdminResponseCallback, complete?: BusyAdminResponseCallback);

        /**
         * 设置JSON数据解析回调
         * @param callback
         */
        setParse(callback: BusyAdminResponseParseCallback): BusyAdminResponse;

        /**
         * 绑定消息代码回调
         * @param code 消息代码
         * @param callback 回调方法，返回false阻止往下传递
         */
        bindCode(code: number, callback: BusyAdminResponseBindCallback): BusyAdminResponse;
    }


    //+======================================================================
    //+ HTTP请求部分
    //+======================================================================

    /**
     * 实例化一个请求
     * @param url 请求的URL地址
     */
    function request(url?: string): BusyAdminRequest;


    /**
     * 请求回调
     * @param json 返回数据结构
     * @param type 状态类型，参考{@see BusyAdminResponse.type}
     * @param xhr jQueryXHR 对象
     * @return boolean 返回false阻止向上执行
     */
    type BusyAdminRequestCallback = (response: BusyAdminResponseResult, type: number, xhr: object) => boolean | null;

    /**
     * 请求进度回调
     * @param event 进度对象
     * @return boolean 返回false阻止向上执行
     */
    type BusyAdminRequestProgressCallback = (event: ProgressEvent) => boolean | null;


    interface BusyAdminRequestTask {
        /**
         * 中断请求
         */
        abort(): void;
    }


    interface BusyAdminRequest {

        /**
         * 设置请求URL，不设置则获取当前网址
         * @param url
         */
        url(url: string): BusyAdminRequest;

        /**
         * 设置是否进行POST请求
         * @param status
         */
        post(status: boolean): BusyAdminRequest;

        /**
         * 设置请求方式
         * @param method 支持GET|POST等方式
         */
        method(method: string): BusyAdminRequest;

        /**
         * 设置请求头
         * @param params
         */
        headers(params: { [key: string]: any }): BusyAdminRequest;

        /**
         * 添加请求头
         * @param name 请求头名称
         * @param value 请求头值
         */
        addHeader(name: string, value: any): BusyAdminRequest;

        /**
         * 设置请求参数
         * @param params
         */
        params(params: { [key: string]: any }[]): BusyAdminRequest;

        /**
         * 添加请求参数
         * @param name 参数名称
         * @param value 参数内容
         */
        addParam(name: string, value: any): BusyAdminRequest;

        /**
         * 是否显示加载等待窗口
         * @param status 是否显示或显示的消息
         * @param message 显示的消息
         */
        pending(status: boolean | string, message?: string): BusyAdminRequest;

        /**
         * 设置GET请求是否缓存，默认不缓存
         * @param status
         */
        cache(status: boolean): BusyAdminRequest;


        /**
         * 清理请求参数
         */
        clear(): BusyAdminRequest;

        /**
         * 设置解析的数据格式
         * @param parse 支持jQuery的各种解析格式，不指定或为空，默认使用 {@link BusyAdminResponse} 解析
         * @param exec 解析执行方法，如解析类型是jsonp，着传入jsonp的callback方法明
         */
        parse(parse: string, exec?: string): BusyAdminRequest;

        /**
         * 请求前回调
         * @param callback 返回false阻止请求
         */
        before(callback: (jQueryXhr: object, ajaxSettings: object) => boolean | void): BusyAdminRequest;

        /**
         * 成功回调
         * @param callback 返回false阻止自动解析成功逻辑
         */
        success(callback: BusyAdminRequestCallback): BusyAdminRequest;

        /**
         * 失败回调
         * @param callback 返回false阻止自动解析失败逻辑
         */
        error(callback: BusyAdminRequestCallback): BusyAdminRequest;

        /**
         * 失败回调
         * @param callback 返回false阻止执行 {@link BusyAdminRequest.success()} {@link BusyAdminRequest.error()}
         */
        complete(callback: BusyAdminRequestCallback): BusyAdminRequest;

        /**
         * 失败回调
         * @param callback 发挥false阻止默认进度处理
         */
        progress(callback: BusyAdminRequestProgressCallback): BusyAdminRequest;

        /**
         * 执行请求
         */
        exec(): BusyAdminRequestTask;
    }


    //+======================================================================
    //+ 对话框部分
    //+======================================================================


    /**
     * 对话框
     */
    var dialog: BusyAdminDialog;

    /**
     * iFrame内初始化dialog
     */
    var dialogIframeReady: (callback: BusyAdminDialogIframeReadyCallback) => void;

    /**
     * iFrame内初始化dialog回调
     */
    type BusyAdminDialogIframeReadyCallback = (index: number) => void;

    /**
     * 通用对话框回调
     * @param index 对话框索引，可以通过 {@link layer.close()} 关闭对话框
     * @param layerApi layer接口对象，参考 {@link layer}
     * @param element jQuery对话框节点对象
     */
    type BusyAdminDialogCallback = (index: number, layerApi: object, element: object) => void;

    /**
     * Prompt输入对话框确定回调
     * @param value 输入的值
     * @param index 对话框索引，可以通过 {@link layer.close()} 关闭对话框
     * @param layerApi layer接口对象，参考 {@link layer}
     * @param input jQuery输入框节点对象
     * @param element jQuery对话框节点对象
     */
    type BusyAdminDialogPromptCallback = (value: string | [string], index: number, layerApi: object, input: object, element: object) => void;

    /**
     * 对话框关闭回调
     */
    type BusyAdminDialogCloseCallback = () => void;


    /**
     * 对话框配置选项
     */
    type LayerOptions = {
        /**
         * 提供了5种层类型。
         * - 可传入的值有：
         * - 0（信息框，默认）
         * - 1（页面层）
         * - 2（iframe层）
         * - 3（加载层）
         * - 4（tips层）
         */
        type?: 0 | 1 | 2 | 3 | 4,

        /**
         * - title支持三种类型的值，
         * - 若你传入的是普通的字符串，如title :'我是标题'，那么只会改变标题文本；<br />
         * - 若你还需要自定义标题区域样式，那么你可以title: ['文本', 'font-size:18px;']，数组第二项可以写任意css样式；<br />
         * - 如果你不想显示标题栏，你可以title: false<br />
         */
        title?: string | [string] | false,

        /**
         * 内容
         * - content可传入的值是灵活多变的，不仅可以传入普通的html内容，还可以指定DOM，更可以随着type的不同而不同
         * - 如果是iframe层，这里content是一个URL，如果你不想让iframe出现滚动条，你还可以content: ['http://sentsin.com', 'no']
         * - 如果是用layer.open执行tips层 ['内容', '#id'] //数组第二项即吸附元素选择器或者DOM
         * - 一个DOM，如：$('#id')，或者 document.getElementById('id') 注意：最好该元素要存放在body最外层，否则可能被其它的相对元素所影响
         * - 传入任意的文本或html
         */
        content?: string | HTMLElement | [string],

        /**
         * 样式类名
         * skin不仅允许你传入内置的样式class名，还可以传入您自定义的class名。这是一个很好的切入点，意味着你可以借助skin轻松完成不同的风格定制。
         * 目前内置的skin有：layui-layer-lanlayui-layer-molv，未来我们还会选择性地内置更多
         */
        skin?: string,

        /**
         * 宽高
         */
        area?: "auto" | "t" | "r" | "b" | "l" | "lt" | "lb" | "rt" | "rb" | string | [string],

        /**
         * 坐标
         * - 默认：垂直水平居中
         * - 但如果你不想垂直水平居中，你还可以进行以下赋值：
         * - 只定义top坐标，水平保持居中，如：'100px'
         * - 同时定义top、left坐标，如：['100px', '50px']
         * - auto 默认坐标，即垂直水平居中
         * - t 快捷设置顶部坐标
         * - r 快捷设置右边缘坐标
         * - b 快捷设置底部坐标
         * - l 快捷设置左边缘坐标
         * - lt 快捷设置左上角
         * - lb 快捷设置左下角
         * - rt 快捷设置右上角
         * - rb 快捷设置右下角
         */
        offset?: "auto" | "t" | "r" | "b" | "l" | "lt" | "lb" | "rt" | "rb" | string | [string],

        /**
         * 图标。信息框和加载层的私有参数
         * 默认：-1（信息框）/0（加载层）
         * 信息框默认不显示图标。当你想显示图标时，
         * - 默认皮肤可以传入0-6
         * - 如果是加载层，可以传入0-2
         */
        icon?: -1 | 0 | 1 | 2 | 3 | 4 | 5 | 6,

        /**
         * 按钮
         * 默认：'确认'
         * 信息框模式时，btn默认是一个确认按钮，其它层类型则默认不显示，加载层和tips层则无效。
         * - 当您只想自定义一个按钮时，你可以btn: '我知道了'，
         * - 当你要定义两个按钮时，你可以btn: ['yes', 'no']。
         * - 当然，你也可以定义更多按钮，比如：btn: ['按钮1', '按钮2', '按钮3', …]，
         * - 按钮1的回调是yes，而从按钮2开始，则回调为btn2: function(){}，以此类推
         */
        btn?: string | [string],

        /**
         * 按钮排列
         * - l 按钮左对齐
         * - r 按钮居中对齐
         * - c 按钮右对齐。默认值，不用设置
         */
        btnAlign?: "l" | "c" | "r",

        /**
         * 提供了两种风格的关闭按钮，可通过配置1和2来展示，如果不显示，则closeBtn: 0
         */
        closeBtn?: number | false,

        /**
         * 即弹层外区域。
         * - 默认是0.3透明度的黑色背景（'#000'）。
         * - 如果你想定义别的颜色，可以shade: [0.8, '#393D49']；
         * - 如果你不想显示遮罩，可以shade: 0
         */
        shade?: string | number | [any] | false,

        /**
         * 是否点击遮罩关闭
         */
        shadeClose?: boolean,

        /**
         * 自动关闭所需毫秒
         * - 默认不会自动关闭。当你想自动关闭时，可以time: 5000，即代表5秒后自动关闭，注意单位是毫秒（1秒=1000毫秒）
         */
        time?: number,

        /**
         * 用于控制弹层唯一标识
         * - 设置该值后，不管是什么类型的层，都只允许同时弹出一个。一般用于页面层和iframe层模式
         */
        id?: string,

        /**
         * 弹出动画
         * - anim: 0    平滑放大。默认
         * - anim: 1    从上掉落
         * - anim: 2    从最底部往上滑入
         * - anim: 3    从左滑入
         * - anim: 4    从左翻滚
         * - anim: 5    渐显
         * - anim: 6    抖动
         */
        anim?: 0 | 1 | 2 | 3 | 4 | 5 | 6,

        /**
         * 关闭动画
         * - 默认情况下，关闭层时会有一个过度动画。如果你不想开启，设置 isOutAnim: false 即可
         */
        isOutAnim?: boolean,

        /**
         * 最大最小化
         * - 该参数值对type:1和type:2有效。默认不显示最大小化按钮。需要显示配置maxmin: true即可
         */
        maxmin?: boolean,

        /**
         * 固定
         * - 即鼠标滚动时，层是否固定在可视区域。如果不想，设置fixed: false即可
         */
        fixed?: boolean,

        /**
         * 是否允许拉伸
         * - 默认情况下，你可以在弹层右下角拖动来拉伸尺寸。如果对指定的弹层屏蔽该功能，设置 false即可。该参数对loading、tips层无效
         */
        resize?: boolean,

        /**
         * 监听窗口拉伸动作
         * - 当你拖拽弹层右下角对窗体进行尺寸调整时，如果你设定了该回调，则会执行。回调返回一个参数：当前层的DOM对象
         */
        resizing?: (dom: HTMLElement) => void,

        /**
         * 是否允许浏览器出现滚动条
         */
        scrollbar?: boolean,

        /**
         * 最大宽度
         * - 只有当area: 'auto'时，maxWidth的设定才有效。
         */
        maxWidth?: number,

        /**
         * 最大高度
         * - 只有当高度自适应时，maxHeight的设定才有效。
         */
        maxHeight?: number,

        /**
         * 层叠顺序
         * - 默认：19891014
         */
        zIndex?: number,

        /**
         * 触发拖动的元素
         * - 默认是触发标题区域拖拽。如果你想单独定义，指向元素的选择器或者DOM即可。如move: '.mine-move'。你还配置设定move: false来禁止拖拽
         */
        move?: string | HTMLElement | false,

        /**
         * 是否允许拖拽到窗口外
         */
        moveOut?: boolean,

        /**
         * 拖动完毕后的回调方法
         */
        moveEnd?: (dom: HTMLElement) => void,

        /**
         * tips方向和颜色
         * - tips层的私有参数。支持上右下左四个方向，通过1-4进行方向设定。如tips: 3则表示在元素的下面出现。有时你还可能会定义一些颜色，可以设定tips: [1, '#c00']
         */
        tips?: 1 | 2 | 3 | 4 | [any],

        /**
         * 是否允许多个tips
         * - 允许多个意味着不会销毁之前的tips层。通过tipsMore: true开启
         */
        tipsMore?: boolean,

        /**
         * 层弹出后的成功回调方法
         * @param dom
         * @param index
         */
        success?: (dom: HTMLElement, index: number) => void,

        /**
         * 确定按钮回调方法
         * @param dom
         * @param index
         */
        yes?: (dom: HTMLElement, index: number) => void,

        /**
         * 右上角关闭按钮触发的回调
         * @param dom
         * @param index
         */
        cancel?: (dom: HTMLElement, index: number) => void,

        /**
         * 无论是确认还是取消，只要层被销毁了，end都会执行，不携带任何参数。
         */
        end?: () => void,

        /**
         * 最大化后触发的回调
         * @param dom
         * @param index
         */
        full?: (dom: HTMLElement, index: number) => void,

        /**
         * 最小化后触发的回调
         * @param dom
         * @param index
         */
        min?: (dom: HTMLElement, index: number) => void,

        /**
         * 还原 后触发的回调
         * @param dom
         * @param index
         */
        restore?: (dom: HTMLElement, index: number) => void,

        /**
         * 是否默认堆叠在左下角
         */
        minStack?: boolean,

        /**
         * Prompt输入对话框的input类型
         */
        formType?: BusyAdminDialogSelectType,

        /**
         * Prompt输入对话框的select选项
         */
        selectOptions?: [{ value: string, name: string }] | [string] | string,

        /**
         * Prompt输入对话框的select是否多选以及显示行数控制或者textarea的行数控制
         */
        rows?: number,

        /**
         * Prompt输入对话框的select选中值
         */
        value?: string,

        /**
         * Prompt输入对话框的placeholder占位
         */
        placeholder?: string,

        /**
         * Prompt输入对话框的步进值
         */
        numberStep: number,

        /**
         * Prompt输入对话框的最大值
         */
        maxlength: number
        /**
         * Prompt输入对话框的最小值
         */
        minlength: number
    }

    type BusyAdminDialogSelectType =
        "select"
        | "textarea"
        | "password"
        | "text"
        | "number"
        | "tel"
        | "email"
        | "url"
        | string;

    /**
     * 对话框图标类型
     */
    type BusyAdminDialogIcon =
        "success"
        | "s"
        | "y"
        | "yes"
        | "error"
        | "e"
        | "n"
        | "no"
        | "a"
        | "ask"
        | "q"
        | "question"
        | "l"
        | "lock"
        | "f"
        | "frown"
        | "sm"
        | "smile"
        | "w"
        | "warn"
        | "warning";

    interface BusyAdminDialog {
        /**
         * Alert弹窗
         * @param message 消息内容
         * @param type 消息图标或layer配置或成功回调
         * @param ok 成功回调
         */
        alert(message: string, type?: BusyAdminDialogIcon | LayerOptions | null | BusyAdminDialogCallback, ok?: BusyAdminDialogCallback): number;

        /**
         * 成功Alert
         * @param message 消息
         * @param ok 回调
         */
        alertSuccess(message: string, ok?: BusyAdminDialogCallback): number;

        /**
         * 失败Alert
         * @param message 消息
         * @param ok 回调
         */
        alertError(message: string, ok?: BusyAdminDialogCallback): number;

        /**
         * Confirm弹窗
         * @param message 消息内容
         * @param type 消息类型或确定回调或配置
         * @param ok 确定回调或取消回调
         * @param cancel 取消回调
         */
        confirm(message: string, type?: BusyAdminDialogIcon | LayerOptions | null | BusyAdminDialogCallback, ok?: BusyAdminDialogCallback, cancel?: BusyAdminDialogCallback): number;

        /**
         * Prompt输入对话框
         * @param title 输入提示
         * @param value 默认内容
         * @param type 输入框类型 或 确定回调 或 配置
         * @param ok 确定回调或取消回调
         * @param cancel 取消回调
         */
        prompt(title: string, value: string | [string], type: BusyAdminDialogSelectType | LayerOptions | BusyAdminDialogPromptCallback, ok: BusyAdminDialogPromptCallback | BusyAdminDialogCallback, cancel?: BusyAdminDialogCallback): number;

        /**
         * Toast提示
         * @param message 消息
         * @param long 是否长提示 或 配置 或 关闭回调
         * @param close 关闭回调
         */
        toast(message: string, long?: boolean | LayerOptions | BusyAdminDialogCloseCallback, close?: BusyAdminDialogCloseCallback): number;

        /**
         * 成功Toast提示
         * @param message 消息
         * @param close 关闭回调
         */
        toastSuccess(message: string, close?: BusyAdminDialogCloseCallback): number;

        /**
         * 失败Toast提示
         * @param message 消息
         * @param close 关闭回调
         */
        toastError(message: string, close?: BusyAdminDialogCloseCallback): number;

        /**
         * 加载中对话框
         * @param message
         */
        pending(message: string): BusyAdminDialogPending;

        /**
         * 打开iFrame对话框
         * @param url 网址 或 数组[标题,网址]
         * @param size 宽高数组 或 关闭回调 或 配置
         * @param close 关闭回调
         */
        iframe(url: string | [string], size: [number] | LayerOptions | BusyAdminDialogIframeCloseCallback, close?: BusyAdminDialogIframeCloseCallback): BusyAdminDialogIframe;

        /**
         * 打开一个右侧边栏对话框
         * @param content 页面内容
         * @param className 对话框类名或配置
         * @param success 对话框显示回调
         * @param close 对话框关闭回调
         */
        fullRight(content: string, className: string | LayerOptions, success?: BusyAdminDialogCallback, close?: BusyAdminDialogCloseCallback): BusyAdminDialogPage;

        /**
         * 成功通知
         * @param title 消息标题或消息内容
         * @param message 消息内容或配置或关闭回调
         * @param options 配置或关闭回调
         */
        notifySuccess(title: string, message?: string | BusyAdminNotifyOptions | (() => void), options?: object | BusyAdminNotifyOptions | (() => void));

        /**
         * 失败通知
         * @param title 消息标题或消息内容
         * @param message 消息内容或配置或关闭回调
         * @param options 配置或关闭回调
         */
        notifyError(title: string, message?: string | BusyAdminNotifyOptions | (() => void), options?: object | BusyAdminNotifyOptions | (() => void));

        /**
         * 警告通知
         * @param title 消息标题或消息内容
         * @param message 消息内容或配置或关闭回调
         * @param options 配置或关闭回调
         */
        notifyWarning(title: string, message?: string | BusyAdminNotifyOptions | (() => void), options?: object | BusyAdminNotifyOptions | (() => void));

        /**
         * 信息通知
         * @param title 消息标题或消息内容
         * @param message 消息内容或配置或关闭回调
         * @param options 配置或关闭回调
         */
        notifyInfo(title: string, message?: string | BusyAdminNotifyOptions | (() => void), options?: object | BusyAdminNotifyOptions | (() => void));
    }

    interface BusyAdminNotifyOptions {
        /**
         * 是否显示关闭按钮
         */
        closeButton: boolean,
        /**
         * 方向
         */
        positionClass: "toast-top-right" | "toast-bottom-right" | "toast-bottom-left" | "toast-top-left" | "toast-top-full-width" | "toast-bottom-full-width" | "toast-top-center" | "toast-bottom-center",
        /**
         * 是否只保留一个
         */
        preventDuplicates: boolean,
        /**
         * 保留时长
         */
        timeOut: number,
        /**
         * 显示完成回调
         */
        onShown: () => void,
        /**
         * 隐藏完成回调
         */
        onHidden: () => void,
        /**
         * 点击回调
         */
        onclick: () => void,
        /**
         * 关闭按钮点击回调
         */
        onCloseClick: () => void,
    }

    interface BusyAdminDialogPage {
        /**
         * 关闭对话框
         */
        close(): void;
    }

    /**
     * 关闭回调
     */
    type BusyAdminDialogIframeCloseCallback = (data: any) => void;

    interface BusyAdminDialogIframe {
        /**
         * 设置关闭对话框传值
         * @param data
         */
        setData(data: any): BusyAdminDialogIframe;

        /**
         * 设置对话框标题
         * @param title
         */
        setTitle(title: string): BusyAdminDialogIframe;

        /**
         * 设置对话框大小
         * @param width
         * @param height
         */
        setSize(width: any, height: any): BusyAdminDialogIframe;

        /**
         * 设置对话框宽度
         * @param width
         */
        setWidth(width: any): BusyAdminDialogIframe;

        /**
         * 设置对话框高度
         * @param height
         */
        setHeight(height: any): BusyAdminDialogIframe;

        /**
         * 更新对话框
         */
        update(): BusyAdminDialogIframe;


        /**
         * 获取layer对话框接口
         */
        getLayerApi(): object;


        /**
         * 关闭对话框
         */
        close(): void;
    }

    interface BusyAdminDialogPending {
        /**
         * 更新消息内容
         * @param message
         */
        update(message: string): BusyAdminDialogPending;

        /**
         * 关闭
         */
        close();
    }

    //+======================================================================
    //+ helper部分
    //+======================================================================
    var helper: BusyAdminHelper;

    interface BusyAdminHelperParseUrlResult {
        /**
         * 原URL
         */
        source: string;
        /**
         * 协议
         */
        protocol: string;
        /**
         * 域名
         */
        host: string;
        /**
         * 端口号
         */
        port: number;
        /**
         * query字符串
         */
        query: string;
        /**
         * 文件名
         */
        filename: string;
        /**
         * #号后面的内容
         */
        hash: string;
        /**
         * 路径,不包含hash和query
         */
        path: string;
        /**
         * 路径,不包含后缀
         */
        pathNoSuffix: string;
        /**
         * 不包含域名的路径
         */
        relative: string;
        /**
         * 路径分段
         */
        segments: [string];
        /**
         * 参数结构
         */
        params: { [key: string]: any }[];
    }

    interface BusyAdminHelper {
        /**
         * 解析URL
         * @param url
         */
        parseURL(url: string): BusyAdminHelperParseUrlResult;

        /**
         * 检测是否URL
         * @param url
         */
        checkURL(url: string): boolean;

        /**
         * 获取值
         * @param data object对象
         * @param key 键名称
         * @param defaults 默认值
         * @param checkEmpty 是否检测为空，如果为空则输出默认值
         */
        getObjectValue(data: object, key: string | number, defaults?: any, checkEmpty?: boolean): any;

        /**
         * 监听DOM结构发生改变
         * @param callback
         */
        onDomChange(callback: () => void): void;

        /**
         * 解析data
         * @param data 要转换的数据
         * @param prefix 要去除的前缀
         * @param filter 要保留的前缀
         */
        parseData(data: object, prefix: string | [string], filter?: string | [string]): object;

        /**
         * 生成UUID
         */
        uuid(): string;

        /**
         * 转义HTML
         * @param html
         */
        htmlEncode(html: string): string;

        /**
         * 还原转义的HTML
         * @param content
         */
        htmlDecode(content: string): string;

        /**
         * Base64加密
         * @param content
         */
        base64Encode(content: string): string;

        /**
         * Base64解密
         * @param content
         */
        base64Decode(content: string): string;

        /**
         * 执行回调
         * @param callback 回调方法或window全局方法
         * @param context 上下文
         * @param args 附加参数
         * @param $target jQuery对象，设置后触发jQuery事件
         * @param event jQuery事件名称 或 {@see $.Event} 对象
         * @param prevent 是否阻止callback传递，默认不阻止
         * @param preventResult 回调返回什么内容才阻止callback传递
         * @param preventResultIsType 返回的内容按照类型拦截
         */
        execCallback(callback: string | (() => any), context?: any, args?: [any], $target?: any, event?: string, prevent?: Boolean, preventResult?: any, preventResultIsType?: boolean): any;

        /**
         * 强制转为Int数值
         * @param number
         */
        int(number: any): number;

        /**
         * 强制转为Float数值
         * @param number
         */
        float(number: any): number;

        /**
         * 通过jQuery选择器获取对应的jQuery对象
         * @param selector
         */
        getElementBySelector(selector: string): any;

        /**
         * 下划线转驼峰
         * @param str
         */
        camel(str: string): string;

        /**
         * 驼峰转下划线
         * @param str
         * @param line
         */
        snake(str: string, line?: string): string;

        /**
         * 替换%s
         * @param str
         */
        sprintf(...str): string;

        /**
         * 通过下标删除数组
         * @param data
         * @param index
         */
        arrayRemoveByIndex(data: [any], index: number): [any];

        /**
         * 触发插件准备完成事件
         * @param {jQuery} $element jQuery对象
         * @param {String} pluginName  插件名称
         * @param {?[]} context 插件上下文
         */
        triggerPluginReady($element: object, pluginName: string, context: any);

        /**
         * 类继承实现
         * @param classes 要继承的类
         * @param methods 覆盖的方法
         * @return 旧方法集合
         */
        extends(classes: object, methods: object): object;

        /**
         * 类方法重写方法实现
         * @param oldMethods 旧方法集合
         * @param name 方法名称
         * @param context 上下文
         * @param args 方法参数
         */
        override(oldMethods: object, name: string, context: any, args: IArguments): any;

        /**
         * 地址预览处理
         */
        urlPreviewHandler(options: {
            url: string,
            image: () => void,
            video: () => void,
            audio: () => void,
            file: () => void,
        });

        /**
         * 获取作用域内的方法或变量
         * @param name
         * @param defaults
         */
        scope(name: string, defaults?: any): any;


        /**
         * 获取滚动条宽度
         */
        getScrollbarWidth(): number;

        /**
         * 剔除左侧内容
         * @param string
         * @param space
         */
        ltrim(string: string, space?: string): string

        /**
         * 剔除右侧内容
         * @param string
         * @param space
         */
        rtrim(string: string, space?: string): string

        /**
         * 生成随机字符
         * @param length 长度
         * @param chars 自定义字符
         */
        random(length: number, chars?: string): string;

        /**
         * 数据转树结构
         * @param list 数组
         * @param idField id字段名称
         * @param childField 子节点字段名
         * @param parentField 上级节点字段名
         */
        listToTree(list: [object], idField: string, childField: string, parentField: string): [object];
    }

    /**
     * 路由
     */
    var route: BusyAdminRoute;

    interface BusyAdminRouteState {
        /**
         * 路由地址
         */
        path: string,

        /**
         * 前进或后退是否执行调度
         */
        popStateDispatch: boolean
    }


    interface BusyAdminRoute {
        /**
         * 设置基本路径
         * @param url
         */
        base(url: string);

        /**
         * 点击拦截处理
         * @param event
         */
        clickHandler(event: Event): boolean;

        /**
         * 执行一个路由
         * @param path 路由地址
         * @param state 路由状态设置
         * @param dispatch 是否执行路由调度，不执行页面不会初始化，默认执行
         * @param push 是否保存到浏览器历史记录中，默认保存
         */
        show(path: string, state?: BusyAdminRouteState, dispatch?: boolean, push?: boolean);

        /**
         * 替换路由
         * @param path 路由地址
         * @param state 路由状态设置
         * @param init 是否为初始化，初始化不会执行页面初始化，默认不是
         * @param dispatch 是否执行路由调度，不执行页面不会初始化，默认执行
         */
        replace(path: string, state?: BusyAdminRouteState, init?: boolean, dispatch?: boolean);

        /**
         * 从一个地址重定向到另外一个地址
         * @param from 来源地址
         * @param to 目标地址
         */
        redirect(from: string, to?: string);

        /**
         * 返回到某个地址
         * @param path 理由地址
         * @param state 路由状态设置
         */
        back(path?: string, state?: BusyAdminRouteState);

        /**
         * 注册退出回调
         * @param path 路由地址或全局退出回调
         * @param fn 退出回调
         */
        exit(path: string | (() => void), fn: () => void);

        /**
         * 刷新
         */
        reload();

        /**
         * 解析响应操作
         * @param result
         * @param operate
         * @param after
         */
        parseRespOperate(result: BusyAdminResponseResult, operate: any, after?: ((operate: string) => void));
    }
}


