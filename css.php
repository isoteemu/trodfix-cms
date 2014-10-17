<?php
define( "BASE", dirname(__FILE__));

// kirjastot.
// Bootstrap
require_once( BASE."/inc/bootstrap.inc.php");
include_once( BASE."/inc/css.class.inc.php" );

// Smarty
include_once( BASE."/inc/smarty.class.inc.php");

// PEAR
$oldinc = ini_get("include_path");
ini_set("include_path", $oldinc.":".BASE."/inc/pear" );

session_name( config::getValue("Site", "identifier"));
session_start();

Header("Content-Type: text/css; charset=".config::getValue("Site", "encoding"));


$cssVals = css::get();

// Smarty
$smarty = new Smarty_init(md5(serialize($cssVals)));

$cssVals['style']['images'] = $smarty->template_dir.DIRECTORY_SEPARATOR."images";

$smarty->left_delimiter = '/*{';
$smarty->right_delimiter = '}*/';

$smarty->assign($cssVals);

$page = $smarty->fetch("css.tpl");
if(config::getSetting('System','gzip', false)) {
    if(!headers_sent() && strstr($_SERVER['HTTP_ACCEPT_ENCODING'], "gzip")) {
        log::trace(_("Using gzip"));
        header('Content-Encoding: gzip');
        $page = gzipencode($page);
    } else {
        log::trace(_("Gzip set to be used, but can't use"));
    }
}

die($page);

?>