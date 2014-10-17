<?php
/***************************************************************************
          config.class.inc.php  -  Asetusten lukija
           -------------------
    begin                : Thu Dec 29 2004
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

include_once(BASE."/inc/log.class.inc.php");

class config {

    var $params = array();

    var $_db;

    function readIniFile($ini=null) {
        if(!function_exists("parse_ini_file")) {
            user_error("required function parse_ini_file() is not supported", E_USER_WARNING);
            return false;
        }
        if( $ini === null ) {
            if( is_readable($ini = BASE."/inc/config.".$_SERVER["SERVER_NAME"].".ini.php")) {
                // Foo
            } elseif( is_readable($ini = BASE."/inc/config.ini.php")) {
                // Bar
            } elseif( is_readable($ini = BASE."/inc/config.dist.ini.php")) {
                // FooBar
            } else {
                log::trace("INI file was not readable");
                return false;
            }
        } else {
            if(! is_readable( $ini ) ) {
                user_error("INI file \"{$ini}\" was not readable");
            }
        }

        $ini_array = parse_ini_file($ini, true);

        if(is_a($this, "config")) {
            $this->_mergeConfig($ini_array);
        } else {
            $config =& config::singleton();
            $config->_mergeConfig($ini_array);
        }
    }

    function loadDB( $params=null ) {
        $config =& config::singleton();
        if( $params === null ) {
            $params = config::getValue("db");
        }
        if(! $config->_connectDB()) {
            log::Trace("Could not load settings from database");
            return false;
        }
        if(($params = $config->_getFromDB()) === false ) {

        }
    }

    function getValue($group, $value=null, $silent=false) {
        $config =& config::singleton();
        if($value === null && !is_array($group)) {
            $val = $config->_getValGroup( $group );
        } elseif( is_array( $group )) {
            $val = $config->_getVal( $group[0], $group[1] );
        } else {
            $val = $config->_getVal( $group, $value );
        }
        return $val;
    }

    function getSetting($group, $key, $default = false) {
        $config =& config::singleton();
        if ( ! $config->_keyIsSet($group, $key)) {
            return $default;
        }
        return  $config->_getVal( $group, $key );
    }

    /**
     * Dumps all config values
     * @return array config values
     */
    function dumpAll() {
        $config =& config::singleton();
        return $config->_dump();
    }

    function setSetting($group,$key,$val) {
        $config =& config::singleton();
        return $config->_setSetting($group,$key,$val);
    }

    function &singleton() {
        static $config;
        if(!isset($config)) {
            $config = new config();
            if( file_exists( $distini = BASE."/inc/config.dist.ini.php" )) {
                $config->readIniFile($distini);
            }
            if( file_exists( $ini = BASE."/inc/config.ini.php")) {
                $config->readIniFile($ini);
            }
            if( file_exists( $serverini = BASE."/inc/config.".$_SERVER['SERVER_NAME'].".ini.php")) {
                $config->readIniFile($serverini);
            }
            if( is_array( $db = $config->_getValGroup("DB", true) )) {
                $this->loadDb($db);
            }
        }
        return $config;
    }

    // PRIVATES

    function _mergeConfig($confArray) {
        if(!is_array($confArray)) {
            log::Trace("Passed argument is not array. is ".gettype($confArray));
            return false;
        }

        foreach( $confArray as $confGroupKey => $confGroupVal ) {
            if(!isset($this->params[$confGroupKey])) $this->params[$confGroupKey] = array();

            foreach( $confGroupVal as $confKey => $confVal ) {
                $this->params[$confGroupKey][$confKey] = $confVal;
            }
        }
    }

    function _keyIsSet($group, $key) {
        if(isset($this->params[$group][$key] )) {
            return true;
        }
        return false;
    }

    function _getValGroup( $group, $silent=false ) {
        if(isset($this->params[$group])) {
            return $this->params[$group];
        } else {
            if( $silent != true ) {
                user_error("No setting group \"{$group}\" found", E_USER_NOTICE);
            }
            return false;
        }
    }

    function _getVal($group, $key, $silent=false) {
        if($this->_keyIsSet($group,$key)) {
            return $this->params[$group][$key];
        } else {
            if( $silent != true ) {
              user_error("Value for \"{$key}\" was not found from group \"{$group}\"", E_USER_NOTICE);
            }
            return false;
        }
    }

    function _connectDB($params) {
        include_once("DB.php");
        $this->_db =& DB::Connect( $params );
        if( DB::isError( $params ) ) {
            user_error("Error while connecting to DB: ".$this->_db->getMessage(), E_USER_WARNING);
            return false;
        }
        return true;
    }

    function _getFromDB() {
        if( isset( $this->params['db']['settingstable'] )) {
            $table = $this->params['db']['settingstable'];
        } elseif( isset( $this->params['db']['tableprefix'] )) {
            $table = $this->params['db']['tableprefix']."config";
        } else {
            $table = "config";
        }

        $res =& $this->_db->query("SELECT group, key, value FROM {$table}");
        if( DB::isError( $res )) {
            user_error("Error while fetching settings from DB: ".$res->getMessage(), E_USER_WARNING);
            return false;
        }

        while($row =& $res->fetchRow()) {
            $confArray[$row[0]][$row[1]] = $row[2];
        }

        $this->_mergeConfig($confArray);
    }

    function _dump() {
        return $this->params;
    }

    function _setSetting($group,$key,$val) {
        $this->params[$group][$key] = $val;
    }
}

?>