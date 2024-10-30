<?php
/*
Plugin Name: Kazoo Templated Content
Plugin URI: http://www.AndrewFriedl.com/wordpress-plugins/kazoo-templated-content/
Description: Create templated content within posts and pages with this extensible plugin.
Version: 1.2.0
Author: Andrew Friedl
Author URI: http://www.AndrewFriedl.com/
License: GPLv2
*/
$kazoo_path     = preg_replace('/^.*wp-content[\\\\\/]plugins[\\\\\/]/', '', __FILE__);
$kazoo_path     = str_replace('\\','/',$kazoo_path);
$kazoo_dir      = substr($kazoo_path,0,strrpos($kazoo_path,'/'));
$kazoo_siteurl  = get_bloginfo('wpurl');
$kazoo_siteurl  = (strpos($kazoo_siteurl,'http://') === false) ? get_bloginfo('siteurl') : $kazoo_siteurl;
$kazoo_fullpath = $kazoo_siteurl.'/wp-content/plugins/'.$kazoo_dir.'/';
$kazoo_relpath  = str_replace('\\','/',dirname(__FILE__));
$kazoo_abspath  = str_replace("\\","/",ABSPATH); 
$kazoo_libpath  = $kazoo_relpath.'/lib/';
$kazoo_adminpage= 'kazoo-templated-content';
$kazoo_adminurl = 'options-general.php?page='.$kazoo_adminpage;
$kazoo_adminmsg = null;
define('KAZOO_VERSION', '1.2.0');
define('KAZOO_PATH', $kazoo_path);
define('KAZOO_DIR', $kazoo_dir);
define('KAZOO_SITEURL', $kazoo_siteurl);
define('KAZOO_FULLPATH', $kazoo_fullpath);
define('KAZOO_RELPATH', $kazoo_relpath);
define('KAZOO_LIBPATH', $kazoo_libpath);
define('KAZOO_ABSPATH', $kazoo_abspath);
define('KAZOO_ADMINPAGE',$kazoo_adminpage);
define('KAZOO_ADMINURL', $kazoo_adminurl);
define('KAZOO_NAME', 'Kazoo Templated Content');

if ( !defined('KUDZUPHP') ) {
	require_once($kazoo_relpath.'/include/KudzuPHP.php');
}
function Kazoo_CreateEngine() {
	global $wp_version;
	global $current_user;
	global $user_ID;

	// create the template engine
	$eng = new CKudzuEngine();		
	
	// basics
	$eng->setWriter(new CKudzuBufferWriter());
	
	// install kazoo environment values
	$eng->putValue('kazoo_path',KAZOO_PATH);
	$eng->putValue('kazoo_dir',KAZOO_DIR);
	$eng->putValue('kazoo_siteurl',KAZOO_SITEURL);
	$eng->putValue('kazoo_fullpath',KAZOO_FULLPATH);
	$eng->putValue('kazoo_relpath',KAZOO_RELPATH);
	$eng->putValue('kazoo_libpath',KAZOO_LIBPATH);
	$eng->putValue('kazoo_abspath',KAZOO_ABSPATH);
	$eng->putValue('kazoo_adminurl',KAZOO_ADMINURL);
	$eng->putValue('kazoo_adminpage',KAZOO_ADMINPAGE);
	$eng->putValue('kazoo_name',KAZOO_NAME);
	$eng->putValue('kazoo_version',KAZOO_VERSION);		
	
	// install Wordpress related values
	$eng->putValue('wp_version', $wp_version);
	$eng->putValue('is_home', is_home());
	$eng->putValue('is_front_page', is_front_page());
	$eng->putValue('is_single', is_single());
	$eng->putValue('is_sticky', is_sticky());
	$eng->putValue('is_page', is_page());
	$eng->putValue('is_category', is_category());
	$eng->putValue('is_admin', is_admin());
	
	// setup current page information
	
	// install current user info
	get_current_user();
	$eng->putValue('user_ip',$_SERVER['REMOTE_ADDR']);
	$eng->putValue('user_id',$user_ID);
	$eng->putValue('is_user',($user_ID>0));		

	// import Wordpress related tag handler library
	$eng->setLibPath(KAZOO_LIBPATH);
	$eng->libImport('kazoo');

	return $eng;
}

