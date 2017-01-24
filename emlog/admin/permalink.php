<?php
/**
 * 固定连接
 * @copyright (c) Emlog All Rights Reserved
 * $Id: permalink.php 1863 2011-03-22 12:02:08Z emloog $
 */

require_once 'globals.php';

if ($action == '') {
	$ex0 = $ex1 = $ex2 = $ex3 = '';
	$t = 'ex'.Option::get('isurlrewrite');
	$$t = 'checked="checked"';

	$isalias = Option::get('isalias') == 'y' ? 'checked="checked"' : '';
	$isalias_html = Option::get('isalias_html') == 'y' ? 'checked="checked"' : '';

	include View::getView('header');
	require_once(View::getView('permalink'));
	include View::getView('footer');
	View::output();
}

if ($action == 'update') {
	$permalink = isset($_POST['permalink']) ? addslashes($_POST['permalink']) : '0';
	$isalias = isset($_POST['isalias']) ? addslashes($_POST['isalias']) : 'n';
	$isalias_html = isset($_POST['isalias_html']) ? addslashes($_POST['isalias_html']) : 'n';

	if($permalink != '0' || $isalias == 'y'){
        if(!file_exists(EMLOG_ROOT.'/.appconfig')
             || !preg_match('#rewriterule#i',file_get_contents(EMLOG_ROOT.'/.appconfig'))) {
			header('Location: ./permalink.php?error=true');
			exit;
        }
	}

	Option::updateOption('isurlrewrite', $permalink);
	Option::updateOption('isalias', $isalias);
	Option::updateOption('isalias_html', $isalias_html);
	$CACHE->updateCache('options');
	header('Location: ./permalink.php?activated=true');
}
