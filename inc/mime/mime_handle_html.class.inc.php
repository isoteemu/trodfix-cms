<?php
/***************************************************************************
        mime_handle_html.class.inc.php  -  html class form mime classes
           -------------------
    begin                : Sat Dec 04 2004
    copyright            : (C) 2004 by Teemu A
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

include_once(BASE."/inc/mime_handle.class.inc.php");

/**
 * Class for html mimetypes
 */
class mime_handle_html extends mime_handle {

    var $html;
    var $meta = false;
    var $body;

	var $tidyconf = array(
		'indent' 			=> true,
		'output-xhtml' 		=> true,
		'show-body-only'	=> true,
		'force-output'		=> true,
		'tidy-mark'			=> true,
		'wrap'				=> 0,
		'wrap-attributes'	=> false,
		'literal-attributes'=> true,
		'numeric-entities'	=> true,
		'quiet'				=> true,
		'quote-nbsp'		=> true,
		'fix-backslash'		=> false,
		'fix-uri'			=> false,
	);

    function mime_handle_html($file="") {
        $this->mime_handle( $file );
        $this->parse();
    }

    function canHandle() {
        return true;
    }

    function parse() {
        $this->html = misc::readFile( $this->file );
        $this->body = $this->htSafer($this->html);
        $this->meta = get_meta_tags( $this->file, true );
    }

    /**
     * Check if contains html start and end tags.
     * @param $str string Tutkittava lause
     * @return bool
     */
    function htCheck( $str ) {
        $regex = "'<html[^>]*?>.*?</html>'si";
        if ( preg_match( $regex, $str )) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get only body from html page
     */
    function htSafer( $str=false ) {
        if( $str === false ) {
            $str =& $this->html;
        }

		$str = $this->_encode($str);

        // Run it thru tidy, if found.
        if( ($tversion = phpversion("tidy")) >= 2 ) {
            log::trace("Tidy v".$tversion." extension found. Using it");

            // Merge user tidy params
			$userconf = config::getValue("Tidy");
			if(is_array($userconf)) $this->tidyconf = array_merge($this->tidyconf,$userconf);

            $tidy = tidy_parse_string($str, $this->tidyconf,$this->_tidyEncoding(config::getSetting("Site", "encoding", "UTF-8")));
            if(is_object($tidy)) {
            	$tidy->cleanRepair();
            	log::trace($tidy->errorBuffer);
            	$str = $tidy->body();
           	} else {
           		log::trace($tidy);
           	}
        }
		$regex = "'<body[^>]*?>.*?</body>'si";
		if ( preg_match( $regex, $str, $bodyContent )) {
			$bodyRegex = Array(
			"'<body[^>]*?>'si",
			"'</body>'si");
			$fixed = preg_replace($bodyRegex, "", $bodyContent[0]);
			$cleaned = trim( $fixed );
			return $cleaned;
		}
        log::trace("No body tags found.");
        return $str;
    }

    function render() {
    	if($this->meta !== false) {
        	$this->pushMeta($this->meta);
    	}

        return $this->body;
    }

	function _encode($str) {
		$encode = array();
		if(preg_match('%<[\s]*meta[\s]*http-equiv="?Content-Type"?[\s]*content="?[^;"]*;[\s]*charset=([^"]*)"?[^>]*>%si',$str,$encode)) {
			$encoding = $encode[1];
		} else {
			$encoding = null;
		}
		return encoding($str,$encoding);
	}

	/**
	 * TODO: This needs to be extended
	 */
	function _tidyEncoding($encode) {
		$encode = strtoupper($encode);
		switch ($encode) {
			case "ISO-8859-15" :
			case "ISO-8859-1" :
				return "latin1";
			case "UTF-8" :
			default :
				return "UTF8";
		}
	}

}

?>