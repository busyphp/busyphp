DROP TABLE IF EXISTS `#__table__#admin_group`;
CREATE TABLE `#__table__#admin_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '权限名称',
  `rule` mediumtext NOT NULL COMMENT '权限规则',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否系统权限',
  `default_group` varchar(30) NOT NULL DEFAULT '' COMMENT '默认面板',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台用户权限';

DROP TABLE IF EXISTS `#__table__#admin_user`;
CREATE TABLE `#__table__#admin_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '帐号',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `email` varchar(255) NOT NULL DEFAULT '' COMMENT '邮箱',
  `phone` varchar(30) NOT NULL DEFAULT '' COMMENT '联系方式',
  `qq` varchar(30) NOT NULL DEFAULT '' COMMENT 'QQ号码',
  `login_ip` char(32) NOT NULL DEFAULT '' COMMENT '本次登录IP',
  `login_time` int(11) NOT NULL DEFAULT '0' COMMENT '本次登录时间',
  `last_ip` varchar(32) NOT NULL DEFAULT '' COMMENT '最后一次登录IP地址',
  `last_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后一次登录时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `group_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '用户组权限ID',
  `section_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '所属部门ID',
  `checked` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否审核',
  `login_total` int(11) NOT NULL DEFAULT '0' COMMENT '登录次数',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否系统管理员',
  `token` varchar(32) NOT NULL DEFAULT '' COMMENT '密钥',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `email` (`email`),
  KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台用户';

DROP TABLE IF EXISTS `#__table__#admin_message`;
CREATE TABLE `#__table__#admin_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已读',
  `read_time` int(11) NOT NULL DEFAULT '0' COMMENT '阅读时间',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '消息内容',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '消息备注',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '操作链接',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台系统消息表';

DROP TABLE IF EXISTS `#__table__#system_config`;
CREATE TABLE `#__table__#system_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `content` mediumtext NOT NULL,
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '备注',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '类型',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '系统配置',
  `is_append` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否加入全局配置',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统键值对配置表';

DROP TABLE IF EXISTS `#__table__#system_file`;
CREATE TABLE `#__table__#system_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '上传时间',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名',
  `url_hash` varchar(32) NOT NULL DEFAULT '' COMMENT 'URL HASH',
  `size` int(16) NOT NULL DEFAULT '0' COMMENT '文件大小（bytes）',
  `mime_type` varchar(30) NOT NULL DEFAULT '',
  `extension` varchar(30) NOT NULL DEFAULT '' COMMENT '文件类型',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '文件原名',
  `mark_type` varchar(30) NOT NULL DEFAULT '' COMMENT '标记类型',
  `mark_value` varchar(36) NOT NULL DEFAULT '' COMMENT '标识值',
  `hash` varchar(32) NOT NULL DEFAULT '' COMMENT '文件的哈希验证字符串',
  `userid` int(11) NOT NULL DEFAULT '0' COMMENT '会员ID',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '后台上传',
  `classify` varchar(20) NOT NULL DEFAULT '' COMMENT '文件分类',
  `is_thumb` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否缩放资源',
  `thumb_id` int(11) NOT NULL DEFAULT '0' COMMENT '缩放资源源文件ID',
  `thumb_width` int(11) NOT NULL DEFAULT '0' COMMENT '缩放宽度',
  `thumb_height` int(11) NOT NULL DEFAULT '0' COMMENT '缩放资源高度',
  PRIMARY KEY (`id`),
  KEY `is_thumb` (`is_thumb`),
  KEY `thumb_id` (`thumb_id`),
  KEY `url_hash` (`url_hash`),
  KEY `hash` (`hash`),
  KEY `mark_type` (`mark_type`),
  KEY `mark_value` (`mark_value`),
  KEY `name` (`name`),
  KEY `extension` (`extension`),
  KEY `userid` (`userid`),
  KEY `is_admin` (`is_admin`),
  KEY `classify` (`classify`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='附件上传表';

DROP TABLE IF EXISTS `#__table__#system_file_class`;
CREATE TABLE `#__table__#system_file_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '分类名称',
  `var` varchar(30) NOT NULL DEFAULT '' COMMENT '分类标识',
  `type` char(20) NOT NULL DEFAULT '' COMMENT '附件类型',
  `home_show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '前台显示',
  `admin_show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '后台显示',
  `sort` smallint(6) NOT NULL DEFAULT '50' COMMENT '自定义排序',
  `suffix` varchar(500) NOT NULL DEFAULT '' COMMENT '允许的后缀',
  `size` int(11) NOT NULL DEFAULT '0' COMMENT '允许的大小 -1 继承基本设置 0 不限',
  `home_upload` tinyint(1) NOT NULL DEFAULT '0' COMMENT '允许前台上传',
  `home_login` tinyint(1) NOT NULL DEFAULT '0' COMMENT '前台必须登录上传',
  `mimetype` text NOT NULL COMMENT '允许的mimetype',
  `is_thumb` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否缩放图片',
  `thumb_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '缩放方式',
  `width` int(11) NOT NULL DEFAULT '0' COMMENT '缩图宽度',
  `height` int(11) NOT NULL DEFAULT '0' COMMENT '缩图高度',
  `delete_source` tinyint(1) NOT NULL DEFAULT '0' COMMENT '缩图后是否删除原图',
  `watermark` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否加水印',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '系统',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件分类表';

