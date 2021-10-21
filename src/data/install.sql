DROP TABLE IF EXISTS `#__table__#admin_group`;
CREATE TABLE `#__table__#admin_group` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '上级权限ID',
    `name` varchar(30) NOT NULL DEFAULT '' COMMENT '权限名称',
    `default_menu_id` int(11) NOT NULL DEFAULT '0' COMMENT '默认菜单ID',
    `system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否系统权限',
    `rule` text NOT NULL COMMENT '权限规则ID集合，英文逗号分割，左右要有逗号',
    `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用',
    `sort` smallint(6) NOT NULL DEFAULT '50' COMMENT '排序',
    PRIMARY KEY (`id`),
    KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台用户权限';


DROP TABLE IF EXISTS `#__table__#admin_user`;
CREATE TABLE `#__table__#admin_user` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `username` varchar(30) NOT NULL DEFAULT '' COMMENT '帐号',
    `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
    `email` varchar(255) NOT NULL DEFAULT '' COMMENT '邮箱',
    `phone` varchar(30) NOT NULL DEFAULT '' COMMENT '联系方式',
    `qq` varchar(30) NOT NULL DEFAULT '' COMMENT 'QQ号码',
    `group_ids` varchar(255) NOT NULL DEFAULT '' COMMENT '权限组ID集合，英文逗号分割，左右要有逗号',
    `default_group_id` int(11) NOT NULL DEFAULT '0' COMMENT '默认角色组',
    `last_ip` varchar(40) NOT NULL DEFAULT '' COMMENT '最后一次登录IP地址',
    `last_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后一次登录时间',
    `login_ip` varchar(40) NOT NULL DEFAULT '' COMMENT '本次登录IP',
    `login_time` int(11) NOT NULL DEFAULT '0' COMMENT '本次登录时间',
    `login_total` int(11) NOT NULL DEFAULT '0' COMMENT '登录次数',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `checked` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否审核',
    `system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否系统管理员',
    `token` varchar(32) NOT NULL DEFAULT '' COMMENT '密钥',
    `error_total` int(11) NOT NULL DEFAULT '0' COMMENT '密码错误次数统计',
    `error_time` int(11) NOT NULL DEFAULT '0' COMMENT '密码错误开始时间',
    `error_release` int(11) NOT NULL DEFAULT '0' COMMENT '密码错误锁定释放时间',
    `theme` text NOT NULL COMMENT '主题配置',
    PRIMARY KEY (`id`),
    KEY `username` (`username`),
    KEY `email` (`email`),
    KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台用户';


DROP TABLE IF EXISTS `#__table__#admin_message`;
CREATE TABLE `#__table__#admin_message` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已读',
    `read_time` int(11) NOT NULL DEFAULT '0' COMMENT '阅读时间',
    `content` varchar(255) NOT NULL DEFAULT '' COMMENT '消息内容',
    `description` varchar(255) NOT NULL DEFAULT '' COMMENT '消息备注',
    `url` varchar(255) NOT NULL DEFAULT '' COMMENT '操作链接',
    `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台系统消息表';


DROP TABLE IF EXISTS `#__table__#system_config`;
CREATE TABLE `#__table__#system_config` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `content` mediumtext NOT NULL,
    `name` varchar(60) NOT NULL DEFAULT '' COMMENT '备注',
    `type` varchar(30) NOT NULL DEFAULT '' COMMENT '类型',
    `system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '系统配置',
    `append` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否加入全局配置',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统键值对配置表';

DROP TABLE IF EXISTS `#__table__#system_file`;
CREATE TABLE `#__table__#system_file` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '上传时间',
    `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '会员ID',
    `type` varchar(20) NOT NULL DEFAULT '' COMMENT '文件类型',
    `class_type` varchar(30) NOT NULL DEFAULT '' COMMENT '文件分类',
    `class_value` varchar(64) NOT NULL DEFAULT '' COMMENT '文件分类对应的业务值',
    `client` varchar(32) NOT NULL DEFAULT '' COMMENT '所属客户端',
    `url` varchar(255) NOT NULL DEFAULT '' COMMENT '文件地址',
    `url_hash` varchar(32) NOT NULL DEFAULT '' COMMENT 'URL HASH',
    `path` varchar(255) NOT NULL DEFAULT '' COMMENT '文件路径',
    `disk` varchar(32) NOT NULL DEFAULT '' COMMENT '磁盘名称',
    `size` int(16) NOT NULL DEFAULT '0' COMMENT '文件大小(字节)',
    `mime_type` varchar(255) NOT NULL DEFAULT '' COMMENT '文件MimeType',
    `extension` varchar(30) NOT NULL DEFAULT '' COMMENT '文件扩展名',
    `name` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名',
    `hash` varchar(32) NOT NULL DEFAULT '' COMMENT '文件的哈希值',
    `width` int(11) NOT NULL DEFAULT '0' COMMENT '文件宽度(像素)',
    `height` int(11) NOT NULL DEFAULT '0' COMMENT '文件高度(像素)',
    PRIMARY KEY (`id`),
    KEY `url_hash` (`url_hash`),
    KEY `hash` (`hash`),
    KEY `user_id` (`user_id`),
    KEY `type` (`type`),
    KEY `class_type` (`class_type`),
    KEY `class_value` (`class_value`),
    KEY `client_type` (`client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='附件上传表';


