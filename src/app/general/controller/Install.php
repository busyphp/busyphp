<?php

namespace BusyPHP\app\general\controller;

use BusyPHP\App;
use BusyPHP\controller;
use BusyPHP\model\Setting;
use BusyPHP\helper\file\File;
use BusyPHP\helper\util\Filter;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\system\config\SystemConfig;
use mysqli;
use think\Exception;

/**
 * BusyPHP数据库安装
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/6 下午10:31 下午 Install.php $
 */
class Install extends Controller
{
    protected $lockFile;
    
    
    protected function initialize()
    {
        parent::initialize();
        
        $this->app->setRuntimePath(App::runtimePath('general'));
        $this->lockFile = App::getPublicPath('install') . 'install.lock';
        $this->assign('finish', false);
        
        // 检测是否安装完毕
        if (is_file($this->lockFile) && !in_array(ACTION_NAME, ['index', 'finish'])) {
            $this->redirect(url('general/install/index'))->send();
            exit;
        }
    }
    
    
    protected function display($template = '', $charset = 'utf-8', $contentType = '', $content = '')
    {
        $template = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . ACTION_NAME . '.html';
        
        // 步进值
        $step  = array_search(ACTION_NAME, ['index', 'env', 'db', 'finish']);
        $steps = [];
        for ($i = 0; $i <= $step; $i++) {
            if ($i == $step) {
                $steps[$i] = ' step-info';
            } else {
                $steps[$i] = ' step-success';
            }
        }
        $progress = $step * 25 + 25;
        $this->assign('steps', $steps);
        $this->assign('progress', $progress);
        $this->assign('version_name', $this->app->getBusyVersion());
        $this->assign('title', $this->app->getBusyName());
        
        return parent::display($template, $charset, $contentType, $content);
    }
    
    
    /**
     * 检测目录是否可写
     * @param $dir
     * @return int
     */
    private function isDirWriteable($dir)
    {
        if (!is_dir($dir)) {
            if (false === mkdir($dir, 0775)) {
                return false;
            }
        }
        
        if ($fp = fopen("$dir/test.txt", 'w')) {
            fclose($fp);
            unlink("$dir/test.txt");
            
            return true;
        }
        
        return false;
    }
    
    
    /**
     * 连接mysql
     * @param $host
     * @param $user
     * @param $pass
     * @param $port
     * @return mysqli
     * @throws Exception
     */
    private function mysqlInit($host, $user, $pass, $port)
    {
        $mysql = new mysqli($host, $user, $pass, null, $port);
        if ($mysql->connect_errno) {
            switch (intval($mysql->connect_errno)) {
                case 1045:
                    $message = '您的数据库访问用户名或是密码错误';
                break;
                case 2002:
                    $message = '您的数据库连接失败';
                break;
                default:
                    $message = "[{$mysql->connect_errno}]{$mysql->connect_error}";
            }
            throw new Exception($message);
        }
        
        return $mysql;
    }
    
    
    /**
     * 解析SQL语句
     * @param string $file
     * @param string $prefix
     * @param array  $serach
     * @param array  $replace
     * @return array
     * @throws Exception
     */
    private function parseSql($file, $prefix, $serach = [], $replace = [])
    {
        // 读取SQL文件
        if (!is_file($file)) {
            throw new Exception("安装包不正确, 数据安装脚本缺失: {$file}");
        }
        
        $sql = file_get_contents($file);
        $sql = str_replace('#__table__#', $prefix, $sql);
        if ($serach && $replace) {
            $sql = str_replace($serach, $replace, $sql);
        }
        
        $sql = str_replace("\r", "\n", $sql);
        $sql = explode(";\n", $sql);
        $sql = is_array($sql) ? $sql : [];
        
        return $sql;
    }
    
    
    /**
     * 配置.env文件
     * @param array $db
     */
    private function configEnv(array $db)
    {
        $file    = $this->app->getRootPath() . '.env';
        $content = file_get_contents($file);
        $content = preg_replace_callback('/\[DATABASE\](.*?)\[/s', function() use ($db) {
            return <<<HTML
[DATABASE]
TYPE = mysql
HOSTNAME = {$db['server']}
DATABASE = {$db['name']}
PREFIX = {$db['prefix']}
USERNAME = {$db['username']}
PASSWORD = {$db['password']}
HOSTPORT = {$db['port']}
CHARSET = utf8

[
HTML;
        }, $content);
        
        File::write($file, $content);
    }
    
    
    /**
     * 首页
     */
    public function index()
    {
        if (is_file($this->lockFile)) {
            if ($this->app->isDebug()) {
                return response('您已安装过该系统，请勿重复安装。<br/> 如需重复安装，请手动删除 public/install/install.lock 文件')->code(404);
            } else {
                return response('', 403);
            }
        }
        
        return $this->display();
    }
    
    
    /**
     * 环境检测
     */
    public function env()
    {
        $ret                          = [];
        $ret['server']['os']['value'] = php_uname();
        if (PHP_SHLIB_SUFFIX == 'dll') {
            $ret['server']['os']['remark'] = '建议使用 Linux 系统以提升程序性能';
            $ret['server']['os']['class']  = 'warning';
        }
        $ret['server']['sapi']['value'] = $_SERVER['SERVER_SOFTWARE'];
        if (PHP_SAPI == 'isapi') {
            $ret['server']['sapi']['remark'] = '建议使用 Apache 或 Nginx 以提升程序性能';
            $ret['server']['sapi']['class']  = 'warning';
        }
        $ret['server']['php']['value']    = PHP_VERSION;
        $ret['server']['upload']['value'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknow';
        
        $ret['php']['version']['value'] = PHP_VERSION;
        $ret['php']['version']['class'] = 'success';
        if (version_compare(PHP_VERSION, '7.1.0') == -1) {
            $ret['php']['version']['class']  = 'danger';
            $ret['php']['version']['failed'] = true;
            $ret['php']['version']['remark'] = 'PHP版本必须为 7.1.0 以上.';
        }
        
        $ret['php']['mysql']['ok'] = function_exists('mysqli_connect');
        if ($ret['php']['mysql']['ok']) {
            $ret['php']['mysql']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
        } else {
            $ret['php']['pdo']['failed']  = true;
            $ret['php']['mysql']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
        }
        
        $ret['php']['pdo']['ok'] = extension_loaded('pdo') && extension_loaded('pdo_mysql');
        if ($ret['php']['pdo']['ok']) {
            $ret['php']['pdo']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
            $ret['php']['pdo']['class'] = 'success';
            if (!$ret['php']['mysql']['ok']) {
                $ret['php']['pdo']['remark'] = '您的PHP环境不支持 mysqli_connect，请开启此扩展. ';
            }
        } else {
            $ret['php']['pdo']['failed'] = true;
            if ($ret['php']['mysql']['ok']) {
                $ret['php']['pdo']['value']  = '<span class="glyphicon glyphicon-remove text-warning"></span>';
                $ret['php']['pdo']['class']  = 'warning';
                $ret['php']['pdo']['remark'] = '您的PHP环境不支持PDO, 请开启此扩展. ';
            } else {
                $ret['php']['pdo']['value']  = '<span class="glyphicon glyphicon-remove text-danger"></span>';
                $ret['php']['pdo']['class']  = 'danger';
                $ret['php']['pdo']['remark'] = '您的PHP环境不支持PDO, 也不支持 mysqli_connect, 系统无法正常运行. ';
            }
        }
        
        $ret['php']['fopen']['ok'] = @ini_get('allow_url_fopen') && function_exists('fsockopen');
        if ($ret['php']['fopen']['ok']) {
            $ret['php']['fopen']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
        } else {
            $ret['php']['fopen']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
        }
        
        $ret['php']['curl']['ok'] = extension_loaded('curl') && function_exists('curl_init');
        if ($ret['php']['curl']['ok']) {
            $ret['php']['curl']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
            $ret['php']['curl']['class'] = 'success';
            if (!$ret['php']['fopen']['ok']) {
                $ret['php']['curl']['remark'] = '您的PHP环境虽然不支持 allow_url_fopen, 但已经支持了cURL, 这样系统是可以正常高效运行的, 不需要额外处理. ';
            }
        } else {
            if ($ret['php']['fopen']['ok']) {
                $ret['php']['curl']['value']  = '<span class="glyphicon glyphicon-remove text-warning"></span>';
                $ret['php']['curl']['class']  = 'warning';
                $ret['php']['curl']['remark'] = '您的PHP环境不支持cURL, 但支持 allow_url_fopen, 这样系统虽然可以运行, 但还是建议你开启cURL以提升程序性能和系统稳定性. ';
            } else {
                $ret['php']['curl']['value']  = '<span class="glyphicon glyphicon-remove text-danger"></span>';
                $ret['php']['curl']['class']  = 'danger';
                $ret['php']['curl']['remark'] = '您的PHP环境不支持cURL, 也不支持 allow_url_fopen, 系统无法正常运行. ';
                $ret['php']['curl']['failed'] = true;
            }
        }
        $ret['php']['gd']['ok'] = extension_loaded('gd');
        if ($ret['php']['gd']['ok']) {
            $ret['php']['gd']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
            $ret['php']['gd']['class'] = 'success';
        } else {
            $ret['php']['gd']['value']  = '<span class="glyphicon glyphicon-remove text-danger"></span>';
            $ret['php']['gd']['class']  = 'danger';
            $ret['php']['gd']['failed'] = true;
            $ret['php']['gd']['remark'] = '没有启用GD, 将无法正常上传和压缩图片, 系统无法正常运行. ';
        }
        
        $ret['php']['openssl']['ok'] = extension_loaded('openssl');
        if ($ret['php']['openssl']['ok']) {
            $ret['php']['openssl']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
            $ret['php']['openssl']['class'] = 'success';
        } else {
            $ret['php']['openssl']['value']  = '<span class="glyphicon glyphicon-remove text-danger"></span>';
            $ret['php']['openssl']['class']  = 'danger';
            $ret['php']['openssl']['failed'] = true;
            $ret['php']['openssl']['remark'] = '没有启用openssl扩展. ';
        }
        
        
        $ret['php']['session']['ok'] = ini_get('session.auto_start');
        if ($ret['php']['session']['ok'] == 0 || strtolower($ret['php']['session']['ok']) == 'off') {
            $ret['php']['session']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
            $ret['php']['session']['class'] = 'success';
        } else {
            $ret['php']['session']['value']  = '<span class="glyphicon glyphicon-remove text-danger"></span>';
            $ret['php']['session']['class']  = 'danger';
            $ret['php']['session']['failed'] = true;
            $ret['php']['session']['remark'] = '系统session.auto_start开启, 将无法正常注册会员, 系统无法正常运行. ';
        }
        
        $ret['php']['asp_tags']['ok'] = ini_get('asp_tags');
        if (empty($ret['php']['asp_tags']['ok']) || strtolower($ret['php']['asp_tags']['ok']) == 'off') {
            $ret['php']['asp_tags']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
            $ret['php']['asp_tags']['class'] = 'success';
        } else {
            $ret['php']['asp_tags']['value']  = '<span class="glyphicon glyphicon-remove text-danger"></span>';
            $ret['php']['asp_tags']['class']  = 'danger';
            $ret['php']['asp_tags']['failed'] = true;
            $ret['php']['asp_tags']['remark'] = '请禁用可以使用ASP 风格的标志，配置php.ini中asp_tags = Off';
        }
        
        
        $ret['write']['root']['ok'] = $this->isDirWriteable($this->app->getRootPath() . 'public/uploads');
        if ($ret['write']['root']['ok']) {
            $ret['write']['root']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
            $ret['write']['root']['class'] = 'success';
        } else {
            $ret['write']['root']['value']  = '<span class="glyphicon glyphicon-remove text-danger"></span>';
            $ret['write']['root']['class']  = 'danger';
            $ret['write']['root']['failed'] = true;
            $ret['write']['root']['remark'] = 'public/uploads 无法写入, 无法使用文件上传功能, 系统无法正常运行.  ';
        }
        $ret['write']['data']['ok'] = $this->isDirWriteable($this->app->getRootPath() . 'runtime');
        if ($ret['write']['data']['ok']) {
            $ret['write']['data']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
            $ret['write']['data']['class'] = 'success';
        } else {
            $ret['write']['data']['value']  = '<span class="glyphicon glyphicon-remove text-danger"></span>';
            $ret['write']['data']['class']  = 'danger';
            $ret['write']['data']['failed'] = true;
            $ret['write']['data']['remark'] = 'runtime 目录无法写入, 将无法写入配置文件, 系统无法正常安装. ';
        }
        $ret['write']['install']['ok'] = $this->isDirWriteable($this->app->getRootPath() . 'public/install');
        if ($ret['write']['install']['ok']) {
            $ret['write']['install']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
            $ret['write']['install']['class'] = 'success';
        } else {
            $ret['write']['install']['value']  = '<span class="glyphicon glyphicon-remove text-danger"></span>';
            $ret['write']['install']['class']  = 'danger';
            $ret['write']['install']['failed'] = true;
            $ret['write']['install']['remark'] = 'public/install 目录无法写入, 将无法写入安装文件, 系统无法正常安装. ';
        }
        $ret['write']['database']['ok'] = is_writable($this->app->getRootPath() . '.env');
        if ($ret['write']['database']['ok']) {
            $ret['write']['database']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
            $ret['write']['database']['class'] = 'success';
        } else {
            $ret['write']['database']['value']  = '<span class="glyphicon glyphicon-remove text-danger"></span>';
            $ret['write']['database']['class']  = 'danger';
            $ret['write']['database']['failed'] = true;
            $ret['write']['database']['remark'] = '.env 文件无法写入, 将无法写入数据库配置文件, 系统无法正常安装. ';
        }
        
        $ret['continue'] = true;
        foreach ($ret['php'] as $opt) {
            if ($opt['failed']) {
                $ret['continue'] = false;
                break;
            }
        }
        foreach ($ret['write'] as $opt) {
            if ($opt['failed']) {
                $ret['continue'] = false;
                break;
            }
        }
        
        $this->assign('ret', $ret);
        
        return $this->display();
    }
    
    
    /**
     * 配置数据库
     */
    public function db()
    {
        // 安装数据库
        if ($this->isPost() && $this->iPost('action') === 'install') {
            set_time_limit(0);
            
            $db    = Filter::trim($this->iPost('db'));
            $user  = Filter::trim($this->iPost('user'));
            $site  = Filter::trim($this->iPost('site'));
            $mysql = null;
            try {
                $mysql = $this->mysqlInit($db['server'], $db['username'], $db['password'], $db['port']);
                $mysql->query("SET character_set_connection=utf8mb4, character_set_results=utf8mb4, character_set_client=binary");
                $mysql->query("SET sql_mode=''");
                if ($mysql->error) {
                    throw new Exception($mysql->error);
                }
                
                // 创建数据库
                $showDBSQL = "SHOW DATABASES LIKE  '{$db['name']}';";
                if (!$mysql->query($showDBSQL)->fetch_assoc()) {
                    $mysql->query("CREATE DATABASE IF NOT EXISTS `{$db['name']}` DEFAULT CHARACTER SET utf8mb4");
                }
                
                // 检测数据库
                if (!$mysql->query($showDBSQL)->fetch_assoc()) {
                    throw new Exception('数据库不存在且创建数据库失败.');
                }
                
                if ($mysql->errno) {
                    throw new Exception($mysql->error);
                }
                
                // 选择创建好的数据
                $mysql->select_db($db['name']);
                $mysql->query("SET character_set_connection=utf8mb4, character_set_results=utf8mb4, character_set_client=binary");
                $mysql->query("SET sql_mode=''");
                
                
                // 读取SQL文件
                $title  = trim($site['title']);
                $title  = $title ? $title : 'BusyPHP';
                $length = strlen($title);
                $sql    = $this->parseSql(App::getBusyPath('data') . 'install.sql', $db['prefix'], [
                    '#__username__#',
                    '#__password__#',
                    '#__create_time__#',
                    '#__title__#'
                ], [
                    $user['username'],
                    AdminUser::createPassword($user['password']),
                    time(),
                    "s:{$length}:\"{$title}\""
                ]);
                
                foreach ($sql as $item) {
                    $item = trim($item);
                    if (!$item) {
                        continue;
                    }
                    
                    $res = $mysql->query($item);
                    if (!$res) {
                        throw new Exception("安装基本数据库SQL语句错误: [{$mysql->errno}] {$mysql->error}");
                    }
                }
                
                
                // 读取扩展SQL文件
                $extendSqlFile = App::getPublicPath('install') . 'sql.sql';
                if (is_file($extendSqlFile)) {
                    $extendSql = $this->parseSql($extendSqlFile, $db['prefix']);
                    foreach ($extendSql as $item) {
                        $item = trim($item);
                        if (!$item) {
                            continue;
                        }
                        
                        $res = $mysql->query($item);
                        if (!$res) {
                            throw new Exception("安装扩展数据库SQL语句错误: [{$mysql->errno}] {$mysql->error}");
                        }
                    }
                }
                
                // 配置.env文件
                $this->configEnv($db);
                
                // 创建锁文件
                $time = date('Y-m-d H:i:s');
                File::write($this->lockFile, "该文件为系统安装完毕后生成，如果需要重新安装，请删除该文件\n\n安装时间: {$time}");
                
                // 生成缓存
                SystemConfig::init()->refreshCache();
                Setting::createConfig();
            } catch (Exception $e) {
                return $this->error($e->getMessage());
            }
            if ($mysql instanceof mysqli) {
                $mysql->close();
            }
            
            return $this->redirect(url('general/install/finish'));
        }
        
        
        // 校验数据库
        if ($this->isAjax()) {
            $host  = $this->iRequest('db_host', 'trim');
            $user  = $this->iRequest('db_user', 'trim');
            $pass  = $this->iRequest('db_pass', 'trim');
            $port  = $this->iRequest('db_port', 'trim');
            $mysql = null;
            try {
                $mysql = $this->mysqlInit($host, $user, $pass, $port);
                if (false === $result = $mysql->query("SELECT `SCHEMA_NAME` FROM `information_schema`.`SCHEMATA`")) {
                    throw new Exception($mysql->error);
                }
                $databases = [];
                while ($vo = $result->fetch_array()) {
                    $databases[] = $vo[0];
                }
                $result = ['code' => '200', 'data' => implode(',', $databases)];
            } catch (Exception $e) {
                $result = ['code' => '100', 'msg' => $e->getMessage()];
            }
            
            if ($mysql instanceof mysqli) {
                $mysql->close();
            }
            
            return $this->json($result);
        }
        
        return $this->display();
    }
    
    
    /**
     * 安装完成
     */
    public function finish()
    {
        $this->assign('finish', true);
        
        return $this->display();
    }
}