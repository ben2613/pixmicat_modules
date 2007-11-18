<?php
/*
mod_threadlist : 討論串列表
By: scribe
*/

class mod_threadlist{
	var $THREADLIST_NUMBER,$THREADLIST_NUMBER_IN_MAIN,$SHOW_IN_MAIN;

	function mod_threadlist(){
		global $PMS;
		$PMS->hookModuleMethod('ModulePage', 'mod_threadlist'); // 向系統登記模組專屬獨立頁面

		$this->THREADLIST_NUMBER = 50; // 一頁顯示列表個數
		$this->THREADLIST_NUMBER_IN_MAIN = 20; // 在主頁面顯示列表個數
		$this->SHOW_IN_MAIN = true; // 在主頁面顯示
	}

	/* Get the name of module */
	function getModuleName(){
		return 'mod_threadlist : 討論串列表';
	}

	/* Get the module version infomation */
	function getModuleVersionInfo(){
		return '4th.Release.2 (v071115)';
	}

	/* 自動掛載：頂部連結列 */
	function autoHookToplink(&$linkbar, $isReply){
		global $PMS;
		$linkbar .= '[<a href="'.$PMS->getModulePageURL('mod_threadlist').'">主題列表</a>]'."\n";
	}

	function autoHookThreadFront(&$txt,$isReply){
		global $PMS, $PIO, $FileIO;
		if($this->SHOW_IN_MAIN && !$isReply) {
			$dat = ''; // HTML Buffer
			$plist = $PIO->fetchThreadList(0, $this->THREADLIST_NUMBER_IN_MAIN, true); // 編號由大到小排序
			$post = $PIO->fetchPosts($plist); // 取出資料
			$post_count = ceil(count($post) / 2);
		    $dat .= '<div id="topiclist" style="text-align: center; clear: both;">
<table cellpadding="1" cellspacing="1" border="0" width="100%" style="margin: 0px auto; text-align: left; margin-bottom: 1em; font-size: 1em;">
<tr><th class="reply_hl" colspan="2" style="text-align: center;">題名一覽</th></tr>
';
			for($i = 0; $i < $post_count; $i++){
				$leftStr = $post[$i]['no'].': <a href="'.PHP_SELF.'?res='.$post[$i]['no'].'">'.$post[$i]['sub'].' ('.($PIO->postCount($post[$i]['no']) - 1).')</a>';
				$rightStr = isset($post[$i + $post_count]) ? $post[$i + $post_count]['no'].': <a href="'.PHP_SELF.'?res='.$post[$i + $post_count]['no'].'">'.$post[$i + $post_count]['sub'].' ('.($PIO->postCount($post[$i + $post_count]['no']) - 1).')</a>' : '';
				$dat .= '<tr class="ListRow'.($i % 2 + 1).'_bg"><td style="width: 50%">'.$leftStr.'</td><td>'.$rightStr.'</td></tr>'."\n";
			}
$dat .= '</table>
</div>

';
		    $txt .= $dat;
		}
	}

	/* 模組獨立頁面 */
	function ModulePage(){
		global $PMS, $PIO, $FileIO;

		$thisPage = $PMS->getModulePageURL('mod_threadlist'); // 基底位置
		$dat = ''; // HTML Buffer
		$listMax = $PIO->threadCount(); // 討論串總筆數
		$pageMax = ceil($listMax / $this->THREADLIST_NUMBER) - 1; // 分頁最大編號
		$page = isset($_GET['page']) ? intval($_GET['page']) : 0; // 目前所在分頁頁數
		if($page < 0 || $page > $pageMax) exit('Page out of range.'); // $page 超過範圍
		$plist = $PIO->fetchThreadList($this->THREADLIST_NUMBER * $page, $this->THREADLIST_NUMBER, true); // 編號由大到小排序
		$post = $PIO->fetchPosts($plist); // 取出資料
		$post_count = count($post);

		head($dat);
		$dat .= '<div id="contents">
[<a href="'.PHP_SELF2.'?'.time().'">回到版面</a>]
<div class="bar_reply">列表模式</div>

<table align="center" width="97%">
<tr><th>No.</th><th width="50%">標題</th><th>發文者</th><th>回應</th><th>日期</th></tr>
';
		// 逐步取資料
		for($i = 0; $i < $post_count; $i++){
			list($no, $sub, $name, $now) = array($post[$i]['no'], $post[$i]['sub'],$post[$i]['name'], $post[$i]['now']);
			$dat .= '<tr class="ListRow'.($i % 2 + 1).'_bg"><td>'.$no.'</td><td><a href="'.PHP_SELF.'?res='.$no.'">'.$sub.'</a></td><td>'.$name.'</td><td>'.($PIO->postCount($no) - 1).'</td><td>'.$now.'</td></tr>'."\n";
		}

		$dat .= '</table>
</div>

<hr />

<div id="page_switch">
<table border="1" style="float: left;"><tr>
';
		if($page) $dat .= '<td><a href="'.$thisPage.'&amp;page='.($page - 1).'">上一頁</a></td>';
		else $dat .= '<td style="white-space: nowrap;">第一頁</td>';
		$dat .= '<td>';
		for($i = 0; $i <= $pageMax; $i++){
			if($i==$page) $dat .= '[<b>'.$i.'</b>] ';
			else $dat .= '[<a href="'.$thisPage.'&amp;page='.$i.'">'.$i.'</a>] ';
		}
		$dat .= '</td>';
		if($page < $pageMax) $dat .= '<td><a href="'.$thisPage.'&amp;page='.($page + 1).'">下一頁</a></td>';
		else $dat .= '<td style="white-space: nowrap;">最後一頁</td>';
		$dat .= '
</tr></table>
</div>

';
		foot($dat);
		echo $dat;
	}
}
?>