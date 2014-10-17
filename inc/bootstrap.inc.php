<?php
/***************************************************************************
     bootstrap.inc.php  -  Runs common bootstrap things.
     -------------------
     begin                : Sat Sep 03 2005
     copyright            : (C) 2005 by Teemu A
     email                : teemu@terrasolid.fi
 ***************************************************************************/

if(!defined("BASE")) die("Direct acces denied");

// simple functions
require_once( BASE."/inc/functions.inc.php");

// Init timer
timer();

//
// Check variables, and remove slashes from strings
//
$_variables = array("_POST", "_GET", "_COOKIE", "_REQUEST");
$_protected = array("_protected", "_variables", "_SESSION", "_POST", "_GET", "_COOKIE", "_REQUEST", "GLOBALS");
if (get_magic_quotes_gpc()) $_stripslashes = true;

foreach($_variables as $toclean) {
	if (is_array($$toclean)) {
		foreach ($$toclean as $key => $value) {
			if($_stripslashes && !is_array($$toclean[$key]))
				$$toclean[$key] = stripslashes($value);
			// Remove if set as global.
			if(in_array($key, $_protected)) continue;
			if(isset($$key)) unset($$key);
		}
	}
}

// Common libraries
include_once( BASE."/inc/log.class.inc.php" );
include_once( BASE."/inc/auth.class.inc.php" );
include_once( BASE."/inc/misc.class.inc.php" );
include_once( BASE."/inc/uri.class.inc.php" );
include_once( BASE."/inc/config.class.inc.php" );


//
// Session
//
// disable use of trasparent session
if( ini_get("session.use_trans_sid") == 1 ) ini_set("session.use_trans_sid", 0 );
// Start session
session_name( config::getSetting('Site','identifier',"alkemisti"));
session_start();


// PATH_SEPARATOR is defind >4.3.0, and we want support older installations
if(!defined("PATH_SEPARATOR")) define("PATH_SEPARATOR", ":");

if(!defined("DIREC_SEPARATOR")) define("DIREC_SEPARATOR", "/");

// Define our own pear library path to include search path.
$oldinc = ini_get("include_path");
ini_set("include_path", BASE."/inc/pear".PATH_SEPARATOR.$oldinc );


//
// Locale
//
// Locale
// Couple of functions that some php installations don't allow
if( function_exists("putenv")) putenv('LANG="'.escapeshellarg(config::getValue("Site", "locale")).'"');
extension("iconv");
if( function_exists("iconv_set_encoding") ) iconv_set_encoding("output_encoding", config::getSetting("Site", "encoding", "UTF-8"));

extension("mbstring");
if(function_exists("mb_internal_encoding") ) mb_internal_encoding(config::getSetting("Site", "encoding", "UTF-8"));

ini_set("default_charset", config::getValue("Site", "encoding"));
ini_set("mbstring.encoding_translation", "on");
Header("Content-Type: text/html; charset=".config::getValue("Site", "encoding"));
setlocale(LC_ALL, config::getValue("Site", "locale"));

// Gettext
extension("gettext");
if(function_exists("_")) {
    bindtextdomain("alkemisti", BASE."/inc/locale");
    textdomain("alkemisti");
} else {
    function _($str) {
        return $str;
    }
    log::trace(_("No gettext extension in PHP. Localization not possible"));
}

// Load config
// Backward compatibility
if(config::getValue("System", "backwardCompatibility")) $GLOBALS['settings']['root'] = config::dumpAll();

// Init url handler
$uri = new uri(config::getValue('System','uristyle'));

// check if user accept session cookies.
// If not, put session identifier in url
$myUrl = "http://".$_SERVER['SERVER_NAME'].dirname( $_SERVER["SCRIPT_NAME"] );
if( substr($_SERVER['HTTP_REFERER'], 0, strlen( $myUrl )) == $myUrl ) {
    if(! isset( $_SESSION['sessionWorks'] ) || $_SESSION['sessionWorks'] != true ) {
        log::trace(_("forcing session storage to urls"));
        $uri->forceSid();
    }
} else {
    log::trace(sprintf(_("Referer was not from myself (exp:%s|ref:%s)"), $myUrl, $_SERVER['HTTP_REFERER']));
    $_SESSION['sessionWorks'] = true;
}

// push memory limit up. eg PHP4 needs sometimes a _much_ more memory to GD functions than 8MB
ini_set("memory_limit",config::getSetting("System", "memory_limit", "16M"));
