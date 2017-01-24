<?php 
/*
* 首页日志列表部分
*/
if(!defined('EMLOG_ROOT')) {exit('error!');} 
?>
<div id="content">
<?php doAction('index_loglist_top'); ?>
<ul>
<?php foreach($logs as $value): ?>
	<li>
	<h2 class="content_h2">
	<?php topflg($value['top']); ?><a href="<?php echo $value['log_url']; ?>"><?php echo $value['log_title']; ?></a>
	</h2>
	<div class="act"><?php blog_sort($value['logid']); ?> </div>
	<div class="editor"><?php editflg($value['logid'],$value['author']); ?></div>
	<div class="clear line"></div>
   	<div class="bloger">post by <?php blog_author($value['author']); ?> / <?php echo gmdate('Y-n-j G:i l', $value['date']); ?></div>
	<div class="post"><?php echo $value['log_description']; ?></div>
	<div class="fujian"><?php blog_att($value['logid']); ?></div>
	<div class="under">
	<div class="top"></div>
	<div class="under_p">
	<div class="tag"><?php blog_tag($value['logid']); ?></div>
	<div>
	<a href="<?php echo $value['log_url']; ?>#comment">评论(<?php echo $value['comnum']; ?>)</a>
	<a href="<?php echo $value['log_url']; ?>#tb">引用(<?php echo $value['tbcount']; ?>)</a>
	<a href="<?php echo $value['log_url']; ?>">浏览(<?php echo $value['views']; ?>)</a>
	</div>
	</div>
	<div class="bottom"></div>
	</div>
	</li>
<?php endforeach; ?>
</ul>
<div id="pagenavi">
	<?php echo $page_url;?>
</div>
</div>
<!--end content-->
<?php
 include View::getView('side');
 include View::getView('footer');
?>