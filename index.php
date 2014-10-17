<?php
/***************************************************************************
           index.php  -  Trodfix cms.
           -------------------
    begin                : ?? Jun ?? 2004
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

define( "BASE", dirname(__FILE__));

// Bootstrap
require_once( BASE."/inc/bootstrap.inc.php");

// Smarty
include_once( BASE."/inc/smarty.class.inc.php");

// PEAR
// Pear::Text::Wiki
include_once('Text/Wiki.php');

if( $_SESSION['debug'] != true ) {
    error_reporting(0);
    ini_set('display_errors', 'false');
} else {
    Header("Cache-control: private, no-cache");
    Header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); # Past date
    Header("Pragma: no-cache");
    // Init session log storage
    log::loadSession();
}

// Smarty
$smarty = new Smarty_init($uri->section()."/".$uri->page());
if( $_SESSION['debug'] == true ) {
	log::trace(_("Cleaning smarty template compile; %s"),$smarty->clear_compiled_tpl());
}

//$smarty->debugging = true;
// Smartyn loppu

/**
 * @todo Make checks for dir usability
 */
$siteDir = dirname($_SERVER['SCRIPT_FILENAME']).DIRECTORY_SEPARATOR.config::getValue("Site", "site");
//$mainpage = $GLOBALS['settings']['root']['Site']['etusivu'];

// Jos sivua ei ole m�ritelty,
// ohjaa oikeaan osoitteeseen.
if (!$uri->wantedSection()) {
    $to = backslash("http://".$_SERVER['SERVER_NAME'].dirname( $_SERVER["SCRIPT_NAME"] )).$uri->sectionUrli();
    Header("Location: ".$to);
    die("<a href=\"$to\">$to</a>");
}

// Sis�t�uun esirakennus
$params = array(
    'header'    => array(), // Otsikkotietojen container.
    'precontent'=> "",      // Something that we want to show before articles
    'content'   => array(), // Sis�l� container.
    'style'     => array(), // Tyylille injektoitavat tiedot
    'footnotes' => array()  // Footnotet
);

// Template imagedir
$params['style']['images'] = $smarty->template_dir.DIRECTORY_SEPARATOR."images";
$params['style']['path'] = $smarty->template_dir;

// Fontin koko
if( isset($_GET['minus'])) {
    include_once( BASE."/inc/css.class.inc.php" );
    css::shrinkFontSize();

    // Don't allow page indexing
    $params['header']['noindex'] = true;
} elseif ( isset($_GET['plus'])) {
    include_once( BASE."/inc/css.class.inc.php" );
    css::growFontSize();

    // Don't allow page indexing
    $params['header']['noindex'] = true;
}


/**
 * Debuggaus.
 */
if ( $uri->getPathPart(0) == "@DEBUG") {
    $uri->pushOffset();
    if($uri->section() == "@DESTROY" ) {
        session_destroy();
        session_start();
    } else {
        $_SESSION['debug'] = true;
    }
}

/**
 * Admin interface.
 */
if ( $uri->getPathPart(0) == "@ADMIN") {
    if( config::getSetting("@ADMIN", "adminInterface", false)) {
        $uri->pushOffset();
        // Disable smarty caching.
        $smarty->caching = false;

        if( Auth::Authenticate() ) {
            log::trace(sprintf(_("Admin rights granted for user '%s'"), Auth::User()));
            $params['footnotes'][] = $smarty->fetch("admin.tpl");

        } else {
            Auth::AuthHeaders(sprintf(_("Admin interface")));
            Auth::needAuth(false);
        }
    } else {
        log::trace(_("Admin interface has been disabled by config"));
    }
}

/**
 * Documents
 * If @DOC prefix in url, set site root to doc dir
 */
if ( $uri->getPathPart(0) == "@DOCS" || $uri->getPathPart(0) == "@DOC" || $uri->getPathPart(0) == "@HELP" ) {
    if(is_dir(config::getValue("System", "helpdir"))) {
        config::setSetting("Site","site",config::getValue("System","helpdir"));
        $uri->section($uri->getPathPart(0));
        $siteDir = dirname($_SERVER['SCRIPT_FILENAME']).DIRECTORY_SEPARATOR.config::getValue("Site", "site");
    } else {
        log::trace(sprintf(_("Documentory dir %s does not exists"),config::getValue("System", "helpdir")));
    }
    $uri->pushOffset();
}


/**
 * Smartyn cache.
 * Tarkistetaan k�tet�nk�smartyn cachea.
 * Jos sivu on cachessa, sit�on turha rakentaa uudestaan.
 */
if( config::getSetting('Smarty','cache', false) == "true" ) {
    if ( is_array( $_POST ) && count( $_POST ) > 0 ) {
        log::trace("Cachea ei k�tet� POST asetettu");
    } elseif ( isset( $_GET['print'] )) {
        if( $smarty->is_cached( "printview.tpl" ) ) {
            $smarty->display("printview.tpl");
            misc::endDebug();
            die();
        }
    } else {
        if( $smarty->is_cached( "page.tpl" ) ) {
            log::trace("K�tet�n smartyn cachea");
            $smarty->display("page.tpl");
            misc::endDebug();
            die();
        }
    }
}