DROP TABLE IF EXISTS `#__table__#system_logs`;
CREATE TABLE `#__table__#system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '记录时间',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '记录类型',
  `title` varchar(40) NOT NULL DEFAULT '' COMMENT '操作描述',
  `path` varchar(100) NOT NULL DEFAULT '' COMMENT '操作路径',
  `userid` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `username` varchar(60) NOT NULL DEFAULT '' COMMENT '操作用户名',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否后台操作 1是，0不是',
  `app_name` varchar(20) NOT NULL DEFAULT '' COMMENT 'APP名称',
  `content` mediumtext NOT NULL COMMENT '操作详情',
  `ip` varchar(45) NOT NULL DEFAULT '' COMMENT '操作IP',
  `ua` varchar(1000) NOT NULL DEFAULT '' COMMENT 'UserAgent',
  `url` text NOT NULL COMMENT '操作URL',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='操作记录表';

DROP TABLE IF EXISTS `#__table__#system_menu`;
CREATE TABLE `#__table__#system_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '名称',
  `action` varchar(30) NOT NULL DEFAULT '' COMMENT '执行方法',
  `control` varchar(30) NOT NULL DEFAULT '' COMMENT '控制器',
  `module` varchar(30) NOT NULL DEFAULT '' COMMENT '分组模块',
  `pattern` varchar(255) NOT NULL DEFAULT '' COMMENT '追加方法',
  `params` varchar(255) NOT NULL DEFAULT '' COMMENT '附加参数',
  `higher` varchar(30) NOT NULL DEFAULT '' COMMENT '定义高亮上级',
  `icon` varchar(30) NOT NULL DEFAULT '' COMMENT '图标',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '外部链接',
  `target` varchar(10) NOT NULL DEFAULT '' COMMENT '打开方式',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '默认导航面板',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
  `is_disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否禁用',
  `is_has_action` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否有执行方法',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否系统菜单',
  `sort` smallint(6) NOT NULL DEFAULT '50' COMMENT '自定义排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台菜单管理';

INSERT INTO `#__table__#system_file_class` (`id`, `name`, `var`, `type`, `home_show`, `admin_show`, `sort`, `suffix`, `size`, `home_upload`, `home_login`, `mimetype`, `is_thumb`, `thumb_type`, `width`, `height`, `delete_source`, `watermark`, `is_system`) VALUES
(1, '默认附件', 'file', 'file', 0, 1, 1, '-1', -1, 1, 1, '', 0, 0, 0, 0, 0, 0, 1),
(2, '默认图片', 'image', 'image', 0, 1, 3, 'jpg,png,gif,jpeg', -1, 0, 0, 'image/*', 0, 4, 0, 0, 0, 0, 1),
(3, '默认视频', 'video', 'video', 0, 1, 3, 'mp4', -1, 0, 0, 'video/mp4', 0, 0, 0, 0, 0, 0, 1),
(4, '会员头像', 'avatar', 'image', 1, 1, 5, 'jpg,jpeg,png,gif', -1, 1, 1, 'image/*', 1, 3, 750, 750, 1, 0, 1),
(5, '幻灯片图片', 'banner', 'image', 0, 1, 4, 'jpg,png,gif,jpeg', -1, 1, 1, 'image/*', 1, 1, 640, 256, 1, 0, 1);

