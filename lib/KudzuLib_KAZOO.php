<?php
/*
Module:  KudzuLib_KAZOO.php
Description: Kazoo Tag Handlers for Wordpress and Support Code
Author: Andrew Friedl
Author URI: http://www.kazooplugin.com/
License: GPLv2 (see license.txt)
*/

// Library Class Definition
class CKudzuLibKAZOO 
{
	function fnIsVoid($node,$fn) {
		$node->getEngine()->Helper_BOOL($node,$fn());
	}
	function fnIsArgs($node,$fn) {
		$bool = $fn();
		if ($node->getParamCount()<1) {
			$this->fnIsVoid($node,$fn);
			return;
		}
		$args = array();
		for ($i=0;$i<$node->getParamCount();$i++) {
			array_push($args,$node->getParamItem($i));
		}
		$bool = $fn($args);
		$node->getEngine()->Helper_BOOL($node,$bool);
	}
	function tag_IS_ARCHIVE($node) {
		$this->fnIsArgs($node,'is_archive');
	}
	function tag_IS_AUTHOR($node) {
		$this->fnIsArgs($node,'is_author');
	}
	function tag_IS_PAGE($node) {
		$this->fnIsArgs($node,'is_page');
	}
	function tag_IS_FRONT_PAGE($node) {
		$this->fnIsVoid($node,'is_front_page');
	}
	function tag_IS_HOME($node) {
		$this->fnIsVoid($node,'is_home');
	}
	function tag_IS_POST($node) {
		$this->fnIsArgs($node,'is_post');
	}
	function tag_IS_SINGULAR($node) {
		$this->fnIsArgs($node,'is_singular');
	}
	function tag_IS_SINGLE($node) {
		$this->fnIsArgs($node,'is_single');
	}
	function tag_IS_STICKY($node) {
		$this->fnIsArgs($node,'is_sticky');
	}
	function tag_IS_TIME($node) {
		$this->fnIsVoid($node,'is_time');
	}
	function tag_IS_DATE($node) {
		$this->fnIsVoid($node,'is_date');
	}
	function tag_IS_ADMIN($node) {
		$this->fnIsVoid($node,'is_admin');
	}
	function tag_IS_ATTACHMENT($node) {
		$this->fnIsVoid($node,'is_attachment');
	}
	function tag_DO_SHORTCODES($node) {
		$node->stackPush();
		$node->evalNodes();
		$content = $node->getEngine()->getContent();
		$content = do_shortcode($content);
		$node->getEngine()->setContent($content);
		$node->stackPop();
	}
	function tag_QUERY_POSTS($node) {
		global $post;
		$temp = $post;
		$eng = $node->getEngine();
		$posts = new WP_Query();
		if ( $node->getParamCount() > 0 ) {
			$query = $node->getParamItem(0);
			$query = preg_replace("/\%parent\%/",$post->ID, $query);
			$posts->query($query);
		} else { 
			$eng->putValue('post_query','');
			$posts->query();
		}
		$eng->putValue('post_query',$query);
		if ($posts->have_posts()) { 
			$post_indx = 0;
			$node->evalChildNode("Header");
			while( $posts->have_posts() ) {
				$post_indx += 1;
				$posts->the_post();
				$eng->putValue('post_index', $post_indx);
				setup_postdata($post);
				$this->setPostDataToEngine( $eng );
				$node->stackPush();
				$node->evalChildNode("Post");
				$node->stackPop();
			}
			$node->evalChildNode("Footer");
		} else {
			$node->evalChildNode("Else");
		}
		$post = $temp;
	}
	function tag_GET_PAGES( $node ) {
		global $post;
		$temp = $post;
		$eng = $node->getEngine();

		if ( $node->getParamCount() > 0 ) {
			$query = $node->getParamItem(0);
			$query = preg_replace('/\%parent\%/',$post->ID, $query);
			$get_pages = get_pages($query);
		} else { 
			$get_pages = get_pages();
			$query = '';
		}
		$eng->putValue('page_query',$query);
		$eng->putValue('page_count', count($posts));
		if (count($posts) > 0) { 
			$post_indx = 0;
			$node->evalChildNode("Header");
			foreach($pages as $page) {
				$page_indx += 1;
				$eng->putValue('page_index', $page_indx);
				setup_postdata($page);		
				$this->setPostDataToEngine($eng,'page');
				$node->stackPush();
				$node->evalChildNode("Page");
				$node->stackPop();
			}
			$node->evalChildNode("Footer");
		} else {
			$node->evalChildNode("Else");
		}
		$post = $temp;
	}
	function tag_GET_POSTS( $node ) {
		global $post;
		$temp_post = $post;
		$eng = $node->getEngine();

		if ( $node->getParamCount() > 0 ) {
			$query = $node->getParamItem(0);
			$query = preg_replace('/\%parent\%/',$post->ID, $query);
			$posts = get_posts($query);
		} else { 
			$eng->putValue('post_query','');
			$posts = get_posts();
		}
		$eng->putValue('post_query',$query);
		$eng->putValue('post_count', count($posts));
		if (count($posts) > 0) { 
			$post_indx = 0;
			$node->evalChildNode("Header");
			foreach($posts as $post) {
				$post_indx += 1;
				$eng->putValue('post_index', $post_indx);
				setup_postdata($post);		
				$this->setPostDataToEngine( $eng );
				$node->stackPush();
				$node->evalChildNode("Post");
				$node->stackPop();
			}
			$node->evalChildNode("Footer");
		} else {
			$node->evalChildNode("Else");
		}
		$post = $temp;
	}
	function tag_GET_OPTION($node) {
		if (!$node->assertParamCount(1,"option[|target]"))
			return;
		$eng = $node->getEngine();
		$option = $node->getParamItem(0);
		$target = $node->getParamItem(1,$option);
		$option = get_option($option);
		$eng->putValue($target,$option);
	}
	function tag_GET_COMMENTS($node) {
		global $post;
		$temp = post;
		$eng = $node->getEngine();
		if ( $node->getParamCount() > 0 ) {
			$query = $query . $node->getParamItem(0);
			$query = preg_replace('/\%parent\%/',$post->ID, $query);
		} else {
			$query = 'post_id='.$post->ID;
		}
		$coms = get_comments($query);
		if ( count($coms) == 0 ) {
			$node->evalChildNode('Else');
			$post = $temp;
			return;
		}
		$eng->putValue('comment_count',count($coms));
		$idx = 1;
		$node->evalChildNode('Header');
		foreach($coms as $com) {
			$eng->putValue('comment_user_id',$com->user_id);
			$eng->putValue('comment_author',$com->comment_author);
			$eng->putValue('comment_author_email',$com->comment_author_email);
			$eng->putValue('comment_author_url',$com->comment_author_url);
			$eng->putValue('comment_author_avatar',$com);
			$eng->putValue('comment_content',$com->comment_content);
			$eng->putValue('comment_agent',$com->comment_agent);
			$eng->putValue('comment_date',$com->comment_date);
			$eng->putValue('comment_type',$com->comment_type);
			$eng->putValue('comment_parent_id',$com->comment_parent);
			$eng->putValue('comment_idx',$idx);
			$node->evalChildNode('Comment');
			$idx++;
		}
		$post = $temp;
		$node->evalChildNode('Footer');
	}
	function tag_GET_CHILDREN($node) {
		global $post;
		$id = $post->ID;
		$temp = $post;
		$eng = $node->getEngine();
		if ( $node->getParamCount() > 0 ) {
			$query = $node->getParamItem(0);
			$query = preg_replace('/\%parent\%/',$post->ID, $query);
		} else {
			$query = 'post_id='.$post->ID;
		}
		$coms = get_children($query);
		if ( count($coms) == 0 ) {
			$node->evalChildNode('Else');
			$post = $temp;
			return;
		}
		$eng->putValue('child_count',count($coms));
		$idx = 1;
		$node->evalChildNode('Header');
		foreach($coms as $com) {
			$post = $com;
			$eng->putValue('attachment_page',get_attachment_link($post->ID));
			$eng->putValue('attachment_image',get_attachment_image($post->ID));
			$eng->putValue('attachment_image_src',$wp_get_attachment_image_src($post->ID));
			$eng->putValue('attachment_url',wp_get_attachment_url($post->ID));
			$eng->putValue('attachment_thumb_url',wp_get_attachment_thumb_url($post->ID));
			$eng->putValue('attachment_idx',$idx);
			$node->evalChildNode('Child');
			$idx++;
		}
		$node->evalChildNode('Footer');
		$post = $temp;
	}
	function tag_GET_USERDATA($node) {
		global $current_user;
		global $user_ID;
		$eng = $node->getEngine();
		if ( $node->getParamCount() > 0 ) {
			$user = $node->getParamItem(0);
			$user = preg_replace('/\%page_author\%/',$eng->getValue('page_author_id'), $user);
			$user = preg_replace('/\%post_author\%/',$eng->getValue('post_author_id'), $user);
			$user = preg_replace('/\%comm_author\%/',$eng->getValue('post_author_id'), $user);
		} else {
			$user = $user_ID;
		}
		$user_info = get_userdata($user_ID);
		$eng->putValue('display_name',$user_info->display_name);
		$eng->putValue('user_display_name',$user_info->display_name);
		$eng->putValue('user_firstname',$user_info->user_firstname);
		$eng->putValue('user_lastname',$user_info->user_lastname);
		$eng->putValue('user_login',$user_info->user_login);
		$eng->putValue('user_email',$user_info->user_email);
		$user_roles = $current_user->roles;
		$user_role = array_shift($user_roles);
		$eng->putValue('user_role',$user_role);	
	}
	function setPostDataToEngine($eng,$tag='post') {
		global $post;
		$postID = $post->ID;
		$eng->putValue($tag.'_author_id', $post->post_author);
		$eng->putValue($tag.'_author', get_the_author());
		$eng->putValue($tag.'_time', get_post_time(get_option('time_format'), false, $post, true));
		$eng->putValue($tag.'_date', get_post_time(get_option('date_format'), false, $post, true));
		$eng->putValue($tag.'_content', $post->post_content);
		$eng->putValue($tag.'_title', $post->post_title);
		$eng->putValue($tag.'_category_id', $post->post_category);
		$eng->putValue($tag.'_excerpt', $post->post_excerpt);
		$eng->putValue($tag.'_name', $post->post_name);
		$eng->putValue($tag.'_parent', $post->post_parent);
		$eng->putValue($tag.'_modified', $post->post_modified);
		$eng->putValue($tag.'_type', $post->post_type);
		$eng->putValue($tag.'_permalink', get_permalink());
		$eng->putValue('comment_status', $post->comment_status);
		$eng->putValue('comment_count', $post->comment_count);
	}
	function setTags($tagLib) {
		$tagLib->setTagFn('Is_Archive','tag_IS_ARCHIVE', $this);
		$tagLib->setTagFn('Is_Page','tag_IS_PAGE', $this);
		$tagLib->setTagFn('Is_FrontPage','tag_IS_FRONT_PAGE', $this);
		$tagLib->setTagFn('Is_Post','tag_IS_POST', $this);
		$tagLib->setTagFn('Is_Admin','tag_IS_ADMIN', $this);
		$tagLib->setTagFn('Is_Singular','tag_IS_SINGULAR', $this);
		$tagLib->setTagFn('Is_Single','tag_IS_SINGLE', $this);
		$tagLib->setTagFn('Is_Sticky','tag_IS_STICKY', $this);
		$tagLib->setTagFn('Is_Time','tag_IS_TIME', $this);
		$tagLib->setTagFn('Is_Date','tag_IS_DATE', $this);
		$tagLib->setTagFn('Is_Home','tag_IS_HOME', $this);
		$tagLib->setTagFn('Is_Attachment','tag_IS_ATTACHMENT', $this);
		$tagLib->setTagFn('ShortCodes','tag_Do_SHORTCODES',$this);
		$tagLib->setTagFn('Do_ShortCodes','tag_Do_SHORTCODES',$this);
		$tagLib->setTagFn('GetOption','tag_GET_OPTION',$this);
		$tagLib->setTagFn('Get_Option','tag_GET_OPTION',$this);
		$tagLib->setTagFn('GetPosts','tag_GET_POSTS',$this);
		$tagLib->setTagFn('Comments','tag_GET_COMMENTS',$this);
		$tagLib->setTagFn('Get_Comments','tag_GET_COMMENTS',$this);
		$tagLib->setTagFn('GetComments','tag_GET_COMMENTS',$this);
		$tagLib->setTagFn('Get_Children','tag_GET_COMMENTS',$this);
		$tagLib->setTagFn('Children','tag_GET_COMMENTS',$this);
		$tagLib->setTagFn('GetUserData','tag_GET_USERDATA',$this);
		$tagLib->setTagFn('Get_UserData','tag_GET_USERDATA',$this);
		$tagLib->setTagFn('QueryPosts','tag_QUERY_POSTS',$this);
		$tagLib->setTagFn('Query_Posts','tag_QUERY_POSTS',$this);
	}
}

// Library Tag Installation
function KudzuLibImport_KAZOO($tagLib) {
	$obj = new CKudzuLibKAZOO();
	$obj->setTags($tagLib);
}
?>