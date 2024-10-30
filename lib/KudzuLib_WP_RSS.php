<?php
/*  
Module: KudzuLib_WP_RSS.php
Description: Kazoo library to handle RSS feeds.
Author: Andrew Friedl
Author URI: http://www.kazooplugin.com/
License: GPLv2 (see license.txt)
*/
include_once(ABSPATH.WPINC.'/rss.php');

// Library Class Definition
class CKazooLibWPRSS {
	function tag_WPRSS($node) {
		if (!$node->assertParamCount(1,"url[|start|count]"))
			return;
		$rss_url = $node->getParamItem(0,'');
		$rss_start = $node->getParamItem(1,1);
		$rss_stop = $node->getParamItem(2,5);
		$rss_stop += $rss_start;
		$eng = $node->getEngine();
		$eng->putValue('rss_start',$rss_start);
		$eng->putValue('rss_stop',$rss_stop);
		$eng->putValue('rss_url',$rss_url);
		$feed = fetch_rss($rss_url);
		$rss_items = array_slice($feed->items,$rss_start,$rss_stop+1);
		if (empty($rss_items)) {
			$node->evalChildNode('Empty');
			return;
			}
		$node->evalChildNode("Head");
		for ( $idx = $rss_start; $idx <= $rss_stop; $idx++ ) {
			$rss_item = $rss_items[$idx];
			$keys = array_keys($rss_item);
			foreach($keys as $key) {
				$eng->putValue('rss_'.$key,$rss_item[$key]);
				}
			$eng->putValue('rss_last',$idx===$rss_stop);
			$node->stackPush();
			$node->evalChildNode("Body");
			$node->stackPop();
		}
		$node->evalChildNode("Foot");
	}
	function setTags($tagLib) {
		$tagLib->setTagFn('wp_rss','tag_WPRSS',$this);
		$tagLib->setTagFn('wprss','tag_WPRSS',$this);
		$tagLib->setTagFn('rss','tag_WPRSS',$this);
	}
}

// Library Tag Installation
function KudzuLibImport_WP_RSS($tagLib) {
	$obj = new CKazooLibWPRSS();
	$obj->setTags($tagLib);
}
?>