<?php
/***************************************************************************
        functions.inc.php  -  Contains punch of basic functions
           -------------------
        begin                : Sat Mar 19 2005
        copyright            : (C) 2005 by Teemu A
        email                : teemu@terrasolid.fi
 ***************************************************************************/

/***************************************************************************
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

if(!defined("BASE")) die("Direct acces denied");

/**
 * Removes backslash if exists from $srt
 */
function stripBackSlash($str) {
    if(substr($str, -1) == DIRECTORY_SEPARATOR)
        return substr($str, strlen($str)-1);
}

/**
 * Add backslash if not exists
 */
function backSlash($str) {
    if( substr( $str, -1 ) != DIRECTORY_SEPARATOR ) {
        $str .= DIRECTORY_SEPARATOR;
    }
    return $str;
}

/**
 * Remove extension part from filename
 */
function removeExt($str) {
        $noext = preg_replace('/(.+)\..*$/', '$1', $str);
        return $noext;
}

/**
 * Converts hex to ascii.
 * original from djneoform at gmail dot com
 */
function hex2ascii($str) {
    $p = '';
    for ($i=0; $i < strlen($str); $i=$i+2) {
        $p .= chr(hexdec(substr($str, $i, 2)));
    }
    return $p;
}

function extension($module) {
    if(!extension_loaded($module)) {
        if(!defined("PHP_SHLIB_SUFFIX")) define("PHP_SHLIB_SUFFIX", "so");
        $prefix = (PHP_SHLIB_SUFFIX == 'dll') ? 'php_' : '';
        dl($prefix.$module.'.'.PHP_SHLIB_SUFFIX);
    }
    if(!extension_loaded($module)) {
        if(function_exists("_")) {
            log::trace(sprintf(_("Could not load extension %s."), $module));
        } else {
            log::trace(sprintf("Could not load extension %s.", $module));
        }
        return false;
    }
    return true;
}

function simpleHtfy( $str="" ) {
    if ( empty( $str )) {
        $str = $this->raw;
    }
    $str = eregi_replace( "([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])",
                            "<a href=\"\\1://\\2\\3\" target=\"_blank\">\\1://\\2\\3</a>", $str);
    $str = eregi_replace( "(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))",
                            "<a href=\"mailto:\\1%s\" >\\1</a>", $str);
    $str = nl2br( $str );
    return $str;
}

/**
 * gzip content to http friendly.
 */
function gzipencode($content) {
    $content = "\x1f\x8b\x08\x00\x00\x00\x00\x00".
    substr(gzcompress($content, 3), 0, - 4). // substr -4 isn't needed
    pack('V', crc32($content)).              // crc32 and
    pack('V', strlen($content));             // size are ignored by all the browsers i have tested

    return $content;
}

function encoding($str,&$from=null,&$to=null) {
	static $extension;

	if(!isset($extension)) {
		if(function_exists("mb_convert_encoding")) {
			$extension = "mbstring";
		} elseif( function_exists("iconv")) {
			$extension = "iconv";
		} else {
			$extension = null;
			log::trace(_("No mbstring, or iconv extension. Can't handle coding conversions"));
		}

	}

	if($from===null) {
		if($extension == "mbstring") {
			$from = mb_detect_encoding($str);
		} else {
			$from = config::getSetting("Site", "encoding", "UTF-8");
		}
	}

	if($to===null) {
		$to = config::getSetting("Site", "encoding", "UTF-8");
	}

	// If input and output encodings are same, dont bother with it.
	if($from === $to && $from !== null) return $str;

	log::trace(sprintf(_("Converting encoding from %s to %s"),$from, $to));

	switch($extension) {
		case "mbstring" :
			return mb_convert_encoding($str,$to,$from);
		case "iconv" :
			return iconv($from, $to, $str);
		default :
			log::trace(_("No character set conversion extension"));
			return $str;
	}
}

function timer() {
	static $start;
	list($usec, $sec) = explode(" ", microtime());
	$now = ((float)$usec + (float)$sec);
	if(!isset( $start )) {
		$start = $now;
	}
	$timed = $now - $start;
	return $timed;
}