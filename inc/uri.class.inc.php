<?php
/***************************************************************************
        uri.class.inc.php  -  URI
           -------------------
    begin                : Wed Jun 30 2004
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

define("SECTION_OFFSET", 0 );
define("PAGE_OFFSET", 1 );

define("FRONTPAGE", "Etusivu");

class uri {

    var $base;
//    var $section;
//    var $page;
    var $xvals = array();
    var $uristyle;

    var $pathParts = array();

    var $sectionOffset = SECTION_OFFSET;
    var $pageOffset = PAGE_OFFSET;

    /**
     * Constructor
     */
    function uri($style = "")
    {
        $this->setUriStyle( $style );
        $this->base = basename( $_SERVER['SCRIPT_NAME'] );

        $this->pathParts = $this->_getPathParts();

    }

    /**
     * Asettaa URIn tyyliksi, get, rewrite tai multiviews
     */
    function setUriStyle( $style ) {
        $style = strtolower( $style );

        switch( $style ) {
            case 'multiviews' :
                $this->uristyle = "multiviews";
                break;
            case 'rewrite' :
                $this->uristyle = "rewrite";
                break;
            case 'get' :
            case '' :
                $this->uristyle = "get";
                break;
            default :
                log::Trace("Tuntematon uri hander style: $style");
                $this->uristyle = "get";
                break;
        }
    }

    /**
     * Asettaa/palautaa section
     */
    function section( $section = "" )
    {
        if( $section != "" ) {
            $this->urlParts[$this->sectionOffset] = $section;
        }
        return urldecode($this->pathParts[$this->sectionOffset]);
    }

    function page( $page = "" )
    {
        if( $page != "" ) {
            $this->urlParts[$this->pageOffset] = $page;
        }
        return urldecode($this->pathParts[$this->pageOffset]);
    }

    function base( $base = "" )
    {
        if( $base != "" ) {
            $this->base = $base;
        }
        return $this->base;
    }

    function inject( $key, $value )
    {
        $this->xvals[$key] = $value;
    }

    function forceSid() {
        log::trace(sprintf(_("Forcing session params to url (name:%s|val:%s)"),session_name(),session_id()));
        $this->inject( session_name(), session_id() );
    }

    function sectionUrli( $params = Array() )
    {
        $params['offset'] = $this->sectionOffset;
        $uri = $this->urli( $params );
        return $uri;
    }

    function urli( $params = Array() )
    {
        $vals = $this->_getParams();
        $vals = array_merge( $vals, $params );

        switch( $this->uristyle ) {
            case "rewrite" :
                $urli = $this->rewriteStyleUri( $vals );
                break;
            case "multiviews" :
                $urli = $this->multiviewsStyleUri( $vals );
                break;
            default :
                log::trace(sprintf(_("Unknown uri style %"), $this->uristyle));
            case "get" :
                $urli =  $this->getStyleUri( $vals );
                break;
        }
        return $urli;
    }

    function rewriteStyleUri($params = NULL) {
        if ( $params == NULL ) {
            $params = $this->_getParams();
        }
        $uri = $this->_getPath($params);

        if ( count ( $params ) > 0 ) {
            foreach( $params as $key => $val ) {
                if( $key == "offset" ) continue;
                if( $key == "base" ) continue;
                if( $key == "section" ) continue;
                if( $key == "page" ) continue;
                if (! empty( $val )) {
                    $uri .= "&amp;".urlencode($key)."=".urlencode($val);
                }
            }
        }
        // Tekee urlista rumemmat mutten keksi oikein muutakaan
        $uri .= "&amp;";
        return $uri;
    }

    function multiviewsStyleUri($params = NULL)
    {
        if ( $params == NULL ) {
            $params = $this->_getParams();
        }
        $uri = "";
        if ( isset( $params['base'] )) {
            $uri = backslash($params['base']);
        }
        $uri .= $this->_getPath($params);

        $uri .= "?";
        if ( count ( $params ) > 1 ) {
            foreach( $params as $key => $val ) {
                if( $key == "offset" ) continue;
                if( $key == "base" ) continue;
                if( $key == "section" ) continue;
                if( $key == "page" ) continue;
                if (! empty( $val )) {
                    $uri .= urlencode($key)."=".urlencode($val)."&amp;";
                }
            }
        }
        return $uri;
    }

    function getStyleUri( $params = NULL )
    {
        if ( $params == NULL ) {
            $params = $this->_getParams();
        }
        $uri = "";
        if($this->base) {
            $uri .= $this->base;
        }
        $uri .= "?path=";
        $uri .= $this->_getPath($params);

        if ( count ( $params ) > 1 ) {
            foreach( $params as $key => $val ) {
                if( $key == "offset" ) continue;
                if( $key == "base" ) continue;
                if( $key == "section" ) continue;
                if( $key == "page" ) continue;
                if (! empty( $val )) {
                    $uri .= "&amp;".urlencode($key)."=".urlencode($val);
                }
            }
        }
        $uri .= "&amp;";
        return $uri;
    }

    /**
     * Returs path url.
     * @param $params parameters
     */
    function _getPath($params=array()) {

        if(empty($params['offset'])) {
            if( isset($params['page'])) {
                $params['offset'] = $this->pageOffset;
            } elseif(isset($params['section'])) {
                $params['offset'] = sectionOffset;
            } else {
                $params['offset'] = count($this->pathParts);
            }
        }
        $path = "";
        $i = 0;
        while($i <= $params['offset']) {
            if($path != "") $path = backslash($path);
            if($i == $this->sectionOffset && isset($params['section'])) {
                $path .= urlencode($params['section']);
            } elseif ( $i == $this->pageOffset && isset($params['page'])) {
                $path .= urlencode($params['page']);
            } else {
                $path .= urlencode($this->pathParts[$i]);
            }
            $i++;
        }
        return backslash($path);
    }

    function getPathPart($nr) {
        return $this->pathParts[$nr];
    }

    /**
     * Moves url offset values +1
     */
    function pushOffset() {
        log::trace("Path offset pushed");
        $this->sectionOffset++;
        $this->pageOffset++;
    }

    /**
     * Tries to fetch section witch has been given in url
     */
    function wantedSection() {
        $urlParts = $this->_getPathUrl();
        return $urlParts[$this->sectionOffset];
    }

    /**
     * Translates local filename to url
     * @todo use file location masking
     * @todo Make this better.
     */
    function translatePath($file) {
        //  //      //      //  //
        //  //     ///       ///
        //////    // //      ///
        //  //   ///////    // //
        //  //  //     //  //   //
        return substr($file, strlen(dirname($_SERVER['SCRIPT_FILENAME']))+1);
    }

    /**
     * Arvaa haettavan urlin osan.
     */
    function _tryToGetUrlPart( $offset ) {

        $_tmp['path'] = explode( "/", $this->_getPathUrl());
        if (! empty( $_tmp['path'][$offset] )) {
            return  $_tmp['path'][$offset];
        }
    }

    function _getPathUrl() {
        if( $this->uristyle == "multiviews" ) {
            if(!isset($_SERVER['PATH_INFO'])) {
                log::trace("Ei \$_SERVER['PATH_INFO'] muuttujaa");
                return false;
            } elseif ( empty( $_SERVER['PATH_INFO'])) {
                return;
            }
            // PATH_INFO heitt� / alkuun.
            $path = substr($_SERVER['PATH_INFO'], 1, strlen($_SERVER['PATH_INFO'])-1);
        } else {
            if(empty( $_GET['path'] )) {
                return;
            }
            if(substr($_GET['path'],0,1) == "/" ) {

                $path = substr($_GET['path'],1, strlen($_GET['path'])-1);
            } else {
                $path = $_GET['path'];
            }
        }
        return $path;
    }

    function _getPathParts() {
        $pathParts = explode( "/", $this->_getPathUrl() );
        // Check that section exists, due section allways required
        if(empty( $pathParts[$this->sectionOffset] )) {
            if(is_callable(array("config", "getValue"))) {
                if($defSection = config::getValue("Site", "frontpage")) {
                    $pathParts[$this->sectionOffset] = $defSection;
                } elseif( $defSection = config::getValue("Site", "etusivu") ) {
                    // backward compatible
                    $pathParts[$this->sectionOffset] = $defSection;
                } else {
                    log::trace("Could not get default frontpage, using hard-coded");
                    $pathParts[$this->sectionOffset] = FRONTPAGE;
                }
            } else {
                log::trace("config class did not exists. using hard-coded default section");
                $pathParts[$this->sectionOffset] = FRONTPAGE;
            }
        }
        return $pathParts;
    }

    function _getParams()
    {
        $vals = array(
            'base' => $this->base
        );
        $vals = array_merge($this->xvals, $vals);
        return $vals;
    }
}