function Kazoo_SetAdminState($eng){
	$eng->putValue('TplAdminPage',KAZOO_ADMINPAGE);
	$eng->putValue('TplAdminURL',KAZOO_ADMINURL);
	$eng->putValue('TplMsg',$kazoo_adminmsg);
	$eng->putValue('TplHasMsg',!is_null($kazoo_adminmsg));
}

function Kazoo_Activated() {
	global $wpdb;

	$wp_path_to_this_file = preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
	$this_plugin = plugin_basename(trim($wp_path_to_this_file));
	$active_plugins = get_option('active_plugins');
	$this_plugin_key = array_search($this_plugin, $active_plugins);
	
	if ($this_plugin_key) {
		array_splice($active_plugins, $this_plugin_key, 1);
		array_unshift($active_plugins, $this_plugin);
		update_option('active_plugins', $active_plugins);
	}
	
	$s = "CREATE TABLE IF NOT EXISTS `kazoo_tpl` (".
		 "`id` int(11) NOT NULL AUTO_INCREMENT,".
		 "`name` varchar(64) NOT NULL,".
		 "`code` text NOT NULL,".
		 "`date` datetime NOT NULL,".
		 "PRIMARY KEY (`id`)".
		 ") ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

	$r = $wpdb->query($s);
}

function Kazoo_ShortCode($attr, $content=null) {
	global $wpdb;
	
	$t = $content;
	if ( is_null($t) )
		$t = '';

	extract(shortcode_atts(array('isdebug' => 'false', 'src' => '0', 'params' => ''), $attr));
	if ( is_numeric($src) ) {
		if ( intVal($src) > 0 ) {
			$q = "SELECT `code` FROM kazoo_tpl WHERE `id` = $src";
			$o = $wpdb->get_row($q,OBJECT);
			if (is_null($o)) {
				return "Invalid Kazoo Template, src=$src";
			}
			$t = stripslashes($o->code);
		}
	} else {
		return "Invalid Kazoo Template, src=$src";
	}
	
	$eng = Kazoo_CreateEngine();
	$eng->setDebug(strcasecmp($isdebug,'true')==0);

	if ( $params !== '' ) {
		$parr = split(',', $params);
		foreach ($parr as $pitm) {
			$itm = split('=',$pitm);
			$eng->setValue(trim($itm[0]),trim($itm[1]));
		}
	}	
	
	$eng->executeString($t);	
	return $eng->getWriter()->getContent();
}

function Kazoo_AdminMenu() {
	if (function_exists('add_menu_page')) {
		add_options_page('Kazoo Templates', 'Kazoo Templates', 'manage_options', 'kazoo-templated-content', 'Kazoo_ShowTemplates');
	}
}

function Kazoo_ShowTemplates() {
	global $wpdb;	

	if (!current_user_can('manage_options'))
		wp_die( __('You do not have sufficient permissions to access this page.') );

	$mode='list';
	$id=$_GET['id']-0;
	if (isset($_GET['mode']))
		$mode = $_GET['mode'];
	  
	if ($mode==='list') {
		Kazoo_TemplateLIST();
		return;
	}

	$fn = 'Kazoo_Template'.strtoupper($mode);
	$fn($id);	
}

function Kazoo_TemplateLIST($msg=null) {
	global $wpdb;	
	$e = Kazoo_CreateEngine();
	Kazoo_SetAdminState($e);
	$q = 'SELECT `id`, `name`, `date` FROM kazoo_tpl ORDER BY id';
	$r = $wpdb->get_results($q,ARRAY_A);
	$e->putValue('TplROWS',$r);
	$e->setHandlerFn('TemplateRows','KazooFn_TEMPLATEROWS');
	$e->executeFile(KAZOO_RELPATH.'/tpl/grid.html');
	echo $e->getWriter()->getContent();
}