DROP TABLE IF EXISTS `#__table__#system_file_class`;
CREATE TABLE `#__table__#system_file_class` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name` varchar(30) NOT NULL DEFAULT '' COMMENT '分类名称',
    `var` varchar(30) NOT NULL DEFAULT '' COMMENT '分类标识',
    `type` char(20) NOT NULL DEFAULT '' COMMENT '附件类型',
    `sort` smallint(6) NOT NULL DEFAULT '50' COMMENT '自定义排序',
    `allow_extensions` varchar(255) NOT NULL DEFAULT '' COMMENT '留空使用系统设置，多个用英文逗号隔开',
    `max_size` int(11) NOT NULL DEFAULT '0' COMMENT '单位MB，0使用系统默认设置，0以上按照该设置',
    `mime_type` varchar(1000) NOT NULL DEFAULT '' COMMENT '允许的MimeType，多个用英文逗号分割',
    `thumb_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '缩图方式',
    `thumb_width` int(11) NOT NULL DEFAULT '0' COMMENT '缩图宽度',
    `thumb_height` int(11) NOT NULL DEFAULT '0' COMMENT '缩图高度',
    `watermark` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否加水印',
    `system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否系统类型',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件分类表';


DROP TABLE IF EXISTS `#__table__#system_logs`;
CREATE TABLE `#__table__#system_logs` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
    `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '操作类型',
    `name` varchar(255) NOT NULL DEFAULT '' COMMENT '操作名称',
    `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作用户ID',
    `username` varchar(60) NOT NULL DEFAULT '' COMMENT '操作用户名',
    `class_type` int(11) NOT NULL DEFAULT '0' COMMENT '日志分类',
    `class_value` varchar(32) NOT NULL DEFAULT '' COMMENT '日志分类业务参数',
    `client` varchar(32) NOT NULL DEFAULT '' COMMENT '操作客户端',
    `ip` varchar(40) NOT NULL DEFAULT '' COMMENT '客户端IP',
    `method` varchar(20) NOT NULL DEFAULT '' COMMENT '请求方式',
    `url` text NOT NULL COMMENT '请求URL',
    `params` text NOT NULL COMMENT '请求参数',
    `headers` text NOT NULL COMMENT '请求头',
    `result` text NOT NULL COMMENT '操作结果',
     PRIMARY KEY (`id`),
     KEY `type` (`type`),
     KEY `class_value` (`class_value`),
     KEY `class_type` (`class_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='操作记录表';


DROP TABLE IF EXISTS `#__table__#system_menu`;
CREATE TABLE `#__table__#system_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '名称',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '路由地址',
  `parent_path` varchar(255) NOT NULL DEFAULT '' COMMENT '上级路由',
  `top_path` varchar(255) NOT NULL DEFAULT '' COMMENT '顶级菜单默认访问路由地址',
  `params` varchar(255) NOT NULL DEFAULT '' COMMENT '附加参数',
  `icon` varchar(128) NOT NULL DEFAULT '' COMMENT '图标',
  `target` varchar(10) NOT NULL DEFAULT '' COMMENT '打开方式',
  `hide` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否禁用',
  `system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否系统菜单',
  `sort` smallint(6) NOT NULL DEFAULT '50' COMMENT '自定义排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台菜单管理';


INSERT INTO `#__table__#admin_group` (`parent_id`, `name`, `default_menu_id`, `system`, `rule`, `status`, `sort`) VALUES
(0, '超级管理员', 87, 1, '', 1, 0);


INSERT INTO `#__table__#admin_user` (`username`, `password`, `group_ids`, `default_group_id`, `create_time`, `update_time`, `checked`, `system`) VALUES
('#__username__#', '#__password__#', ',1,', 1, '#__create_time__#', '#__create_time__#', 1, 1);


INSERT INTO `#__table__#system_config` (`content`, `name`, `type`, `system`, `append`) VALUES
('a:6:{s:5:\"title\";s:0:\"\";s:7:\"favicon\";s:0:\"\";s:9:\"police_no\";s:0:\"\";s:6:\"icp_no\";s:0:\"\";s:9:\"copyright\";s:0:\"\";s:21:\"img_error_placeholder\";s:0:\"\";}', '系统基本配置', 'public', 1, 1),
('a:11:{s:6:\"verify\";b:1;s:15:\"multiple_client\";b:1;s:10:\"save_login\";i:15;s:5:\"often\";i:0;s:18:\"login_error_minute\";i:5;s:15:\"login_error_max\";i:5;s:23:\"login_error_lock_minute\";i:60;s:5:\"title\";s:0:\"\";s:15:\"logo_horizontal\";s:0:\"\";s:9:\"logo_icon\";s:0:\"\";s:8:\"login_bg\";s:0:\"\";}', '后台安全配置', 'admin', 1, 0),
('a:3:{s:4:\"disk\";s:6:\"public\";s:17:\"dir_generate_type\";s:6:\"hash-3\";s:7:\"clients\";a:2:{s:5:\"admin\";a:2:{s:16:\"allow_extensions\";s:66:\"jpg, jpeg, png, gif, mp3, mp4, zip, rar, doc, docx, xls, xlsx, ttf\";s:8:\"max_size\";s:6:\"100.00\";}s:4:\"home\";a:2:{s:16:\"allow_extensions\";s:19:\"jpg, jpeg, png, gif\";s:8:\"max_size\";s:5:\"10.00\";}}}', '文件上传配置', 'upload', 1, 0),
('a:6:{s:4:\"file\";s:0:\"\";s:7:\"opacity\";s:2:\"25\";s:8:\"offset_x\";s:1:\"0\";s:8:\"offset_y\";s:1:\"0\";s:13:\"offset_rotate\";s:1:\"0\";s:8:\"position\";s:1:\"9\";}', '图片水印配置', 'watermark', 1, 0),
('a:8:{s:6:\"domain\";s:0:\"\";s:8:\"bg_color\";s:7:\"#ffffff\";s:15:\"empty_image_var\";s:0:\"\";s:17:\"error_placeholder\";s:0:\"\";s:10:\"save_local\";b:0;s:9:\"watermark\";b:0;s:5:\"sizes\";a:2:{i:0;a:3:{s:5:\"alias\";s:6:\"avatar\";s:5:\"width\";i:300;s:6:\"height\";i:300;}i:1;a:3:{s:5:\"alias\";s:0:\"\";s:5:\"width\";i:100;s:6:\"height\";i:100;}}s:14:\"unlimited_size\";b:0;}', '动态缩图配置', 'thumb', 1, 0),
('a:1:{s:7:\"clients\";a:2:{s:5:\"admin\";a:12:{s:5:\"curve\";b:1;s:5:\"noise\";b:1;s:8:\"bg_image\";b:0;s:6:\"length\";s:0:\"\";s:13:\"expire_minute\";s:0:\"\";s:5:\"token\";s:0:\"\";s:8:\"bg_color\";s:0:\"\";s:4:\"type\";s:0:\"\";s:9:\"font_size\";s:0:\"\";s:4:\"font\";s:0:\"\";s:9:\"font_file\";s:0:\"\";s:4:\"code\";s:0:\"\";}s:4:\"home\";a:12:{s:5:\"curve\";b:1;s:5:\"noise\";b:1;s:8:\"bg_image\";b:0;s:6:\"length\";s:0:\"\";s:13:\"expire_minute\";s:0:\"\";s:5:\"token\";s:0:\"\";s:8:\"bg_color\";s:0:\"\";s:4:\"type\";s:0:\"\";s:9:\"font_size\";s:0:\"\";s:4:\"font\";s:0:\"\";s:9:\"font_file\";s:0:\"\";s:4:\"code\";s:0:\"\";}}}', '图片验证码配置', 'captcha', 1, 0),
('a:9:{s:6:\"domain\";s:0:\"\";s:5:\"level\";s:1:\"Q\";s:6:\"margin\";s:1:\"1\";s:7:\"quality\";s:2:\"80\";s:4:\"size\";s:2:\"10\";s:10:\"save_local\";s:1:\"0\";s:11:\"logo_status\";s:1:\"0\";s:9:\"logo_path\";s:0:\"\";s:9:\"logo_size\";s:1:\"5\";}', '二维码生成配置', 'qrcode', 1, 0);


INSERT INTO `#__table__#system_file_class` (`name`, `var`, `type`, `sort`, `allow_extensions`, `max_size`, `mime_type`, `thumb_type`, `thumb_width`, `thumb_height`, `watermark`, `system`) VALUES
('文件', 'file', 'file', 1, '', 0, '', 0, 0, 0, 0, 1),
('图片', 'image', 'image', 2, 'jpg, png, gif, jpeg', 0, 'image/*', 0, 0, 0, 0, 1),
('视频', 'video', 'video', 3, 'mp4', 0, 'video/mp4', 0, 0, 0, 0, 1),
('音频', 'audio', 'audio', 4, 'mp3', 0, '', 0, 0, 0, 0, 1),
('头像', 'avatar', 'image', 5, 'jpg, jpeg, png, gif', 0, 'image/*', 0, 640, 640, -1, 1),
('轮播图', 'banner', 'image', 6, 'jpg, png, gif, jpeg', 0, 'image/*', 1, 1280, 512, -1, 1);



INSERT INTO  `#__table__#system_menu` (`name`, `path`, `parent_path`, `top_path`, `params`, `icon`, `target`, `hide`, `disabled`, `system`, `sort`) VALUES
('开发模式', '#developer', '', '', '', 'fa fa-folder-open-o', '', 0, 0, 1, 0),
('文件分类', 'system_file_class/index', '#developer', '', '', 'fa fa-file-text-o', '', 0, 0, 1, 3),
('添加附件分类', 'system_file_class/add', 'system_file_class/index', '', '', '', '', 1, 0, 1, 1),
('修改附件分类', 'system_file_class/edit', 'system_file_class/index', '', '', '', '', 1, 0, 1, 2),
('删除附件分类', 'system_file_class/delete', 'system_file_class/index', '', '', '', '', 1, 0, 1, 3),
('菜单管理', 'system_menu/index', '#developer', '', '', 'bicon bicon-menu', '', 0, 0, 1, 1),
('添加菜单', 'system_menu/add', 'system_menu/index', '', '', '', '', 1, 0, 1, 1),
('修改菜单', 'system_menu/edit', 'system_menu/index', '', '', '', '', 1, 0, 1, 2),
('删除菜单', 'system_menu/delete', 'system_menu/index', '', '', '', '', 1, 0, 1, 3),
('排序菜单', 'system_menu/sort', 'system_menu/index', '', '', '', '', 1, 0, 1, 4),
('配置管理', 'system_config/index', '#developer', '', '', 'fa fa-cube', '', 0, 0, 1, 2),
('添加配置', 'system_config/add', 'system_config/index', '', '', '', '', 1, 0, 1, 1),
('修改配置', 'system_config/edit', 'system_config/index', '', '', '', '', 1, 0, 1, 2),
('删除配置', 'system_config/delete', 'system_config/index', '', '', '', '', 1, 0, 1, 3),
('开发手册', '#developer_manual', '#developer', '', '', 'fa fa-book', '', 0, 0, 1, 5),
('基本元素', '#developer_manual_element', '#developer_manual', '', '', 'fa fa-wpforms', '', 0, 0, 1, 1),
('组件', '#developer_manual_component', '#developer_manual', '', '', 'fa fa-sliders', '', 0, 0, 1, 2),
('表单', 'manual_element/form', '#developer_manual_element', '', '', 'fa fa-list-alt', '', 0, 0, 1, 3),
('按钮/按钮组', 'manual_element/button', '#developer_manual_element', '', '', 'fa fa-flickr', '', 0, 0, 1, 4),
('徽章', 'manual_element/badge', '#developer_manual_element', '', '', 'fa fa-circle', '', 0, 0, 1, 5),
('警告框', 'manual_element/alert', '#developer_manual_element', '', '', 'fa fa-info-circle', '', 0, 0, 1, 6),
('进度条', 'manual_element/progress', '#developer_manual_element', '', '', 'fa fa-tasks', '', 0, 0, 1, 7),
('日期/时间', 'manual_component/date', '#developer_manual_component', '', '', 'fa fa-calendar', '', 0, 0, 1, 12),
('目录', 'manual_component/catalog', '#developer_manual_component', '', '', 'fa fa-list-ol', '', 0, 0, 1, 20),
('复制/剪切', 'manual_component/copy', '#developer_manual_component', '', '', 'fa fa-copy', '', 0, 0, 1, 50),
('对话框', 'manual_component/dialog', '#developer_manual_component', '', '', 'fa fa-clone', '', 0, 0, 1, 1),
('自动请求', 'manual_component/request', '#developer_manual_component', '', '', 'fa fa-random', '', 0, 0, 1, 11),
('表单验证', 'manual_component/form_verify', '#developer_manual_component', '', '', 'fa fa-check', '', 0, 0, 1, 10),
('自动表单', 'manual_component/form', '#developer_manual_component', '', '', 'fa fa-wpforms', '', 0, 0, 1, 9),
('搜索栏', 'manual_component/search_bar', '#developer_manual_component', '', '', 'fa fa-search', '', 0, 0, 1, 8),
('全选/反选', 'manual_component/check_all', '#developer_manual_component', '', '', 'fa fa-check-square-o', '', 0, 0, 1, 7),
('穿梭框', 'manual_component/shuttle', '#developer_manual_component', '', '', 'fa fa-exchange', '', 0, 0, 1, 6),
('下拉选择器', 'manual_component/select_picker', '#developer_manual_component', '', '', 'bicon bicon-down-menu', '', 0, 0, 1, 13),
('输入提示', 'manual_component/autocomplete', '#developer_manual_component', '', '', 'fa fa-pencil', '', 0, 0, 1, 14),
('富文本编辑器', 'manual_component/editor', '#developer_manual_component', '', '', 'fa fa-edit', '', 0, 0, 1, 15),
('文件选择', 'manual_component/file_picker', '#developer_manual_component', '', '', 'fa fa-folder-open', '', 0, 0, 1, 3),
('视频预览', 'manual_component/video_viewer', '#developer_manual_component', '', '', 'bicon bicon-video', '', 0, 0, 1, 18),
('图片预览', 'manual_component/image_viewer', '#developer_manual_component', '', '', 'bicon bicon-image-viewer', '', 0, 0, 1, 17),
('文件上传', 'manual_component/upload', '#developer_manual_component', '', '', 'fa fa-cloud-upload', '', 0, 0, 1, 3),
('树形组件', 'manual_component/tree', '#developer_manual_component', '', '', 'bicon bicon-tree', '', 0, 0, 1, 5),
('颜色选择器', 'manual_component/color_picker', '#developer_manual_component', '', '', 'fa fa-eyedropper', '', 0, 0, 1, 16),
('数据表格', 'manual_component/table', '#developer_manual_component', '', '', 'bicon bicon-table', '', 0, 0, 1, 4),
('模态框', 'manual_component/modal', '#developer_manual_component', '', '', 'bicon bicon-dialog', '', 0, 0, 1, 2),
('代码高亮', 'manual_component/high_code', '#developer_manual_component', '', '', 'fa fa-file-code-o', '', 0, 0, 1, 19),
('面板', 'manual_element/panel', '#developer_manual_element', '', '', 'bicon bicon-dialog', '', 0, 0, 1, 11),
('选项卡', 'manual_element/tabs', '#developer_manual_element', '', '', 'fa fa-flag-o', '', 0, 0, 1, 10),
('列表组', 'manual_element/group_list', '#developer_manual_element', '', '', 'fa fa-list-ul', '', 0, 0, 1, 9),
('列表布局', 'manual_element/list_item', '#developer_manual_element', '', '', 'fa fa-list-alt', '', 0, 0, 1, 12),
('布局', 'manual_element/grid', '#developer_manual_element', '', '', 'fa fa-th-large', '', 0, 0, 1, 1),
('表格', 'manual_element/table', '#developer_manual_element', '', '', 'bicon bicon-table', '', 0, 0, 1, 8),
('基本', 'manual_element/base', '#developer_manual_element', '', '', 'fa fa-code', '', 0, 0, 1, 2),
('Checkbox/Radio', 'manual_component/checkbox_radio', '#developer_manual_component', '', '', 'bicon bicon-checkbox-checked', '', 0, 0, 1, 13),
('下拉菜单', 'manual_component/dropdown', '#developer_manual_component', '', '', 'fa fa-chevron-circle-down', '', 0, 0, 1, 14),
('选项卡', 'manual_component/tab', '#developer_manual_component', '', '', 'fa fa-flag-o', '', 0, 0, 1, 14),
('提示工具', 'manual_component/tooltip', '#developer_manual_component', '', '', 'fa fa-commenting-o', '', 0, 0, 1, 14),
('弹出框', 'manual_component/popover', '#developer_manual_component', '', '', 'fa fa-comment-o', '', 0, 0, 1, 14),
('折叠面板', 'manual_component/collapse', '#developer_manual_component', '', '', 'fa fa-server', '', 0, 0, 1, 14),
('随机字符', 'manual_component/random', '#developer_manual_component', '', '', 'fa fa-refresh', '', 0, 0, 1, 50),
('插件管理', 'system_plugin/index', '#developer', '', '', 'fa fa-plug', '', 0, 0, 1, 4),
('安装插件', 'system_plugin/install', 'system_plugin/index', '', '', '', '', 1, 0, 1, 1),
('卸载插件', 'system_plugin/uninstall', 'system_plugin/index', '', '', '', '', 1, 0, 1, 2),
('系统', '#system', '', '', '', 'glyphicon glyphicon-cog', '', 0, 0, 0, 1),
('管理员管理', 'system_user/index', '#system_user', '', '', 'bicon bicon-user-manager', '', 0, 0, 0, 1),
('添加管理员', 'system_user/add', 'system_user/index', '', '', '', '', 1, 0, 0, 1),
('修改管理员', 'system_user/edit', 'system_user/index', '', 'id', '', '', 1, 0, 0, 2),
('删除管理员', 'system_user/delete', 'system_user/index', '', '', '', '', 1, 0, 0, 3),
('修改管理员密码', 'system_user/password', 'system_user/index', '', '', '', '', 1, 0, 0, 4),
('系统管理', '#system_manager', '#system', '', '', 'fa fa-anchor', '', 0, 0, 0, 0),
('系统设置', '#system_setting', '#system_manager', '', '', 'fa fa-cogs', '', 0, 0, 0, 0),
('操作记录', 'system_logs/index', '#system_manager', '', '', 'fa fa-file-text-o', '', 0, 0, 0, 2),
('查看操作记录', 'system_logs/detail', 'system_logs/index', '', '', '', '', 1, 0, 0, 50),
('清理操作记录', 'system_logs/clear', 'system_logs/index', '', '', '', '', 1, 0, 0, 50),
('管理员角色', 'system_group/index', '#system_user', '', '', 'bicon bicon-user-lock', '', 0, 0, 0, 2),
('添加角色', 'system_group/add', 'system_group/index', '', '', '', '', 1, 0, 0, 1),
('修改角色', 'system_group/edit', 'system_group/index', '', '', '', '', 1, 0, 0, 2),
('删除角色', 'system_group/delete', 'system_group/index', '', '', '', '', 1, 0, 0, 3),
('文件管理', 'system_file/index', '#system_manager', '', '', 'fa fa-file-text', '', 0, 0, 0, 3),
('上传文件', 'system_file/upload', 'system_file/index', '', '', '', '', 1, 0, 0, 50),
('删除文件', 'system_file/delete', 'system_file/index', '', '', '', '', 1, 0, 0, 50),
('清理缓存', 'system_manager/cache_clear', '#system_manager', '', '', '', '', 1, 0, 0, 50),
('缓存加速', 'system_manager/cache_create', '#system_manager', '', '', '', '', 1, 0, 0, 50),
('基本设置', 'system_manager/index', '#system_setting', '', '', 'fa fa-cog', '', 0, 0, 0, 0),
('后台设置', 'system_manager/admin', '#system_setting', '', '', 'bicon bicon-pc', '', 0, 0, 0, 1),
('文件上传设置', 'system_manager/upload', '#system_setting', '', '', 'fa fa-cloud-upload', '', 0, 0, 0, 3),
('图片水印设置', 'system_manager/watermark', '#system_setting', '', '', 'fa fa-bookmark', '', 0, 0, 0, 2),
('分类上传设置', 'system_manager/file_class', 'system_manager/upload', '', '', '', '', 1, 0, 0, 4),
('排序文件分类', 'system_file_class/sort', 'system_file_class/index', '', '', '', '', 1, 0, 0, 50),
('系统用户', '#system_user', '#system', '', '', 'fa fa-user-circle', '', 0, 0, 0, 50),
('排序角色', 'system_group/sort', 'system_group/index', '', '', '', '', 1, 0, 0, 5),
('启用/禁用管理员', 'system_user/change_checked', 'system_user/index', '', '', '', '', 1, 0, 0, 5),
('解锁管理员', 'system_user/unlock', 'system_user/index', '', '', '', '', 1, 0, 0, 6),
('启用/禁用角色', 'system_group/change_status', 'system_group/index', '', '', '', '', 1, 0, 0, 4),
('缩图生成设置', 'system_manager/thumb', '#system_setting', '', '', 'fa fa-image', '', 0, 0, 0, 4),
('二维码生成设置', 'system_manager/qrcode', '#system_setting', '', '', 'fa fa-qrcode', '', 0, 0, 0, 5),
('图形验证码设置', 'system_manager/captcha', '#system_setting', '', '', 'bicon bicon-safe', '', 0, 0, 0, 6);