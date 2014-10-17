<?php
/***************************************************************************
        search.class.inc.php  -  Ht://dig search subsys
           -------------------
    begin                : 03 Aug 09 2004
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
// $Id: search.class.inc.php,v 1.1.2.2 2004/08/06 14:44:22 teemu Exp $


class htdig {

    var $url;
    var $results;

    var $phpResults = array('firstdisplayed' => 0,
                            'lastdisplayed'  => 0,
                            'matches'        => 0,
                            'results'        => array());

    var $words;

    var $qurlparams = array('config'  => 'htdig',
                            'exclude' => '',
                            'method'  => 'and',
                            'format'  => 'long',
                            'sort'    => 'score');

    function &htdig($url, $config = "htdig")
    {
        $this->url = $url;
        $this->qurlparams['config'] = $config;
    }

    function &search( $word )
    {
        if (empty( $word )) {
            log::trace("Hakulause tyhj‰.");
            return FALSE;
        }
        $this->words = $word;
        if (!$this->results = $this->_getResults()) {
            log::trace("Virhe haettaseessa tulosta");
            return FALSE;
        }
        $this->formatResult();
        return $this->phpResults;
    }

    function &formatResult()
    {
        if(! strstr( $this->results, "<htdig>" )) {
            log::trace("Ei <htdig> merikint‰‰ tuloksessa.");
            return FALSE;
        }

       
        $results = explode( "<result>", $this->results );
        $results = array_splice($results, 1 );
        $this->phpResults['firstdisplayed'] = $this->grep("firstdisplayed", $this->results);
        $this->phpResults['lastdisplayed'] = $this->grep("lastdisplayed", $this->results);
        $this->phpResults['matches'] = $this->grep("matches", $this->results);
        foreach( $results as $result ) {
            $fancySize = $this->_fancySize( $this->grep("size", $result) );
            $this->phpResults['results'][] = array(
                'url' => $this->grep("url", $result),
                'size' => $fancySize,
                'title' => $this->grep("title", $result),
                'excerpt' => $this->grep("excerpt", $result),
                'modified' => $this->grep("modified", $result));
        }
    }

    function grep($what, $from, $verbose = true)
    {
        $pregReg = "/<".$what.">.*?<\/".$what.">/si";
        if ( preg_match($pregReg, $from, $returns )) {
            $bodyRegex = Array(
                "'<".$what.">'siu",
                "'</".$what.">'siu");
            $fixed = preg_replace($bodyRegex, "", $returns[0]);
            return $fixed;
        } elseif ($verbose == true) {
            log::trace("Ei pilkottavaa osiota $what");
            return FALSE;
        }
        return FALSE;
    }

    function _fancySize( $size )
    {
        $type = Array ('b', 'kb', 'Mb', 'gb');
        for ($i = 0; $size > 1024; $i++)
            $size /= 1024;
        return round ($size, 2)." $type[$i]";
    }
    
    function &_qurl()
    {
        $url = $this->url."?";
        foreach( $this->qurlparams as $key => $val ) {
            $url .= $key."=".$val."&";
        }
        $url .= "words=".urlencode( $this->words );
        log::Trace("$url");
        return $url;
    }

    function &_getResults()
    {
        ini_set("allow_url_fopen", true );
        ini_set("user_agent", "PHP (trodfix::cms)");
        ini_set("default_socket_timeout", "10");
        if(connection_aborted()) die("Connection Aborted");
        if( function_exists( "file_get_contents" )) {
            $result = file_get_contents($this->_qurl());
        } else {
            $result = implode('', file($this->_qurl()));
        }
        if(connection_aborted()) die("Connection Aborted");
        return $result;
    }
}

?>