function KazooFn_TEMPLATEROWS($n) {
	$e = $n->getEngine();
	$r = $e->getValue('TplROWS');
	if (!is_null($r)) {
		if (count($r)>0) {
			for ($i=0;$i<count($r);$i++) {
				$t = $r[$i];
				$e->putValue('TplID',$t['id']);
				$e->putValue('TplName',$t['name']);
				$e->putValue('TplDate',$t['date']);
				$e->putValue('TplShort',"[kazoo src='".$t['id']."' /]");
				$n->stackPush();
				$n->evalChildNode('Item');
				$n->stackPop();
			}
		}
		return;
	} 
	$n->stackPush();
	$n->evalChildNode('Else');
	$n->stackPop();
}

function Kazoo_TemplateEDIT($id) {
	global $wpdb;
	
	$r = null;

	if ($id > 0) {
		$q = "SELECT id,name,date,code FROM kazoo_tpl WHERE id=$id";
		$r = $wpdb->get_row($q,OBJECT);
	}
	
	$e = Kazoo_CreateEngine();
	Kazoo_SetAdminState($e);	

	if (is_null($r)) {
		$e->putValue('TplID',0);
		$e->putValue('TplName','');
		$e->putValue('TplCode','');
		$e->putValue('TplDate','');
		$e->putValue('TplShort','');
		$e->putValue('TplMode','create');
		$e->putValue('TplEditText','Create');
		if ( $id > 0 ) {
			$e->putValue('TplHasMsg',true);
			$e->putValue('TplMsg',"ERROR: Template (id=$id) was not found.");
		}
	} else {
		$e->putValue('TplID',$id);
		$e->putValue('TplName',stripslashes($r->name));
		$e->putValue('TplCode',stripslashes($r->code));
		$e->putValue('TplDate',$tplDate);
		$e->putValue('TplShort',"[Kazoo src='$id' /]");
		$e->putValue('TplMode','update');
		$e->putValue('TplEditText','Update');
		$e->putValue('TplHasMsg',false);
		$e->putValue('TplMsg','');
	}
	
	$e->executeFile(KAZOO_RELPATH.'/tpl/edit.html');
	echo $e->getWriter()->getContent();
}

function Kazoo_TemplateUPDATE($id) {
	global $wpdb;	

	$tplName = addslashes($_POST['TplName']);
	$tplCode = addslashes($_POST['TplCode']);
	$tplEditText= 'Update';
	$mode = 'update';
	
	if ($id==0) {
		$s = "INSERT INTO kazoo_tpl SET `name`='$tplName',`code`='$tplCode',`date`=NOW()";
		$wpdb->query($s);
		Kazoo_TemplateLIST();
	} else {
		$s = "UPDATE kazoo_tpl SET `name`='$tplName',`code`='$tplCode',`date`=NOW() WHERE id=$id";
		$wpdb->query($s);
		
		Kazoo_TemplateEDIT($id);
	}
}
function Kazoo_TemplateCREATE($msg=null) {
	global $wpdb;	

	$tplName = addslashes($_POST['name']);
	$tplCode = addslashes($_POST['code']);
	$tplMsg = '';
	
	$wpdb->query();
	Kazoo_TemplateLIST();
}
function Kazoo_TemplateDELETE($id) {
	global $wpdb;
	$s = "DELETE FROM kazoo_tpl WHERE id = $id";
	$wpdb->query($s);
	Kazoo_TemplateLIST();
}

// add templated event hooks into wordpress
//add_action('activated_plugin','Kazoo_Activated');
add_action('admin_menu','Kazoo_AdminMenu');
add_shortcode('kazoo','Kazoo_ShortCode');
register_activation_hook( __FILE__,'Kazoo_Activated');
?>