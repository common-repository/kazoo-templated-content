<?php
/*  
Module: KudzuLib_WP_FEED.php
Description: Kazoo library to handle FEEDS.
Author: Andrew Friedl
Author URI: http://www.kazooplugin.com/
License: GPLv2 (see license.txt)
*/
include_once(ABSPATH.WPINC . '/feed.php');
class CKazooLibWPFEED {
	function get_feed($url) {
		return fetch_feed($url);	
	}
	function tag_WPFEED($node) {
		if (!$node->assertParamCount(1,"url[|start|count]"))
			return;
		$eng = $node->getEngine();
		$rss_url = $node->getParamItem(0,'');
		$rss_start = $node->getParamItem(1,1);
		$rss_count = $node->getParamItem(2,5);
		$dateorder = $node->getParamItem(3,false);
		$eng->putValue('feed_start',$rss_start);
		$eng->putValue('feed_count',$rss_count);
		$eng->putValue('feed_url',$rss_url);
		$feed = $this->get_feed($rss_url);
		if (is_wp_error($feed)) {
			$eng->putValue('feed_error',$feed->get_error_message());
			$node->evalChildNode('Error');
			return;
		}
		$feed->enable_order_by_date($dateorder);
		$limit = $feed->get_item_quantity($rss_count);
		$rss_items = $feed->get_items($rss_start,$rss_count);
		if ($limit == 0 || empty($rss_items)) {
			$node->evalChildNode('Empty');
			return;
		}	
		$node->evalChildNode("Head");
		foreach ( $rss_items as $rss_item ) {
			$eng->putValue('feed_title',$rss_item->get_title());
			$eng->putValue('feed_description',$rss_item->get_description());
			$eng->putValue('feed_content',$rss_item->get_content());
			$eng->putValue('feed_category',$rss_item->get_category());
			$eng->putValue('feed_id',$rss_item->get_id());
			$eng->putValue('feed_link',$rss_item->get_link());
			$node->stackPush();
			$node->evalChildNode("Body");
			$node->stackPop();
		}
		$node->evalChildNode("Foot");
	}	
	function setTags($tagLib) {
		$tagLib->setTagFn('wp_feed','tag_WPFEED',$this);
		$tagLib->setTagFn('wpfeed','tag_WPFEED',$this);
		$tagLib->setTagFn('feed','tag_WPFEED',$this);
	}
}
function KudzuLibImport_WP_FEED($tagLib) {
	$obj = new CKazooLibWPFEED();
	$obj->setTags($tagLib);
}
?>