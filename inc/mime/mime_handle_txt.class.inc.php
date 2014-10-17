<?php
/***************************************************************************
        mime_handle_txt.class.inc.php  -  html class form mime classes
           -------------------
    begin                : Mon Dec 06 2004
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

include_once(BASE."/inc/mime_handle.class.inc.php");

define( "MIME_HANDLE_TXT_FALLBACK", 'simpleHtfy');
define( "MIME_HANDLE_TXT_WIKI", 'wikify');

define( "MIME_HANDLE_TXT_TEXT_WIKI_FILE", "Text/Wiki.php");

class mime_handle_txt extends mime_handle {

    var $handler;
    var $raw;

    function mime_handle_txt($file="") {
        $this->mime_handle( $file );
    }

    /**
     * Tests if wiki syntaxt can be used
     */
    function canHandle() {
        if(@include_once(MIME_HANDLE_TXT_TEXT_WIKI_FILE)){
            if(!class_exists("Text_Wiki")) {
                log::trace("Text_Wiki class does not exists. Using fallback");
                $this->handler = MIME_HANDLE_TXT_FALLBACK;
            } else {
                $this->handler = MIME_HANDLE_TXT_WIKI;
            }
        } else {
            log::trace("Can't include ".MIME_HANDLE_TXT_TEXT_WIKI_FILE." file. Using fallback.");
            $this->handler = MIME_HANDLE_TXT_FALLBACK;
        }
        if( ( $this->raw = misc::readFile( $this->file )) === false ) {
            return sprintf("Could not read file %s",$this->file);
        } else {
            return true;
        }
    }

    /**
     * This is a fallback function, if for some reason, wiki can't be used
     */
    function simpleHtfy( $str="" ) {
        return simpleHtfy($str);
    }

    function wikify( $str="", $pages = "" )
    {
        global $uri;
        include_once(MIME_HANDLE_TXT_TEXT_WIKI_FILE);
        if ( empty( $str )) {
            $str = &$this->raw;
        }

        //$url = backslash($uri->sectionUrli());
        $url = $uri->sectionUrli(array("section"=>"", "page"=>""));

        $wiki =& new Text_Wiki();
        $wiki->setRenderConf('xhtml', 'wikilink', 'view_url', $url);
        $wiki->setRenderConf('xhtml', 'wikilink', 'new_url', '');
        $wiki->setRenderConf('xhtml', 'wikilink', 'new_text', '<!--NO WARRANTY-->');

        $wiki->setRenderConf('xhtml', 'freelink', 'view_url', $url);
        $wiki->setRenderConf('xhtml', 'freelink', 'new_url', $url);
        $wiki->setRenderConf('xhtml', 'freelink', 'new_text', '<!--NO WARRANTY-->');

        $wiki->setRenderConf('xhtml', 'url', 'target', '');
        $wiki->setRenderConf('xhtml', 'url', 'images', false);
        $wiki->setRenderConf('xhtml', 'image', 'base', 'liitteet/');
        // HUOM Pages systeemi t�tyy luoda (paremmaksi).
        if (!is_array( $pages )) {
            $pages = &misc::getPages();
        }
        $wiki->setRenderConf('xhtml', 'freelink', 'pages', $pages);
        $wiki->setRenderConf('xhtml', 'wikilink', 'pages', $pages);
        $xhtml = &$wiki->transform($str, 'Xhtml');
        return $xhtml;
    }

    function render() {
        if( is_callable( array(&$this, $this->handler))) {
            return call_user_method($this->handler, $this);
        } elseif(is_callable( $this->handler )) {
            return call_user_method($this->handler);
        } else {
            log::Trace("Serious problem. Handler is not callable {$this->handler}. Returning raw file.");
            return "<pre>".$this->raw."</pre>";
        }
    }
}

?>