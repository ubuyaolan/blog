<?php
/**
 * 安装程序
 * @copyright (c) Emlog All Rights Reserved
 * $Id: install.php 2029 2011-09-11 01:35:16Z emloog $
 */

define('EMLOG_ROOT', dirname(__FILE__));
define('DEL_INSTALLER', 1);

require_once EMLOG_ROOT.'/include/lib/function.sae.base.php';
require_once EMLOG_ROOT.'/sae.config.php';

$DB = MySql::getInstance();
$sql="show tables like '%".DB_PREFIX."options%'";//判断表是否存在！
if($DB->num_rows($DB->query($sql)) == 1)
{
	header("location: ./index.php");
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
doStripslashes();

$act = isset($_GET['action'])? $_GET['action'] : '';

if (PHP_VERSION < '5.0'){
    emMsg('emlog从3.5开始不再支持您当前的 PHP'.PHP_VERSION.' 环境，请您选用支持 PHP5 的主机，或下载 emlog3.4 安装。');
}

if(!$act){
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>emlog</title>
<style type="text/css">
<!--
body {background-color:#F7F7F7;font-family: Arial;font-size: 12px;line-height:150%;}
.main {background-color:#FFFFFF;margin-top:20px;font-size: 12px;color: #666666;width:750px;margin:0px auto;padding:10px;list-style:none;border:#DFDFDF 1px solid; border-radius: 6px;}
.logo{background:url(admin/views/images/logo.gif) no-repeat center;padding:30px 0px 30px 0px;margin:30px 0px;}
.title{text-align:center;}
.title span{font-size:24px;font-weight:bold;}
.input {border: 1px solid #CCCCCC;font-family: Arial;font-size: 18px;height:28px;background-color:#F7F7F7;color: #666666;margin:0px 0px 0px 25px;}
.submit{cursor: pointer;font-size: 12px;padding: 4px 10px;}
.care{color:#0066CC;}
.title2{font-size:14px;color:#000000;border-bottom: #CCCCCC 1px solid; margin:20px 0px;}
.foot{text-align:center;}
.main li{ margin:20px 0px;}
-->
</style>
</head>
<body>
<form name="form1" method="post" action="sae.install.php?action=install">
<div class="main">
<p class="logo"></p>
<p class="title"><span>emlog<?php echo Option::EMLOG_VERSION ?></span> 安装程序</p>
<div class="c">
<p class="title2">博主设置 （用于安装成功后登录博客）</p>
<li>
博主登录名：<br />
<input name="admin" type="text" class="input">
</li>
<li>
博主登录密码：<br />
<input name="adminpw" type="password" class="input">
<span class="care">(不小于6位)</span>
</li>
<li>
再次输入博主登录密码：<br />
<input name="adminpw2" type="password" class="input">
</li>
</div>
<div>
<p class="foot">
<input type="submit" class="submit" value="确 定">
<input type="reset" class="submit" value="重 置">
</p>
</div>
<div><p class="foot">Powered by <a href="http://www.emlog.net">emlog</a></p></div>
</div>
</form>
</body>
</html>
<?php
}
if($act == 'install' || $act == 'reinstall')
{
	$admin = isset($_POST['admin']) ? addslashes(trim($_POST['admin'])) : '';
	$adminpw = isset($_POST['adminpw']) ? addslashes(trim($_POST['adminpw'])) : '';
	$adminpw2 = isset($_POST['adminpw2']) ? addslashes(trim($_POST['adminpw2'])) : '';
	$result = '';

	if($admin == '' || $adminpw == ''){
		emMsg('博主登录名和密码不能为空!');
	}elseif(strlen($adminpw) < 6){
		emMsg('博主登录密码不得小于6位');
	}elseif($adminpw!=$adminpw2)	 {
		emMsg('两次输入的密码不一致');
	}

	$DB = MySql::getInstance();
	$CACHE = Cache::getInstance();

	//密码加密存储
	$PHPASS = new PasswordHash(8, true);
	$adminpw = $PHPASS->HashPassword($adminpw);

	$dbcharset = 'utf8';
	$type = 'MYISAM';
	$add = $DB->getMysqlVersion() > '4.1' ? 'ENGINE='.$type.' DEFAULT CHARSET='.$dbcharset.';':'TYPE='.$type.';';
	$setchar = $DB->getMysqlVersion() > '4.1' ? "ALTER DATABASE `".DB_NAME."` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;" : '';

	$widgets = Option::getWidgetTitle();
    $sider_wg = Option::getDefWidget();

	$widget_title = serialize($widgets);
	$widgets = serialize($sider_wg);

	define('BLOG_URL', getBlogUrl());

	$sql = $setchar."
DROP TABLE IF EXISTS ".DB_PREFIX."blog;
CREATE TABLE ".DB_PREFIX."blog (
  gid mediumint(8) unsigned NOT NULL auto_increment,
  title varchar(255) NOT NULL default '',
  date bigint(20) NOT NULL,
  content longtext NOT NULL,
  excerpt longtext NOT NULL,
  alias VARCHAR(200) NOT NULL DEFAULT '',
  author int(10) NOT NULL default '1',
  sortid tinyint(3) NOT NULL default '-1',
  type varchar(20) NOT NULL default 'blog',
  views mediumint(8) unsigned NOT NULL default '0',
  comnum mediumint(8) unsigned NOT NULL default '0',
  tbcount mediumint(8) unsigned NOT NULL default '0',
  attnum mediumint(8) unsigned NOT NULL default '0',
  top enum('n','y') NOT NULL default 'n',
  hide enum('n','y') NOT NULL default 'n',
  allow_remark enum('n','y') NOT NULL default 'y',
  allow_tb enum('n','y') NOT NULL default 'y',
  password varchar(255) NOT NULL default '',
  PRIMARY KEY  (gid),
  KEY date (date),
  KEY author (author),
  KEY sortid (sortid),
  KEY type (type),
  KEY hide (hide)
)".$add."
INSERT INTO ".DB_PREFIX."blog (gid,title,date,content,excerpt,author,views,comnum,attnum,tbcount,top,hide, allow_remark,allow_tb,password) VALUES (1, '欢迎使用emlog', '1230508801', '从今天起，做一个幸福的人。', '', 1, 0, 0, 0, 0, 'n', 'n', 'y', 'y', '');
DROP TABLE IF EXISTS ".DB_PREFIX."attachment;
CREATE TABLE ".DB_PREFIX."attachment (
  aid smallint(5) unsigned NOT NULL auto_increment,
  blogid mediumint(8) unsigned NOT NULL default '0',
  filename varchar(255) NOT NULL default '',
  filesize int(10) NOT NULL default '0',
  filepath varchar(255) NOT NULL default '',
  addtime bigint(20) NOT NULL,
  PRIMARY KEY  (aid),
  KEY blogid (blogid)
)".$add."
DROP TABLE IF EXISTS ".DB_PREFIX."comment;
CREATE TABLE ".DB_PREFIX."comment (
  cid mediumint(8) unsigned NOT NULL auto_increment,
  gid mediumint(8) unsigned NOT NULL default '0',
  pid mediumint(8) unsigned NOT NULL default '0',
  date bigint(20) NOT NULL,
  poster varchar(20) NOT NULL default '',
  comment text NOT NULL,
  mail varchar(60) NOT NULL default '',
  url varchar(75) NOT NULL default '',
  ip varchar(128) NOT NULL default '',
  hide enum('n','y') NOT NULL default 'n',
  PRIMARY KEY  (cid),
  KEY gid (gid),
  KEY hide (hide)
)".$add."
DROP TABLE IF EXISTS ".DB_PREFIX."options;
CREATE TABLE ".DB_PREFIX."options (
option_id INT( 11 ) UNSIGNED NOT NULL auto_increment,
option_name VARCHAR( 255 ) NOT NULL ,
option_value LONGTEXT NOT NULL ,
PRIMARY KEY (option_id),
KEY option_name (option_name)
)".$add."
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('blogname','点滴记忆');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('bloginfo','美好的生活需要用心记录');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('site_key','emlog');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('blogurl','".BLOG_URL."');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('icp','');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('footer_info','');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('admin_perpage_num','15');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('rss_output_num','10');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('rss_output_fulltext','y');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('index_lognum','10');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('index_comnum','10');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('index_twnum','10');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('index_newtwnum','5');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('index_newlognum','5');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('index_randlognum','5');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('comment_subnum','20');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('nonce_templet','default');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('admin_style','default');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('tpl_sidenum','1');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('comment_code','n');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('isgravatar','y');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('comment_paging','n');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('comment_pnum','20');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('comment_order','newer');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('login_code','n');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('reply_code','n');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('ischkcomment','n');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('ischkreply','n');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('isurlrewrite','0');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('isalias','n');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('isalias_html','n');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('isgzipenable','n');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('istrackback','y');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('isxmlrpcenable','n');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('istwitter','y');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('twnavi','碎语');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('topimg','content/templates/default/images/top/default.jpg');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('custom_topimgs','a:0:{}');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('timezone','8');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('active_plugins','a:1:{i:0;s:13:\"tips/tips.php\";}');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('navibar','a:0:{}');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('widget_title','$widget_title');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('custom_widget','a:0:{}');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('widgets1','$widgets');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('widgets2','');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('widgets3','');
INSERT INTO ".DB_PREFIX."options (option_name, option_value) VALUES ('widgets4','');
DROP TABLE IF EXISTS ".DB_PREFIX."link;
CREATE TABLE ".DB_PREFIX."link (
  id smallint(4) unsigned NOT NULL auto_increment,
  sitename varchar(30) NOT NULL default '',
  siteurl varchar(75) NOT NULL default '',
  description varchar(255) NOT NULL default '',
  taxis smallint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (id)
)".$add."
INSERT INTO ".DB_PREFIX."link (id, sitename, siteurl, description, taxis) VALUES (1, 'emlog', 'http://www.emlog.net', 'emlog官方主页', 0);
DROP TABLE IF EXISTS ".DB_PREFIX."tag;
CREATE TABLE ".DB_PREFIX."tag (
  tid mediumint(8) unsigned NOT NULL auto_increment,
  tagname varchar(60) NOT NULL default '',
  gid text NOT NULL,
  PRIMARY KEY  (tid),
  KEY tagname (tagname)
)".$add."
DROP TABLE IF EXISTS ".DB_PREFIX."sort;
CREATE TABLE ".DB_PREFIX."sort (
  sid tinyint(3) unsigned NOT NULL auto_increment,
  sortname varchar(255) NOT NULL default '',
  alias VARCHAR(200) NOT NULL DEFAULT '',
  taxis smallint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (sid)
)".$add."
DROP TABLE IF EXISTS ".DB_PREFIX."trackback;
CREATE TABLE ".DB_PREFIX."trackback (
  tbid mediumint(8) unsigned NOT NULL auto_increment,
  gid mediumint(8) unsigned NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  date bigint(20) NOT NULL,
  excerpt text NOT NULL,
  url varchar(255) NOT NULL default '',
  blog_name varchar(255) NOT NULL default '',
  ip varchar(16) NOT NULL default '',
  PRIMARY KEY  (tbid),
  KEY gid (gid)
)".$add."
DROP TABLE IF EXISTS ".DB_PREFIX."twitter;
CREATE TABLE ".DB_PREFIX."twitter (
id INT NOT NULL AUTO_INCREMENT,
content text NOT NULL,
author int(10) NOT NULL default '1',
date bigint(20) NOT NULL,
replynum mediumint(8) unsigned NOT NULL default '0',
PRIMARY KEY (id),
KEY author (author)
)".$add."
DROP TABLE IF EXISTS ".DB_PREFIX."reply;
CREATE TABLE ".DB_PREFIX."reply (
  id mediumint(8) unsigned NOT NULL auto_increment,
  tid mediumint(8) unsigned NOT NULL default '0',
  date bigint(20) NOT NULL,
  name varchar(20) NOT NULL default '',
  content text NOT NULL,
  hide enum('n','y') NOT NULL default 'n',
  ip varchar(128) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY gid (tid),
  KEY hide (hide)
)".$add."
DROP TABLE IF EXISTS ".DB_PREFIX."user;
CREATE TABLE ".DB_PREFIX."user (
  uid tinyint(3) unsigned NOT NULL auto_increment,
  username varchar(32) NOT NULL default '',
  password varchar(64) NOT NULL default '',
  nickname varchar(20) NOT NULL default '',
  role varchar(60) NOT NULL default '',
  photo varchar(255) NOT NULL default '',
  email varchar(60) NOT NULL default '',
  description varchar(255) NOT NULL default '',
PRIMARY KEY  (uid),
KEY username (username)
)".$add."
INSERT INTO ".DB_PREFIX."user (uid, username, password, role) VALUES (1,'$admin','".$adminpw."','admin');";

	$array_sql = preg_split("/;[\r\n]/", $sql);
	foreach($array_sql as $sql)
	{
		$sql = trim($sql);
		if ($sql)
		{
			if (strstr($sql, 'CREATE TABLE'))
			{
				preg_match('/CREATE TABLE ([^ ]*)/', $sql, $matches);
				$ret = $DB->query($sql);
				if ($ret)
				{
					$result .= '数据库表：'.$matches[1].' 创建成功<br />';
				}
			} else {
				$ret = $DB->query($sql);
			}
		}
	}
	//重建缓存
	$CACHE->updateCache();
	$result .= "博主: {$admin} 添加成功<br />恭喜你！emlog 安装成功<br />";
	if (DEL_INSTALLER === 1 && !@unlink('./install.php') || DEL_INSTALLER === 0) {
	    $result .= '<span style="color:red;"></span> ';
	}
	$result .= '<a href="./"> 进入emlog </a>';
	emMsg($result);
}
?>
