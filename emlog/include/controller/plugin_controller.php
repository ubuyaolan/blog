<?php
/**
 * 前台加载插件页面
 *
 * @copyright (c) Emlog All Rights Reserved
 * $Id: plugin_controller.php 1792 2010-10-21 14:50:08Z emloog $
 */

class Plugin_Controller {

    /**
     * 前台加载插件页面
     */
    function loadPluginShow($params) {
        $plugin = isset($params[1]) && $params[1] == 'plugin' ? addslashes($params[2]) : '' ;
        if (preg_match("/^[\w\-]+$/", $plugin) && file_exists(EMLOG_ROOT."/content/plugins/{$plugin}/{$plugin}_show.php")) {
            include_once("./content/plugins/{$plugin}/{$plugin}_show.php");
        }
    }
}