$sectionRealPath = realpath( $siteDir."/".$uri->section() );
if( ! is_dir( $sectionRealPath )) {
    log::trace("Saatu section ei ole kelvollinen; ".$uri->section()."[".$sectionRealPath."]; Ei hakemisto");
    $params['content']['cont']['404']['text'] = "<center><h1>"._("404: Page not found")."</h1></center>";
} elseif (strstr( $sectionRealPath, $siteDir )) {
    $uri->section( $section );
} else {
    log::trace("Saatu section ei ole kelvollinen; $sectionRealPath");
    $params['content']['cont']['404']['text'] = "<h1><center>"._("404: Page not found")."</center></h1>";
}
/**
 * sivujen muuttujie t�tt� oletuksilla.
 */
$params['header'] = array_merge( $params['header'], Array(
    'title' => ucfirst($uri->section()),
    'org' => config::getValue('Sivukohtaiset','otsikko'),
    'author' => config::getSetting('Sivukohtaiset', 'author', 'Teemu A'),
    'lang' => "fi",
    'uri' => $uri->sectionUrli(),
    'section' => $uri->section(),
    'page' => $uri->page(),
    'pageuri' => $uri->urli(),
    'base' => "http://".backslash($_SERVER['SERVER_NAME'].dirname( $_SERVER["SCRIPT_NAME"] )),
    'msie' => misc::msie(),
    'charset' => config::getSetting("Site", "encoding", "UTF-8"),
    'description' => config::getValue('Sivukohtaiset','kuvaus'),
    'keywords' => config::getValue('Sivukohtaiset','hakusanat')
));


$params['cwd'] = $uri->urli();

// Assign allready known world facts
$smarty->assign( $params );

/**
 * Sivun rakennus
 */

$sectionBob = new bobTheBuilder();
$sectionBob->workDir($siteDir."/".$uri->section());

if( $uri->page() != "" ) {
    $pageRealPath = realpath( $siteDir."/".$uri->section()."/".$uri->page() );
    if( ! is_dir( $pageRealPath )) {
        log::trace("Saatu page ei ole kelvollinen; $pageRealPath; Ei hakemisto");
        $currentBob =& $sectionBob;
        $params['content']['cont']['404']['text'] = "<h1><center>"._("404: Page not found")."</center></h1>";
    } elseif (strstr( $pageRealPath, $siteDir."/".$uri->section()."/".$uri->page() )) {
        $currentBob = new bobTheBuilder();
        $currentBob->workDir($siteDir."/".$uri->section()."/".$uri->page());
        $params['header']['subtitle'] = ucfirst( $uri->page );
        $params['header']['suburi'] = ucfirst( $uri->urli() );
    } else {
        $params['content']['cont']['404']['text'] = "<h1><center>"._("404: Page not found")."</center></h1>";
        log::trace("Saatu sivu ei ole kelvollinen; $pageRealPath");
        $currentBob =& $sectionBob;
    }
} else {
    $currentBob =& $sectionBob;
}

$mainBob = new bobTheBuilder();
$mainBob->workDir($siteDir);

// Get last modiefied
if($mtime = $mainBob->lastModified()) Header("Last-Modified: $mtime GMT");

// LOGO
if ($mainBob->pseudoExists( "@LOGO.png" )) {
    $params['header']['logo'] = backslash(config::getValue('Site', 'site'))."@LOGO.png";
} elseif($mainBob->pseudoExists( "@LOGO.jpg" )) {
    $params['header']['logo'] = backslash(config::getValue('Site', 'site'))."@LOGO.jpg";
} elseif($mainBob->pseudoExists( "@LOGO.gif" )) {
    $params['header']['logo'] = backslash(config::getValue('Site', 'site'))."@LOGO.gif";
}

if ($mainBob->pseudoExists( "@HEAD" )) {
    $params['head'] = misc::readfile($mainBob->workDir()."/@HEAD");
}
// Infotarjotin
if ($currentBob->pseudoExists( "@INFO" )) {
    $params['info']['content'] = misc::readfile($currentBob->workDir()."/@INFO");
}
if ($mainBob->pseudoExists( "@FOOT" )) {
    $params['foot'] = misc::readfile($mainBob->workDir()."/@FOOT");
}

$infoMenuLinks = $sectionBob->dirs();
if (is_array($infoMenuLinks) && count( $infoMenuLinks ) > 0 ) {
    foreach ( $infoMenuLinks as $infoLink ) {
        $params['info']['links'][$infoLink] = $uri->urli( array('page' => $infoLink ));
    }
}

// Topmenu
$topMenuLinks = $mainBob->dirs();
if (is_array($topMenuLinks) && count( $topMenuLinks ) > 0 ) {
    foreach ( $topMenuLinks as $TopLink ) {
        $params['menu']['links'][$TopLink] = $uri->sectionUrli( array('section' => $TopLink ));
    }
}