INSERT INTO `#__table__#admin_group` (`id`, `name`, `rule`, `is_system`, `default_group`) VALUES
(1, '超级管理员', '', 1, 'system'),
(2, '普通权限', 'System,System.Update,System.Update/cache,System.Update/index,System.User,System.User/password,System.User/personal_info,System.User/personal_password', 0, 'system');

INSERT INTO `#__table__#admin_user` (`id`, `username`, `password`, `email`, `phone`, `qq`, `login_ip`, `login_time`, `last_ip`, `last_time`, `create_time`, `update_time`, `group_id`, `section_id`, `checked`, `login_total`, `is_system`, `token`) VALUES
(1, '#__username__#', '#__password__#', '', '', '', '', 0, '', 0, #__create_time__#, #__create_time__#, 1, 0, 1, 0, 1, '');

INSERT INTO `#__table__#system_config` (`id`, `content`, `name`, `type`, `is_system`, `is_append`) VALUES
(3, 'a:2:{s:6:\"verify\";b:1;s:15:\"multiple_client\";b:1;}', '后台安全配置', 'admin', 1, 0),
(4, 'a:9:{s:6:\"format\";s:6:\"hash-3\";s:10:\"admin_type\";s:48:\"jpg,png,gif,jpeg,bmp,zip,rar,mp4,ttf,ttc,apk,ipa\";s:10:\"admin_size\";s:6:\"102400\";s:9:\"home_type\";s:28:\"jpg,png,gif,jpeg,bmp,zip,rar\";s:9:\"home_size\";s:6:\"102400\";s:5:\"token\";s:32:\"hRZSiECNIrXJAbkqOBpAkZWsKUfavZwU\";s:14:\"watermark_file\";s:0:\"\";s:18:\"watermark_position\";i:8;s:9:\"save_path\";s:11:\"/data/file/\";}', '附件配置', 'file', 1, 0),
(7, 'a:2:{s:5:\"title\";#__title__#;s:9:\"copyright\";s:0:\"\";}', '系统基本配置', 'public', 1, 1);

INSERT INTO `#__table__#system_menu` (`id`, `name`, `action`, `control`, `module`, `pattern`, `params`, `higher`, `icon`, `link`, `target`, `is_default`, `is_show`, `is_disabled`, `is_has_action`, `is_system`, `sort`) VALUES
(1, '系统', '', '', 'system', '', '', '', 'cog', '', '', 0, 1, 0, 1, 0, 2),
(2, '管理员管理', '', 'user', 'system', '', '', '', 'user', '', '', 0, 1, 0, 1, 0, 3),
(3, '管理员列表', 'index', 'user', 'system', '', '', '', 'list-ul', '', '', 0, 1, 0, 1, 0, 0),
(4, '增加管理员', 'add', 'user', 'system', '', '', 'index', 'pencil', '', '', 0, 1, 0, 1, 0, 1),
(5, '管理权限管理', '', 'group', 'system', '', '', '', 'group', '', '', 0, 1, 0, 1, 0, 4),
(6, '权限组列表', 'index', 'group', 'system', '', '', '', 'list-ul', '', '', 0, 1, 0, 1, 0, 0),
(7, '增加权限组', 'add', 'group', 'system', '', '', 'index', 'pencil', '', '', 0, 1, 0, 1, 0, 1),
(8, '附件管理', '', 'file', 'system', '', '', '', 'paperclip', '', '', 0, 1, 0, 1, 0, 50),
(9, '附件列表', 'index', 'file', 'system', '', '', '', 'list-ul', '', '', 0, 1, 0, 1, 0, 2),
(10, '上传附件', 'upload', 'file', 'system', '', '', 'index', 'paperclip', '', '', 0, 1, 0, 1, 0, 4),
(11, '系统管理', '', 'index', 'system', '', '', '', 'wrench', '', '', 0, 1, 0, 1, 0, 2),
(12, '基本设置', 'index', 'index', 'system', '', '', '', 'cog', '', '', 0, 1, 0, 1, 0, 1),
(13, '附件设置', 'setting', 'file', 'system', '', '', '', 'wrench', '', '', 0, 1, 0, 1, 0, 1),
(14, '修改管理员', 'edit', 'user', 'system', '', 'id', 'index', '', '', '', 0, 0, 0, 1, 0, 2),
(15, '修改权限组', 'edit', 'group', 'system', '', 'id', 'index', '', '', '', 0, 0, 0, 1, 0, 2),
(16, '数据更新', '', 'update', 'system', '', '', '', 'refresh', '', '', 0, 1, 0, 1, 0, 0),
(17, '清空数据缓存', 'cache', 'update', 'system', '', '', '', 'trash-o', '', '', 0, 1, 0, 1, 0, 1),
(18, '数据更新中心', 'index', 'update', 'system', '', '', '', 'clock-o', '', '', 0, 1, 0, 1, 0, 2),
(19, '修改个人资料', 'personal_info', 'user', 'system', '', '', '', 'newspaper-o', '', '', 0, 1, 0, 1, 0, 50),
(20, '修改个人密码', 'personal_password', 'user', 'system', '', '', '', 'lock', '', '', 0, 1, 0, 1, 0, 50),
(21, '操作记录', 'logs', 'index', 'system', '', '', '', 'sliders', '', '', 0, 1, 0, 1, 0, 5),
(22, '查看操作记录', 'view_logs', 'index', 'system', '', 'id', 'logs', '', '', '', 0, 0, 0, 1, 0, 6),
(23, '删除附件', 'delete', 'file', 'system', '', '', 'index', '', '', '', 0, 0, 0, 1, 0, 3),
(24, '删除管理员', 'delete', 'user', 'system', '', '', 'index', '', '', '', 0, 0, 0, 1, 0, 3),
(25, '删除权限组', 'delete', 'group', 'system', '', '', 'index', '', '', '', 0, 0, 0, 1, 0, 3),
(26, '开发模式', '', '', 'develop', '', '', '', 'folder-open', '', '', 0, 1, 0, 1, 1, 1),
(27, '后台菜单管理', '', 'system_menu', 'develop', '', '', '', 'list-ul', '', '', 0, 1, 0, 1, 1, 2),
(28, '菜单列表', 'index', 'system_menu', 'develop', '', '', '', 'list-ul', '', '', 0, 1, 0, 1, 1, 1),
(29, '增加菜单', 'add', 'system_menu', 'develop', '', 'id', 'index', 'pencil', '', '', 0, 1, 0, 1, 1, 2),
(30, '修改菜单', 'edit', 'system_menu', 'develop', '', 'id', 'index', '', '', '', 0, 0, 0, 1, 1, 3),
(31, '删除菜单', 'delete', 'system_menu', 'develop', '', 'id', 'index', '', '', '', 0, 0, 0, 1, 1, 4),
(32, '设置排序', 'myorder', 'system_menu', 'develop', '', '', 'index', '', '', '', 0, 0, 0, 1, 1, 5),
(33, '配置管理', '', 'system_config', 'develop', '', '', '', 'save', '', '', 0, 1, 0, 1, 1, 3),
(34, '配置列表', 'index', 'system_config', 'develop', '', '', '', 'list-ul', '', '', 0, 1, 0, 1, 1, 1),
(35, '增加配置', 'add', 'system_config', 'develop', '', '', 'index', 'pencil', '', '', 0, 1, 0, 1, 1, 2),
(36, '修改配置', 'edit', 'system_config', 'develop', '', 'id', 'index', '', '', '', 0, 0, 0, 1, 1, 3),
(37, '删除配置', 'delete', 'system_config', 'develop', '', 'id', 'index', '', '', '', 0, 0, 0, 1, 1, 4),
(38, '附件分类管理', '', 'system_file_class', 'develop', '', '', '', 'list-ul', '', '', 0, 1, 0, 1, 1, 0),
(39, '附件分类列表', 'index', 'system_file_class', 'develop', '', '', '', 'list-ul', '', '', 0, 1, 0, 1, 1, 1),
(40, '增加附件分类', 'add', 'system_file_class', 'develop', '', '', 'index', 'pencil', '', '', 0, 1, 0, 1, 1, 2),
(41, '修改附件分类', 'edit', 'system_file_class', 'develop', '', 'id', 'index', '', '', '', 0, 0, 0, 1, 1, 3),
(42, '删除附件分类', 'delete', 'system_file_class', 'develop', '', 'id', 'index', '', '', '', 0, 0, 0, 1, 1, 4),
(43, '修改管理员密码', 'password', 'user', 'system', '', 'id', 'index', '', '', '', 0, 0, 0, 1, 0, 50),
(44, '清理操作记录', 'clear_logs', 'index', 'system', '', '', 'logs', '', '', '', 0, 0, 0, 1, 0, 8),
(45, '后台安全', 'admin', 'index', 'system', '', '', '', 'cog', '', '', 0, 1, 0, 1, 0, 2);