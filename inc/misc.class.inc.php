<?php
/***************************************************************************
        misc.class.inc.php  -  Shyweb specified class[es]
           -------------------
    begin                : Sat Apr 24 2004
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

class bobTheBuilder {

    var $dir;

    var $pseudos = Array(
        "@SPONSORIKUVAT",    // Sponssaajien bannerit.
        "@KUVAT",
        "@INFO",             // Sivupalkin sis�t�        "@UUTISET",
        "@UUTISOTSIKKO",
        "@ORDER",
        "@AUTH",
        "@LOGO.png",
        "@LOGO.jpg",
        "@HEAD",
        "@FOOT",
        "@THUMBS",
        "@EXIF"
    );

    var $onlyList = Array();

    /**
     * Pahojen tiedostojen musta lista.
     * Regular expression syntaxilla.
     */
    var $blackList = Array (
        '^CVS$',
        '^.svn$',
        '~$'
    );

    var $_tags;

    var $_handle;
    var $_cache;

    var $org;

    var $_mimeobjects = array();

	var $lastmodified = 0;

    function workDir( $dir = "" )
    {
        if ( $dir != "" ) {
            if (is_dir( $dir )) {
                $this->dir = $dir;
            } else {
                log::trace("workDir(): Ei ole hakemisto: $dir");
            }
        }
        return $this->dir;
    }

    function dirs($filter = true)
    {
        if (! $this->_Auth() ) {
            return FALSE;
        }
        $this->_checkDirContent();
        $this->orderByPseudo("dirs");
        if ( $filter != TRUE ) {
            return $this->_cache['dirs'];
        }
        return $this->filter("dirs");
    }

    function ls($filter = true)
    {
        if (! $this->_Auth() ) {
            return FALSE;
        }
        $this->_checkDirContent();
        $this->orderByPseudo("files");
        if ( $filter != TRUE ) {
            return $this->_cache['files'];
        }
        return $this->filter("files");
    }

    function count( $what = 'files' )
    {
        $this->_checkDirContent();
        if (! is_array( $this->_cache[$what] )) {
            return 0;
        }
        return count( $this->_cache[$what] );
    }

    function pickRand( $what = 'files' )
    {
        if ( $this->count( $what ) == 0 ) {
            log::trace("pickRand(): Haluttu $what, mutta count v�tt� ettei sis�l�mit�n");
            return FALSE;
        }

        // Vanhempien >4.2.0 rand pit� initialisoida.
        $phpv = phpversion();
        $phpver = str_replace(".", "", $phpv);
        settype( $phpver, "integer" );
        if ( $phpver <= 420 ) {
            srand((float) microtime() * 10000000);
        }

        // Ugly hack till this whole crap is rewritten.
        // Get only one-time one image.
        if(! isset( $GLOBALS['.pickRand'] )) {
            $GLOBALS['.pickRand'] = array();
            $GLOBALS['.pickRand'][$what] = array();
        }
        // Reset if all no unique items left.
        if (count($this->_cache[$what]) == count( $GLOBALS['.pickRand'][$what] )) {
            log::trace(_("Resetting shown array."));
            unset( $GLOBALS['.pickRand'][$what] );
            $GLOBALS['.pickRand'][$what] = array();
        }

        $canditates = array_diff( $this->_cache[$what], $GLOBALS['.pickRand'][$what]);

        $key = array_rand($canditates, 1);
        $GLOBALS['.pickRand'][$what][$key] = $canditates[$key];

        return $canditates[$key];
    }

    /**
     * Filer�nti.
     * Wau. Sin��se vasta ruma peto oletkin.
     */
    function filter( $what = "files" )
    {
        if (!isset($this->_cache[$what])) {
            log::trace("filter(): Ei voida suodataa _cache[$what]; ei ole olemassa");
            return FALSE;
        }
        $filtered = Array();
        foreach( $this->_cache[$what] as $entry ) {
            // Onlylist
            if ( is_array( $this->onlyList ) && count( $this->onlyList ) > 0 ) {
                reset( $this->onlyList );
                $discarded = TRUE;
                while( ( list( $key, $regex ) = each($this->onlyList) ) && $discarded == TRUE) {
                    if (eregi( $regex, $entry )) {
                        $discarded = FALSE;
                    }
                }
            } else {
                $discarded = FALSE;
            }

            // BlackList
            $found = FALSE;

            if ( is_array( $this->blackList ) && count( $this->blackList ) > 0 ) {
                reset( $this->blackList );
                while( ( list( $key, $regex ) = each($this->blackList) ) && $found == FALSE) {
                    if (eregi( $regex, $entry )) {
                        log::trace("filter(): Hyl�tiin tiedosto $entry regexill�$regex");
                        $found = TRUE;
                    }
                }
            }

            if ( $found == FALSE && $discarded == FALSE ) {
                $filtered[] = $entry;
            }
        }
        return $filtered;
    }

    function pseudoExists($pseudo)
    {
        $this->_checkDirContent();
        if(! in_array($pseudo, $this->pseudos )) {
            log::trace("pseudoExists(): Pseudoa $pseudo ei ole rekister�ty.");
            return false;
        }
        if (!isset( $this->_cache['pseudos'] )) {
            return false;
        } elseif (in_array($pseudo, $this->_cache['pseudos'] )) {
            if(! is_readable( $this->dir."/".$pseudo )) {
                log::trace("pseudoExists(): Pseudo $pseudo ei ole luettavissa");
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    function reader( $file, $path = "" )
    {
        $ext = basename(misc::getExt( $file ));
        if ( file_exists( BASE."/inc/mime/mime_handle_".$ext.".class.inc.php" )) {
            include_once( BASE."/inc/mime/mime_handle_".$ext.".class.inc.php" );
            $fclass = "mime_handle_$ext";
            if ( class_exists( $fclass )) {
                $mime =& new $fclass( $this->dir."/".$file );
                if(($handleMsg = $mime->canHandle()) !== true ) {
                    log::Trace(sprintf(_("Mime handler %s returned for canHandle: %"), $fclass, $handleMsg ));
                    return;
                } else {
                    if( $path != "" ) {
                        $mime->setPath( $path );
                    }
                    $this->_mimeobjects[] =& $mime;
                    return $mime->render();
                }
            } else {
                log::trace("Class $fclass does not exists but mime_handle_{$ext} file existed.");
            }
        } else {
            log::trace("No mime handler found for extension \"{$ext}\"");
        }

        return misc::readFile( $this->dir."/".$file );
    }

    function postReader() {
        $keys = array_keys($this->_mimeobjects);
        foreach( $keys as $key ) {
            $this->_mimeobjects[$key]->post();
        }
    }

    function getDefTags()
    {
        return $this->_tags;
    }

    function kuva( $txtFile, $path )
    {
        $this->_checkDirContent();
        if(! $this->pseudoExists("@KUVAT")) {
            return FALSE;
        }

        if (!isset( $this->org['kuvat'] )) {
            $this->org['kuvat'] = new bobTheWindower();
            $this->org['kuvat']->workDir(backslash($this->dir)."@KUVAT");
        }

        if ( $kuvaDir = $this->org['kuvat']->seekImgDir($txtFile) ) {
            log::trace("kuvaUri(): Kuva (hakemisto[$kuvaDir]) l�tyi");

            // Teemu-tavis: WHOOOOOOU!
            // Teemu-tavis: Aivan kuin t��k�n ei olisi keksitty helpompaa keinoa.
            // Teemu-inssi: N�n rakenne pysyy selv��
            // Teemu-tavis: No ompa "todella" selv�l�estymistapa.
            if (! isset(  $this->org['kuvat']->org[$kuvaDir] )) {
                $this->org['kuvat']->org[$kuvaDir] = new bobTheWindower();
                $this->org['kuvat']->org[$kuvaDir]->workDir( backslash($this->dir)."@KUVAT/".$kuvaDir );
            }

            if( $this->org['kuvat']->org[$kuvaDir]->count('files') > 0 ) {
                $kuva = $this->org['kuvat']->org[$kuvaDir]->pickRand('files');
                $r = $this->org['kuvat']->org[$kuvaDir]->genHtmlTag($kuva, backslash($path)."@KUVAT/".$kuvaDir);
                return $r;
            } else {
                log::trace("kuvaUri(): Ei tiedostoja kuvahakemistossa [$kuvaDir]");
                return FALSE;
            }
        } elseif ( $this->org['kuvat']->count('files') > 0 ) {
            $kuva = $this->org['kuvat']->pickRand('files');
            $r = $this->org['kuvat']->genHtmlTag($kuva, backslash($path)."@KUVAT/".$kuvaDir);
        }
        return $r;
    }

    function orderByPseudo($what="dirs")
    {
        $this->_checkDirContent();
        if (!isset($this->_cache[$what])) {
            log::trace("orderByPseudo(): Ei voida j�jestell�_cache[$what]; ei ole olemassa");
            return FALSE;
        }
        if (!isset( $this->_cache['pseudos'] )) {
            // Ei pseudoja. Ainoastaan akkosittain.
            sort( $this->_cache[$what] );
            reset( $this->_cache[$what] );
            return FALSE;
        } elseif (! in_array( "@ORDER", $this->_cache['pseudos'] )) {
            sort( $this->_cache[$what] );
            reset( $this->_cache[$what] );
            return FALSE;
        }
        $file = misc::readFile( $this->dir."/@ORDER" );
        // Koska jotkut k�tt�� "oikeaoppista" enteri� konvertoidaan.
        $fixed = misc::fixEndings( $file );

        $rowArray = explode("\n", $fixed );
        log::trace(_("Found #".count( $rowArray )." lines in @ORDER"));
        $sorted = Array();
        foreach( $rowArray as $row ) {
            if(in_array( trim($row), $this->_cache[$what] )) {
                $sorted[] = trim($row);
            } elseif ( $row != "" ) {
                log::trace(_("Unneccessary @ORDER mark; $row"));
            }
        }
        $toMerge = array_diff( $this->_cache[$what], $sorted );
        sort( $toMerge );
        $merged = array_merge( $sorted, $toMerge );
        $this->_cache[$what] = $merged;
        log::trace("Ordered ".count( $this->_cache[$what] )." rows in place $what");
    }

	function lastModified() {
		$this->_checkDirContent();
		if($this->lastmodified) return gmdate('D, d M Y H:i:s T', $this->lastmodified);
	}

    function _checkDirContent()
    {
        if (empty( $this->_handle )){
            $this->_readDir();
        }
    }

    function _readDir()
    {
        if (!empty( $this->_handle )) {
            log::trace("_readDir(): _handle on jo avattu. Luultavasti cache pilaantuu.");
        }
        if ($this->_handle = opendir( $this->dir )) {
            while (false !== ($file = readdir($this->_handle))) {
                if (in_array($file, $this->pseudos)) {
                    $this->_cache['pseudos'][] = $file;
                } elseif( $file != "." && $file != "..") {
                    if ( is_dir( $this->dir.DIRECTORY_SEPARATOR.$file )) {
                        $this->_cache['dirs'][] = $file;
                    } else {
                    	if(($mtime = filemtime($this->dir.DIRECTORY_SEPARATOR.$file)) > $this->lastmodified)
                    		$this->lastmodified = $mtime;
                        $this->_cache['files'][] = $file;
                    }
                }
            }
            log::trace("L�tyi ".count($this->_cache['dirs'])." kansiota, "
                       .count($this->_cache['files'])." tiedostoa ja "
                       .count($this->_cache['pseudos'])." pseudoa");
        } else {
            log::trace("Kansion '".$this->dir."' avaaminen ep�nnistui");
        }
    }

    /**
     * Tarkistaa onko oikeuksia kansioon.
     * @return bool
     */
    function _Auth() {
        if ( ! $this->pseudoExists("@AUTH") ) {
            return TRUE;
        }
        if ( Auth::Authenticate(backslash($this->dir)."@AUTH") ) {
            return TRUE;
        }
        return FALSE;
    }
}

/**
 * Kuva laajennukset bobTheBuilder luokkaan
 */
class bobTheWindower extends bobTheBuilder {

    var $blackList = Array();

    var $onlyList = Array (
        '.(jpeg|jpg)$',
        '.gif$',
        '.png$'
    );

    // Prosentuaalinen samann��syys joka menettelee.
    var $allowedSimilarity = 93;

    var $_map;

    function seekImgDir($file)
    {
        $this->_checkDirContent();
        // in_array() olisi k�s� mutta halutaan ei-merkkikoherkk�ratkaisu.
        if ( $imgDir = $this->_seekFolder( $file )) {
            return $imgDir;
        }
    }

    function seekFolder( $file )
    {
        if ($hakemisto = $this->_rmap( $file )) {
            return $hakemisto;
        }
        if ( $hakemisto = $this->_seekFolder( $file )) {
            $this->_map( $file, $hakemisto );
            return $hakemisto;
        }
        return FALSE;
    }

    function _seekFolder( $needle )
    {
        $haystack = $this->_cache['dirs'];
        $candidates = array();
        if ( count( $haystack ) < 1 ) {
            return FALSE;
        }
        foreach( $haystack as $possibleDir ) {
            $possibleDirI = strtoupper( $possibleDir );
            $needleI = strtoupper( $needle );

            if ( $needleI == $possibleDirI ) {
                // Hei, meill�� on pari!
                return $possibleDir;
            }

            // Hmm, hieman hienommat vertailut.
            similar_text( $possibleDirI, $needleI, $similarity );
            if ( $similarity >= $this->allowedSimilarity ) {
                log::trace("_seekFolder(): Riitt��similarity $needle:$similarity");
                // Lis��n mahdollisiin kanditaatteihin
                $key = settype($similarity, "integer").".".$possibleDir;
                $candidates[$key] = $possibleDir;
            }
        }
        // Tarkistetaan kanditaattien varalta.
        if ( count( $candidates ) == 1 ) {
            return current($candidates);
        }
        if ( count( $candidates ) < 1 ) {
            // Ei kanditaatteja.
            return FALSE;
        }
        krsort( $candidates );
        reset( $candidates );
        log::trace( "L�tyi ".count( $candidates )." kanditaattia '$needle'lle joista paras on ".$candidates[0]);
        return current($canditates);
    }

    function _map( $key, $val )
    {
        $this->_map[$key] = $val;
    }

    function _rMap( $key )
    {
        if (isset( $this->_map[$key] )) {
            return $this->_map[$key];
        }
        return FALSE;
    }

    function genHtmlTag( $kuva, $uriPath )
    {
        $this->_checkDirContent();
        $img = $this->genImgTag( $kuva, $uriPath );
        // Tehd�nk�linkki
        $fname = urldecode(eregi_replace('[.].{1,4}$', '', $kuva));

        if (eregi("([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])", $fname)) {
            $html = '<a href="'.$fname.'" style="background:none;">'.$img.'</a>';
        } else {
            /*
             * Check if image filename matches some dir in $sectionBob.
             * If so, create link from image to that dir.
             */
            global $sectionBob;
            $sdirs = $sectionBob->dirs();
            log::trace(print_r($sdirs, 1));
            if(is_array($sdirs) && in_array( $fname, $sdirs )) {
                global $uri;
                $html = '<a href="'.$uri->urli(array('page'=>$fname)).'" style="background:none;">'.$img.'</a>';
            } else {
                $html = $img;
            }
        }
        return $html;
    }

    function genImgTag( $kuva, $uriPath, $link = false )
    {
        $this->_checkDirContent();
        if (! in_array( $kuva, $this->_cache['files'])) {
            log::trace("genHtmlTags(): Tiedostoa [$kuva] ei ole.");
        }
        if ( function_exists("getimagesize")) {
            $imgParams = getimagesize(backslash($this->dir)."$kuva");
            $srcuri = backslash($uriPath).str_replace("+", " ", urlencode($kuva));
            $src = misc::scandify( $srcuri );
            $imgTag = '<img src="'.$src.'" '.$imgParams[3].' border="0" alt="" class="contimg" />';
        } else {
            $imgTag = '<img src="'.$src.'" border="0" alt="" class="contimg" />';
        }
        return $imgTag;
    }
}

class misc {

    /**
     * @deprecated  Depracated by log::trace()
     * @see log::trace
     */
    function tracemsg ( $message )
    {
        log::trace( "Kustuttu vanhaa misc::tracemsg toimintoa" );
        log::trace( $message );
    }

    /**
     * Lis� backslashin jos ei ole.
     */
    function backSlash( $str )
    {
        if( substr( $str, -1 ) != DIRECTORY_SEPARATOR ) {
            $str .= DIRECTORY_SEPARATOR;
        }
        return $str;
    }

    /**
     * @deprecated  K�t�log::dumpTrace()a ja log::trace()a
     * @see log::dumpTrace
     */
    function dumpTrace()
    {
        log::trace( "Vanhentunut misc::dumpTrace() kutsuttu." );
        return log::dumpTrace();
    }

    /**
     * @deprecated
     */
    function debuggaaja()
    {
        if ( $_SERVER['REMOTE_ADDR'] == "127.0.0.1" || $_SERVER['REMOTE_ADDR'] == "::1" ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Lukee tiedoston
     * @param $file luettava tiedosto
     * @return Luettu tiedosto
     */
    function readFile( $file )
    {
        $tmp = Array();
        if (! is_readable( $file )) {
            log::Trace("File {$file} is not readable");
        }
        if ( $tmp['fOpen'] = fopen( $file, "r" )) {
            if ( $tmp['fRead'] = fread($tmp['fOpen'], filesize($file))) {
                fclose( $tmp['fOpen'] );
                $tmp['fRead'] = encoding($tmp['fRead']);
                return $tmp['fRead'];
            } else {
                log::trace("Tiedostoa $file ei voitu lukea");
            }
        } else {
            log::trace("Tiedostoa $file ei voitu avata.");
        }
        return false;
    }

    /**
     * @deprecated
     */
    function phpCheck( $file, $str ) {
        log::trace("This is deprecated.");
    }

    /**
     * Suorittaa php tiedoston.
     * Alkuper�nen (toimiva) konsepti: michael at smartgrp dot net
     * @param $_execPhpfile_file Suoritettava PHP tiedosto
     * @return Suoritetun tiedoston ulostama sis�t�
     * @deprecated
     */
    function execPhpFile($_execPhpfile_file)
    {
        log::Trace("Depracated.");
        include_once(BASE."/inc/mime/mime_handle_php.class.inc.php");

        $_execPhpfile_read = mime_handle_php::evalPhp( $_execPhpfile_file );
    }

    function htfy( $str )
    {
        $str = misc::fixEndings( $str );
        $str = eregi_replace( "([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])",
                              "<a href=\"\\1://\\2\\3\" target=\"_blank\">\\1://\\2\\3</a>", $str);
        $str = eregi_replace( "(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))",
                              "<a href=\"mailto:\\1%s\" >\\1</a>", $str);
        $str = nl2br( $str );
        return $str;
    }

    /**
     * @deprecated
     */
    function wikify( $str, $pages = "" )
    {
        log::trace("Depracated");
        include_once(BASE."/inc/mime/mime_handle_txt.class.inc.php");
        return mime_handle_txt::wikify($str, $pages);
    }

    function getPages()
    {
        static $dirs;
        if( isset( $dirs ) ) {
            return $dirs;
        }
        global $mainBob, $sectionBob;
        $dirs = array();

        if ( is_a( $mainBob, "bobTheBuilder" )) {
            $dirs = array_merge( $dirs, $mainBob->dirs() );
        }

        if ( is_a( $sectionBob, "bobTheBuilder" )) {
            if ( is_array( $sdirs = $sectionBob->dirs() )) {
                $dirs = array_merge( $dirs, $sectionBob->dirs() );
            }
        }
        log::trace("L�tyi ".count( $dirs )." alisivua");
        return $dirs;
    }

    /**
     * Korvaa Win/Mac/Dos tiedostonp�teet unixin vastaavilla.
     */
    function fixEndings( $str ) {
        // Win/Dos
        $win = preg_replace("/\n\r/", "\n", $str );
        // Mac
        $mac = preg_replace("/\r/", "\n", $str );
        return $mac;
    }

	/**
	 * @todo tarvitaanko tätä enään? (uft-8)
	 */
    function scandify($string)
    {
    	return $string;
        $fixed = str_replace( 'ä', '%E4', $string );
        $fixed = str_replace( 'Ä', '%C4', $fixed );
        $fixed = str_replace( 'ö', '%F6', $fixed );
        $fixed = str_replace( 'Ö', '%D6', $fixed );
        $fixed = str_replace( ' ', '%20', $fixed );
        $fixed = str_replace( '@', '%40', $fixed );
        return $fixed;
    }

    /**
     * Does user use MSIE
     */
    function msIe()
    {
        if ( config::getSetting("Site", "msiefixes") == false ) {
            return false;
        }
        $msie='/msie.*.(win)/i';
        if(isset($_SERVER['HTTP_USER_AGENT']) &&
          preg_match($msie,$_SERVER['HTTP_USER_AGENT']) &&
          !preg_match('/opera/i',$_SERVER['HTTP_USER_AGENT'])) {
            sleep(rand(1,5)/10);
            return true;
        }
        return false;
    }

    /**
     * Laskee aikaa aina aloituksesta eteenpäin
     * @deprecated timer() functio korvaa.
     * @return Kulunut aika ensimmäisestä kutsusta.
     */
    function timer()
    {
		return timer();
    }

    /**
     * vaihtaa fontin kokoa.
     * @param $do joko plus tai minus.
     * @deprecated  Depracated by $css class.
     */
    function fontSize($do)
    {
        log::trace("Depracated");
        include_once( BASE."inc/css.class.inc.php" );
            switch ( $do ) {
                case "+" :
                case "plus" :
                    css::growFontSize();
                    break;
                case "-" :
                case "minus" :
                    css::shrinkFontSize();
                    break;
                default :
                    log::trace("Tuntematon komento $do");
                    break;
            }
    }

    /**
     * Poistaa tiedostop�tteen.
     * @deprecated Use removeExt();
     */
    function removeExt($str)
    {
        log::trace(_("Use removeExt()"));
        return removeExt($str);
    }
    /**
     * Get file extension
     */
    function getExt($file)
    {
        $parts = explode(".", $file );
        return $parts[count($parts)-1];
    }

    /**
     * Get CMS revision by looking .svn folder.
     */
    function getRevision()
    {
        if(!is_dir(BASE."/.svn")) {
            return "no .svn dir";
        } elseif ( !is_readable( BASE."/.svn/entries")) {
            return "not readable";
        }
        $svn = misc::readFile( BASE."/.svn/entries" );
        preg_match( "/revision=\"(.*)\"/", $svn, $rev);

        return $rev[1];
    }

    function endDebug()
    {
        global $smarty;
        if( $_SERVER['REMOTE_ADDR'] == "127.0.0.1" || $_SERVER['REMOTE_ADDR'] == "::1" ) {
            log::trace(_("Localhost address, turning debug on."));
            $_SESSION['debug'] = true;
        } elseif( $_SESSION['debug'] != true ) {
            return false;
        }
        $smarty->clear_cache("debug.tpl");
        log::trace("CMS rev: ".misc::getRevision().", PHP version: ".phpversion());
        log::trace("Suoritettu ajassa ".misc::timer());

        $dmesg = log::dumpTrace();
        $smarty->assign("_debugmsgs", $dmesg );
        return $smarty->fetch("debug.tpl");
    }

    /**
     * Determines the location of the system temporary directory.
     * Ripof from horde
     *
     * @access public
     *
     * @return string  A directory name which can be used for temp files.
     *                 Returns false if one could not be found.
     */
    function getTempDir()
    {
        $tmp_locations = array();

        /* First, try user configured temp dir */
        if($_conf_tmp = config::getValue("System", "temp")) $tmp_locations[] = $_conf_tmp;

        /* try PHP's upload_tmp_dir directive. */
        $tmp_locations[] = ini_get('upload_tmp_dir');

        /* Otherwise, try to determine the TMPDIR environment
         * variable. */
        $tmp_locations[] = getenv('TMPDIR');

        /* If we still cannot determine a value, then cycle through a
         * list of preset possibilities. */
        $tmp_locations = array_merge($tmp_locations, array(BASE.'/templates/cache/', '/tmp', '/var/tmp',
                               'c:\WUTemp', 'c:\temp', 'c:\windows\temp', 'c:\winnt\temp'));
        while (empty($tmp) && count($tmp_locations)) {
            $tmp_check = array_shift($tmp_locations);
            if (@is_dir($tmp_check) && @is_writable($tmp_check) && @is_readable($tmp_check)) {
                $tmp = $tmp_check;
            }
        }

        /* If it is still empty, we have failed, so return false;
         * otherwise return the directory determined. */
        return empty($tmp) ? false : $tmp;
    }

    /**
     * Creates a temporary directory in the system's temporary directory.
     *
     * @access public
     *
     * @param boolean $delete   Delete the temporary directory at the end of
     *                          the request?
     * @param string $temp_dir  Use this temporary directory as the directory
     *                          where the temporary directory will be created.
     *
     * @return string  The pathname to the new temporary directory.
     *                 Returns false if directory not created.
     */
    function createTempDir($delete = true, $temp_dir = null)
    {
        if (is_null($temp_dir)) {
            $temp_dir = misc::getTempDir();
        }

        if (empty($temp_dir)) {
            return false;
        }

        /* Get the first 8 characters of a random string to use as a temporary
           directory name. */
        do {
            $temp_dir .= '/' . substr(base_convert(mt_rand() . microtime(), 10, 36), 0, 8);
        } while (file_exists($temp_dir));

        $old_umask = umask(0000);
        if (!mkdir($temp_dir, 0700)) {
            $temp_dir = false;
        } elseif ($delete) {
            misc::deleteAtShutdown($temp_dir);
        }
        umask($old_umask);

        return $temp_dir;
    }

    /**
     * Removes given elements at request shutdown.
     *
     * If called with a filename will delete that file at request shutdown; if
     * called with a directory will remove that directory and all files in that
     * directory at request shutdown.
     *
     * If called with no arguments, return all elements to be deleted (this
     * should only be done by misc::_deleteAtShutdown).
     *
     * The first time it is called, it initializes the array and registers
     * misc::_deleteAtShutdown() as a shutdown function - no need to do so
     * manually.
     *
     * The second parameter allows the unregistering of previously registered
     * elements.
     *
     * @access public
     *
     * @param string $filename   The filename to be deleted at the end of the
     *                           request.
     * @param boolean $register  If true, then register the element for
     *                           deletion, otherwise, unregister it.
     * @param boolean $secure    If deleting file, should we securely delete
     *                           the file?
     */
    function deleteAtShutdown($filename = false, $register = true,
                              $secure = false)
    {
        static $dirs, $files, $securedel;

        /* Initialization of variables and shutdown functions. */
        if (is_null($dirs)){
            $dirs = array();
            $files = array();
            $securedel = array();
            register_shutdown_function(array('misc', '_deleteAtShutdown'));
        }

        if ($filename) {
            if ($register) {
                if (@is_dir($filename)) {
                    $dirs[$filename] = true;
                } else {
                    $files[$filename] = true;
                }
                if ($secure) {
                    $securedel[$filename] = true;
                }
            } else {
                unset($dirs[$filename]);
                unset($files[$filename]);
                unset($securedel[$filename]);
            }
        } else {
            return array($dirs, $files, $securedel);
        }
    }

    /**
     * Deletes registered files at request shutdown.
     *
     * This function should never be called manually; it is registered as a
     * shutdown function by misc::deleteAtShutdown() and called automatically
     * at the end of the request. It will retrieve the list of folders and
     * files to delete from misc::deleteAtShutdown()'s static array, and then
     * iterate through, deleting folders recursively.
     *
     * Contains code from gpg_functions.php.
     * Copyright (c) 2002-2003 Braverock Ventures
     *
     * @access private
     */
    function _deleteAtShutdown()
    {
        $registered = misc::deleteAtShutdown();
        $dirs = $registered[0];
        $files = $registered[1];
        $secure = $registered[2];

        foreach ($files as $file => $val) {
            /* Delete files */
            if ($val && @file_exists($file)) {
                /* Should we securely delete the file by overwriting the
                   data with a random string? */
                if (isset($secure[$file])) {
                    $random_str = '';
                    for ($i = 0; $i < filesize($file); $i++) {
                        $random_str .= chr(mt_rand(0, 255));
                    }
                    $fp = fopen($file, 'r+');
                    fwrite($fp, $random_str);
                    fclose($fp);
                }
                @unlink($file);
            }
        }

        foreach ($dirs as $dir => $val) {
            /* Delete directories */
            if ($val && @file_exists($dir)) {
                /* Make sure directory is empty. */
                $dir_class = dir($dir);
                while (false !== ($entry = $dir_class->read())) {
                    if ($entry != '.' && $entry != '..') {
                        @unlink($dir . '/' . $entry);
                    }
                }
                $dir_class->close();
                @rmdir($dir);
            }
        }
    }
}

?>