// Sivun sis�l� rakennus.
$files = $currentBob->ls();
if ( is_array( $files ) && count ( $files ) >= 1 ) {
    foreach($files as $file) {
        $kuvapolku = backslash(config::getValue('Site','site')).$uri->section()."/".$uri->page();
        $read = $currentBob->reader( $file, $kuvapolku );

        if($read === true) continue;

        $read = trim($read);
        if ( empty( $read )) {
            log::trace(sprintf(_("Empty file %s. Ignoring"), $file));
            continue;
        }
        $noExtFile = misc::removeExt( $file );
        $params['content']['cont'][$noExtFile] = "";
        if ( $kuva = $currentBob->kuva( $file, $kuvapolku ) ) {
            $params['content']['cont'][$noExtFile]['image'] = $kuva;
        }
        $params['content']['cont'][$noExtFile]['text'] .= $read;

    }
    $currentBob->postReader();
    if ( is_array( $userTags = $currentBob->getDefTags())) {
        $params['header'] = array_merge($params['header'], $userTags );
    }
} else {
    $params['content']['cont']['404']['text'] = "<h1><center>"._("404: Page not found")."</center></h1>";
    log::trace("Ei tiedostoja kansiossa ".$currentBob->workDir());
}

// Haku
if( $GLOBALS['settings']['root']['Search']['use'] == true ) {
    $params['google'] = array();
    $params['google']['haku'] = _("Search from site");
    $params['google']['section'] = $uri->sectionUrli(array('section' => config::getValue('Search','section')));

    if( $uri->section() == config::getValue('Search', 'section')) {
        // Ei "ei sivua" ilmoitusta".
        unset($params['content']['cont']['404']);

        // Suoritetaanko haku.
        if ( isset( $_REQUEST['q'] )) {
            $params['google']['haku'] = $_REQUEST['q'];
            $params['google']['showresults'] = true;
            include_once( BASE."/inc/search.class.inc.php" );
            $htdig = new htdig( config::getValue('Search','cgi'), config::getValue('Search','config'));
            $params['google'] = array_merge($params['google'], $htdig->search( $_REQUEST['q'] ));
        }
    }
}


// Ads...
if( $currentBob->pseudoExists( "@SPONSORIKUVAT" )) {
    $adBob = new bobTheWindower();
    $adBob->workDir( $currentBob->workDir()."/@SPONSORIKUVAT" );
    $ads = $adBob->ls();
    if ( is_array($ads) && count($ads) >= 1 ) {
        $path = backslash($uri->section());
        if( $uri->page() ) {
            $path .= backslash($uri->page());
        }
        $aduri = "sivut/".$path."@SPONSORIKUVAT/";
        foreach( $ads as $ad ) {
            $params['content']['ads'][] = $adBob->genHtmlTag($ad, $aduri).'<br />';
        }
    }
}

// Edelliselle sivulle.
if ( isset($_SESSION['prev'])) {
    $_SESSION['prev'] = array_splice($_SESSION['prev'], -5, 5);
    $params['info']['prev'] = array_reverse($_SESSION['prev']);
}
if ( $params['info']['prev'][0]["uri"] != $uri->urli() && !isset($params['content']['cont']['404']) ) {
    if ( $uri->page() == "") {
        $title = $uri->section();
    } else {
        $title = $uri->page();
    }
    $_SESSION['prev'][] = array(
        "uri" => $uri->urli(),
        "title" => $title
    );
}

// Autentikointi
if( Auth::personelOnly() ) {
    // Estet�n cachen k�tt�t�le sivulle
    $smarty->clear_cache(null);
    $smarty->caching = false;
    if( ! Auth::Authenticate() ) {
        $osio = $uri->section();
        if ( $uri->page() ) {
            $osio .= "/".$uri->page();
        }
        Auth::AuthHeaders(sprintf(_("Protected area: %s"), ucfirst($osio)));

        unset($params['content']);
        $params['content']['cont']['unauth']['text'] = "<h1><center>"._("401: Page viewing requires authentication")."</center></h1>";
        $params['header']['title'] = _("401 Unauthorized")." ".$params['header']['title'];
    }
}

if( !empty( $params['content']['cont']['404'] )) {
    $params['header']['noindex'] = true;
    header("Status: 404 Not Found");
}

$smarty->assign( $params );

if ( isset( $_GET['print'] )) {
    $page = $smarty->fetch("printview.tpl");
} else {
    $page = $smarty->fetch("page.tpl");
}

// Calculate md5 for page, so client knows if downloading page is worth bandwith
header("ETag: ".crc32($page));

if(config::getSetting('System','gzip', false) == "true") {
    if(!headers_sent() && strstr($_SERVER['HTTP_ACCEPT_ENCODING'], "gzip")) {
        log::trace(_("Using gzip"));
        header('Content-Encoding: gzip');
        $page = gzipencode($page);
    } else {
        log::trace(_("Gzip set to be used, but can't use"));
        $page .= misc::endDebug();
    }
} else {
	$page .= misc::endDebug();
}


die($page);

?>