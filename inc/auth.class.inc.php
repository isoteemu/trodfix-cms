<?php
/***************************************************************************
        auth.class.inc.php  -  Shyweb specified class[es]
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

class Auth {

    var $pam = array();

    var $needAuth;

    var $_timeOutSet;

    function Authenticate($authFile=null)
    {
        $auth =& auth::preboot();
        // Jos ollaan tällä asti, autentikoitia tarvitaan.
        Auth::needAuth(true);

        if($authFile != null) {
            $auth->appendFile($authFile);
        }
		if(empty($_SERVER['PHP_AUTH_USER'])) {
			log::trace(_("No username given."));
        // No funny bussiness
        } elseif(!preg_match("/^[a-zA-z1-9_]+$/",$_SERVER['PHP_AUTH_USER'])) {
            log::trace(sprintf(_("Funny bussiness detected. Unconsolidated characters in login name (%s)"),$_SERVER['PHP_AUTH_USER']));
            return false;
        }
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            return false;
        }
        if ( $auth->runAuth()) {
            return true;
        }
        return false;
    }

    function needAuth($bool=true) {
        $auth =& auth::preboot();
        $auth->needAuth = $bool;
    }

    /**
     * Kertoo autentikoinnin käytön.
     */
    function personelOnly()
    {
        $auth = &auth::preboot();
        if ( $auth->needAuth == TRUE ) {
            return TRUE;
        }
        return FALSE;
    }

    function speedLimit()
    {
        if ( $this->_timeOutSet == TRUE ) {
            return TRUE;
        }
        $this->_timeOutSet = TRUE;
        if(!isset( $_SESSION['auth']['tries'] )) {
            $_SESSION['auth']['tries'] = 1;
        }
        sleep( $_SESSION['auth']['tries'] );
        $_SESSION['auth']['tries']++;

        return TRUE;
    }

    function &preboot()
    {
        static $auth;
        if(!isset($auth)) {
            $auth = new Auth();

            if( ($authFile = config::getSetting("System", "passwd", false)) == false) {
                $authFile = realpath(BASE."/inc/passwd");
				log::trace(_("No authfile defined. using $authFile"));
            }
            if( file_exists($authFile)) {
                $auth->appendFile($authFile);
            } else {
				log::trace(sprintf(_("Authentication file %s does not exists"),$authFile));
			}
        }
        return $auth;
    }

    function appendFile($authFile) {

        if (!file_exists( $authFile )) {
            log::trace(sprintf(_("Authentication file %s does not exists."),$authFile));
        } elseif(!is_readable( $authFile )) {
            log::trace(sprintf(_("Authentication file %s is not readable."),$authFile));
        } else {
            //$unixpamdb = misc::fixendings($pamdb);
            $passwd = $this->_processPamDB( $authFile );
            if ( count( $passwd ) < 1 ) {
                log::trace("Varoitus, ei autentikoitirivejä.");
            }
            $this->pam = array_merge($this->pam, $passwd);
            return true;
        }
        return false;
    }

    function _processPamDB( $pamdb )
    {

        $passwd = array();
        $handle = fopen($pamdb, "r");
        while($entry = fscanf($handle, "%[a-zA-Z0-9_ ]:%[a-zA-z1-9,_ ]\n")) {
            list($user, $pswd) = $entry;
            $user = trim($user);
            $pswd = trim($pswd);
            if ( isset( $passwd[$user] )) {
                log::trace("User exists already on password DB. skipping user {$user}");
                continue;
            }
            $passwd[$user] = $pswd;
        }
        fclose($handle);
        return $passwd;
    }

    function runAuth()
    {
        $passwd = $this->pam;
        $uid = $_SERVER['PHP_AUTH_USER'];
        $pwd = $_SERVER['PHP_AUTH_PW'];

        //$this->speedLimit();

        if(! isset( $passwd[$uid] )) {
            log::trace(_("username ($uid) was not found in pw database"));
            return FALSE;
        }
        return $this->chkPasswd( $passwd[$uid], $pwd );
    }

    function chkPasswd( $from, $to )
    {
        if (crypt( $to, $from) === $from ) {
            log::trace(_("Accepted password."));
            return TRUE;
        }

        if ( $from === $to ) {
            log::trace("Plaintext salasana. Tätä ei tueta. käytä apachen crypt salasanaa.");
            return false;
        }
        log::trace("Tunnukseen assosioitu salasana ei täsmää");
        return false;
    }

    /**
     * Submits authentication headers to client.
     */
    function AuthHeaders($msg="protected area") {
        Header('WWW-Authenticate: Basic realm="'.$msg.'"');
        Header('HTTP/1.0 401 Unauthorized');
    }

    function User() {
        return $_SERVER['PHP_AUTH_USER'];
    }
}
?>