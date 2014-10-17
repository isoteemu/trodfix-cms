<?php
/***************************************************************************
        log.class.inc.php  -  loki/debuggaus luokka
           -------------------
    begin                : Sat Jul 24 2004
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

if (!defined('E_STRICT')) {
    define('E_STRICT', 2048); // PHP 5
}

class log {

    /**
     * Array virheilmoituksien säilyttämiseen
     */
    var $messages;

    /**
     * Error handlerin asettamissen k�tett��
     * @see log::setErrorHandler
     * @private
     */
    var $_oldErrorHandler;

    /**
     * Lista phpn errortyypeistä
     */
    var $errortype = array (
		E_ERROR           => "Virhe",
		E_WARNING         => "Varoitus",
		E_PARSE           => "Parsing Error",
		E_NOTICE          => "Huomautus",
		E_CORE_ERROR      => "Core Error",
		E_CORE_WARNING    => "Core Warning",
		E_COMPILE_ERROR   => "Compile Error",
		E_COMPILE_WARNING => "Compile Warning",
		E_USER_ERROR      => "User Error",
		E_USER_WARNING    => "User Warning",
		E_USER_NOTICE     => "User Notice",
		E_STRICT          => "Runtime Notice"
	);

	var $_lock = false;

    /**
     * Error/debug viestin läetys/tallennus functio.
     * @param ... arguments to passed to string formation
     * @see log::dumpTrace
     */
    function &trace() {
        $log =& log::singleton();
    	if(!$log->_lock()) return false;

        $args = func_get_args();
        $message = $args[0];
        settype($message, "string");
        if(count($args > 1)) {
        	$args = array_slice($args,1);
			$message = vsprintf($message, $args);
        }
        $btrace = array();
        if (function_exists("debug_backtrace")) {
            $btrace = debug_backtrace();
            $btrace2 = array_slice($btrace, 1);
            $file = $btrace[0]['file'];
            $line = $btrace[0]['line'];
        }

        if ( is_callable('timer')) {
            $time = timer();
        } else {
            $time = "";
        }
        $log->messages[] = Array (
            'message'   => $message,
            'file'      => $file,
            'line'      => $line,
            'btrace'    => $btrace2,
            'time'      => $time
        );
        $log->_unlock();
    }

    /**
     * PHPn error_handlerin korvaaja.
     * T��functio korvaa phpn oman error_handlerin.
     */
    function &errorHandler($errno, $errmsg, $filename, $linenum, $vars) {
        $log = &log::singleton();

        $btrace = array();

        // Ei noticeja ja strictejä
		if( $errno == E_STRICT ) {
            return false;
        } elseif ( $errno == E_NOTICE ) {
            return false;
        }

		$log->_lock();

        if (function_exists("debug_backtrace")) {
            $btrace = debug_backtrace();
            if( strtolower($btrace[1]['function']) == "_logclasserrorhandler" ) {
                $btrace2 = array_slice( $btrace, 2 );
            } elseif (strtolower($btrace[1]['function']) == "_errorhandler") {
				$log->_emerg();
            } else {
                $btrace2 = array_slice( $btrace, 1 );
            }
        }

        // PHP lis� itse functioname(): tyylisen merkinn�. Ei niit�
        //$errmsg2 = substr( $errmsg, strlen( $btrace2[0]['function'])+4);
        $errmsg2 = $errmsg;

        $message = $errmsg2." [".$log->errortype[$errno]."]";

        if ( is_callable('timer')) {
            $time = timer();
        } else {
            $time = "";
        }
        $log->messages[] = Array (
            'message'   => $message,
            'file'      => $filename,
            'line'      => $linenum,
            'btrace'    => $btrace2,
            'time'      => $time
        );

        if( $errno == E_ERROR || $errno == E_USER_ERROR || $errno == E_COMPILE_ERROR) {
            $log->_emerg();
        }
        $log->_unlock();
    }

    /**
     * Dumpaa lokin viestit.
     * @see log::trace viestin luominen
     * @see log::errorHandler Phpn k�tt��viestinj�t�unctio
     * @return Array lokin viestit
     */
    function &dumpTrace()
    {
        $log = &log::singleton();
        if (is_array($log->messages) && count($log->messages) > 0) {
            $trace = array();
            foreach( $log->messages as $key => $msg ) {
                $row = count( $trace )+1;
                $trace[$row] = array(
                    'message'   => htmlspecialchars($msg['message']),
                    'file'      => $log->_parseTraceFile($msg['file']),
                    'realfile'  => htmlspecialchars( $msg['file'] ),
                    'line'      => $msg['line'],
                    'caller'    => $log->_parseTraceCaller($msg['btrace'][0]),
                );
                if (!empty( $msg['time'] )) {
                    $trace[$row]['time'] = $msg['time'];
                }
                if (is_array( $msg['btrace'] ) && count( $msg['btrace'] ) > 0 ) {
                    $tracestack = array_slice( $msg['btrace'], 1 );
                    foreach( $tracestack as $tracemsg ) {
                        $trace[$row]['stack'][] = array(
                            'file'      => $log->_parseTraceFile($tracemsg['file']),
                            'realfile'  => htmlspecialchars($tracemsg['file']),
                            'line'      => $tracemsg['line'],
                            'caller'    => $log->_parseTraceCaller($tracemsg)
                        );
                    }
                }
                // Remove processed entry from container
                unset( $log->messages[$key] );
            }
            return $trace;
        }
    }

    /**
     * Luon singleton tyyppisen olion.
     * @return object
     */
    function &singleton()
    {
        static $log;
        if (!isset( $log )) {

            $log = new log();

            // Use session container, if can be used.
            if( session_id() ) {
                $log->loadSession();
				$log->trace(_("new log session"));
            }

            // Init own error handler
            $log->setErrorHandler();
        }
        return $log;
    }

    function loadSession()
    {
        $log = &log::singleton();
        if( isset( $_SESSION['log'] )) {
            $_SESSION['log'] = array_merge($_SESSION['log'], $log->messages );
        } else {
            $_SESSION['log'] = $log->messages;
        }
        $log->messages =& $_SESSION['log'];
    }

    /**
     * Asettaa phpn errorhandlerin lokin functioon.
     * @see log::errorHandler PHPn k�tt��functio.
     */
    function &setErrorHandler() {
        ini_set("html_errors", "false");
        $this->_oldErrorHandler = set_error_handler("_logClassErrorHandler");
        if(!is_bool($this->_oldErrorHandler) && $this->_oldErrorHandler)
        	error_reporting(E_ALL ^ E_NOTICE);
    }

    /**
     * Luo luettavan merkinn� kutsuvasta functiosta.
     * @private
     * @return string
     */
    function &_parseTraceCaller( $traceEntry )
    {
        $caller = "";
        if ( isset( $traceEntry['function'] )) {
            if (isset( $traceEntry['class'] )) {
                $caller = $traceEntry['class'].$traceEntry['type'];
            }
            $caller .= $traceEntry['function']."()";
        }
        return $caller;
    }

    /**
     * Luo lyhennetyn version tiedoston nimestä
     * @private
     * @return string
     */
    function &_parseTraceFile( $file )
    {
        if (strlen( $file ) > 19 ) {
            $dirParts = explode(DIRECTORY_SEPARATOR, $file);
            if (count( $dirParts ) > 2 ) {
                $end = DIRECTORY_SEPARATOR.$dirParts[count($dirParts)-1];
                $begin = DIRECTORY_SEPARATOR.$dirParts[1].DIRECTORY_SEPARATOR;
            } else {
                $begin = "";
                $end = substr( $file, -16 );
            }
            $ffile = $begin."...".$end;
            return $ffile;
        }
        return $file;
    }

	function _emerg() {
		echo "<pre>";
		echo "<h1>Critical Error</h1>";
		print_r($this->messages);
		echo "</pre>";
		die();
	}

	function _lock() {
		if($this->_lock) return false;
		$this->_lock = true;
		return $this->_lock;
    }

    function _unlock() {
    	$this->_lock = false;
		return $this->_lock;
    }
}

// Asetetaan oma error handleri suorittamalla log::singleton kerran.
log::singleton();

/**
 * Tyhm�function log luokan function kutsumiseen.
 * PHP ei salli suoraan luokkien k�tt�ist�error_handleriss� niinp�t�� * kiert� sen.
 * @see log::errorHandler
 */
function _logClassErrorHandler($errno, $errmsg, $filename, $linenum, $vars) {
    log::errorHandler($errno, $errmsg, $filename, $linenum, $vars);
}