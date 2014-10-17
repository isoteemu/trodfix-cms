<?php
/***************************************************************************
        smarty.class.inc.php  -  Smartyn asetusluokka
           -------------------
    begin                : Sun Jul 25 2004
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

if(!defined("PATH_SEPARATOR")) define("PATH_SEPARATOR", ":");

ini_set("include_path", BASE."/inc/smarty".PATH_SEPARATOR.ini_get("include_path"));

if (!defined('SMARTY_DIR'))
    define('SMARTY_DIR', BASE.'/inc/smarty/');
require_once(SMARTY_DIR.'Smarty.class.php');
include_once(BASE."/inc/config.class.inc.php");
include_once(BASE."/inc/smarty.modifiers.inc.php");

/**
 * Asettaa smartyn asetukset kohdalleen.
 */

class smarty_init extends smarty {

    var $templateBaseDir = "templates/";

    /**
     * Luokan constructor.
     * Asettaa smartyn parametrit jotakuinkin kohdilleen.
     */
    function smarty_init($section)
    {
        $this->Smarty();

        $this->_smarty_init_compilerDir();
        $this->_smarty_init_cache();

        $this->_load_template();

        $this->compile_id = $section;

        if(is_callable("smarty_register_extra_modifiers")) {
			smarty_register_extra_modifiers(&$this);
		}
    }

    /**
     * check that if theme file is usable, use it. if not, fallback to global theme dir
     */
    function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false)
    {
        if($this->_theme_template_exists($resource_name)) {
            return parent::fetch($resource_name, $cache_id, $compile_id, $display);
        } else {
            return parent::fetch("../".$resource_name, $cache_id, $compile_id, $display);
        }
    }

    function _theme_template_exists($resource_name)
    {
        $templateFile = backslash($this->template_dir).$resource_name;
        if( ! file_exists( $templateFile )) {
            return false;
        } elseif ( ! is_readable( $templateFile )) {
            log::trace(sprintf(_("template file %s exists but is not readable"), $templateFile));
            return false;
        }
        return true;
    }

    function _smarty_init_compilerDir()
    {
        $iniDir = config::getValue( "Smarty", "compile_dir");
        if (isset( $iniDir ) && is_writable( $iniDir ) && is_readable( $iniDir ) && $iniDir !== false ) {
            $this->compile_dir = $iniDir;
            return $iniDir;
        }
        if (is_writable(BASE."/".$this->templateBaseDir."/compile/") && is_readable(BASE."/".$this->templateBaseDir."/templates/compile/")) {
            $this->compile_dir = BASE."/".$this->templateBaseDir."/compile/";
            return BASE."/".$this->templateBaseDir."/compile/";
        }
        if(($dir = $this->_sys_tmp_dir( 'compile' ))=== false) {
            trigger_error(_("Could not find any dir suitable for smarty compiledir."),E_USER_ERROR);
        }
        /*
         * Check if can use subdirs.
         * http://smarty.php.net/manual/en/variable.use.sub.dirs.php
         */
        $this->use_sub_dirs = $this->_can_i_use_subdirs_for($this->templateBaseDir);

        return $dir;
    }

    function _smarty_init_cache()
    {
        $cache = config::getValue("Smarty", "cache");
        if(isset( $cache ) && $cache == "true" ) {
            if( $this->_smarty_init_cacheDir() ) {
                $this->caching = true;
                if (! $lifetime = config::getValue("Smarty", "cache_lifetime") ) {
                    $lifetime = 300;
                }
                log::trace(_("Using smarty cache. lifetime: {$lifetime}s."));
                return true;
            }
        }
        $this->caching = false;
        return false;
    }

    function _smarty_init_cacheDir()
    {
        $iniDir = config::getValue("Smarty", "cache_dir");
        if (isset( $iniDir ) && is_writable( $iniDir ) && is_readable( $iniDir )) {
            $this->cache_dir = $iniDir;
            return $iniDir;
        }
        if (is_writable(BASE."/templates/cache/") && is_readable(BASE."/".$this->templateBaseDir."/cache/")) {
            $this->cache_dir = BASE."/".$this->templateBaseDir."/cache/";
            return BASE."/".$this->templateBaseDir."/cache/";
        }
        $dir = $this->_sys_tmp_dir( 'cache' );
        return $dir;
    }

    function _sys_tmp_dir($sdir)
    {
        if( is_callable(array("misc", "getTempDir"))) {
            $tmp = misc::getTempDir();
        } else {
            $tmp = "/tmp";
        }
        if (is_writable($tmp)) {
            $dir = $tmp;

            $id = config::getValue("Site", "identifier");
            if ( is_writable($dir."/".$id) && is_readable($dir."/".$id)) {
                $dir .= "/".$id;
                $dosub = true;
            } elseif (mkdir( $dir."/".$id )) {
                $dir .= "/".$id;
                $dosub = true;
            }
            if ($dosub == true ) {
                if ( is_writable($dir."/".$sdir) && is_readable($dir."/".$sdir)) {
                    $dir .= "/".$sdir;
                } elseif (mkdir( $dir."/".$sdir )) {
                    $dir .= "/".$sdir;
                }
            }
            $wdir = $sdir."_dir";
            $this->$wdir = $dir;
            log::trace("Using $dir as smartys $wdir");
            return "$dir";
        } else {
            log::trace("Could not find system temp dir");
            return false;
        }
    }

    function _load_template()
    {
        $userTheme = config::getValue("Site", "theme");
        if (!isset( $userTheme ) || ! is_readable( backslash($this->templateBaseDir).$userTheme."/page.tpl" ) ) {
            log::trace("Not usable theme defined ($userTheme)");
            if(! $userTheme = $this->getTheme()) {
                trigger_error("You're now in big shit. Could not find theme. Panic!", E_USER_ERROR);
                return false;
            }
        }
        $this->template_dir = backslash($this->templateBaseDir).$userTheme;
        $this->config_dir = backslash($this->templateBaseDir).$userTheme;
        return true;
    }

    function _can_i_use_subdirs_for($dir)
    {
        if( is_writable( $dir )) {
            return true;
        }
        return false;
    }

    /**
     * Returns first "usable" theme
     */
    function getTheme()
    {
        if (!is_dir( $this->templateBaseDir )) {
            log::trace("Template Basedir ({$this->templateBaseDir}) is not dir.");
            return false;
        }
        $canditades = array();
        if( $tempdirhandle = dir( $this->templateBaseDir )) {
            while (false !== ($entry = $tempdirhandle->read())) {
                if( $entry == "." ) continue;
                if( $entry == ".." ) continue;

                // Jump over common cache_dir and compile_dir
                if( $entry == "cache" ) continue;
                if( $entry == "compile" ) continue;

                $jag = backslash($this->templateBaseDir).$entry;

                // Check for more obscure cache & compile dirs
                if( realpath($jag) == realpath($this->cache_dir)) continue;
                if( realpath($jag) == realpath($this->compile_dir)) continue;

                // Couple check;
                if(! is_dir( $jag )) continue;
                if(! is_readable( $jag )) {
                    log::trace("Found possible candidate but it's not readable");
                    continue;
                }
                if ( file_exists( backslash($jag)."page.tpl")) {
                    log::trace("Found theme $entry. Good Luck");
                    $tempdirhandle->close();
                    return $entry;
                }
            }
            log::trace("Did not found possible theme canditades");
            $tempdirhandle->close();
            return false;
        } else {
            log::Trace("Could not open templateBaseDir ({$this->templateBaseDir})");
            return false;
        }
    }
}