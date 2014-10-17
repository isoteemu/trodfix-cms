<?php
/***************************************************************************
        mime_handle.class.inc.php  -  Base class form mime classes
           -------------------
    begin                : Fri Nov 05 2004
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

class mime_handle {

    var $path;
    var $file;

    var $features = array(
        "pseudos"  => array(),
        "onlyList" => array(),
        "blackList" => array(),
        "whitelist" => array()
    );

    function features($what=null) {
        if($what === null) return $this->features;
        if(isset($this->features[$what])) return $what;
        log::trace(sprintf(_("Feature '%s' was requested but I don't have any clue what that would be"), $what));
        return array();
    }

    function mime_handle($file) {
        if(! file_exists( $file )) {
            log::trace("File {$file} does not exists");
            return false;
        }
        if(! is_readable( $file )) {
            log::Trace("File {$file} is not readable");
            return false;
        }
        $this->file = $file;
    }

    /**
     * Can file be handled
     * @return mixed true on success, error message on fail
     */
    function canHandle() {
        log::trace("Mime class ".get_class( $this )." does not support canHandle()");
        return true;
    }

    function setPath($path) {
        $this->path = backslash($path);
        return true;
    }

    function render() {
        log::Trace("Class ".get_class( $this )." does not support render()");
        return false;
    }

	/**
	 * render page after all items has been processed
     */
	function post() {
        log::Trace(sprintf(_("Class %s does not support post()"),get_class( $this )));
        return false;
	}
    
    /**
     * @todo Make this internal -> update mime handlers
     */
    function pushMeta($meta) {
        global $params;
        if(!is_array($meta)) {
            log::Trace("Not an array");
            return false;
        }
        $params['header'] = array_merge($params['header'], $meta);
    }

    function _registerFeature($type, $value, $force=false) {
        if(!isset($this->features[$type])) {
            log::trace(sprintf(_("Feature '%s' is not known"), $type));
            // Lets create then that category...
            $this->features[$type] = array();
        }
        if(in_array($value, $this->features[$type], true)) {
            log::trace(sprintf(_("Feature '%s' is already registered in '%s'"), $value, $type));
            if($force == false) return false;
        }
        $this->features[$type][] = $value;
    }
}
?>