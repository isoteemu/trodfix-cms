<?php
/***************************************************************************
        mime_handle.class.inc.php  -  php class form mime classes
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

include_once(BASE."/inc/mime_handle.class.inc.php");

class mime_handle_php extends mime_handle {

    var $valid = false;
    var $php;

    function mime_handle_php( $file ) {
        $this->mime_handle($file);
        $this->php = misc::readFile( $file );
        $this->valid = $this->phpCheck();
    }

    /**
     * Check if is php file.
     * @param $str string php string to be checked.
     * @return (bool)
     */
    function phpCheck( $str="" ) {
        if ( $str == "" ) {
            $str = $this->php;
        }
        $regex = "'<?php.*?>'si";
        if ( preg_match( $regex, $str ) ) {
            return true;
        } else {
            return false;
        }
    }

    function render() {
        if ( $this->valid == false ) {
            log::Trace("File {$this->file} is reported as invalid php file.");
            if( function_exists("highlight_string")) {
                ob_start();
                highlight_string($this->php);
                $source = ob_get_contents();
                ob_end_clean();
            } else {
                $source = "<pre>".htmlspecialchars( $this->php )."</pre>";
            }
            return $source;
        }
        return $this->evalPhp( $this->php );
    }

    function evalPhp($_execPhpfile_read) {
        preg_match_all("/(<\?php|<\?)(.*?)\?>/si", $_execPhpfile_read, $_execPhpfile_raw_php_matches);

        $_execPhpfile_php_idx = 0;

        while (isset($_execPhpfile_raw_php_matches[0][$_execPhpfile_php_idx])) {
            $_execPhpfile_raw_php_str = $_execPhpfile_raw_php_matches[0][$_execPhpfile_php_idx];
            $_execPhpfile_raw_php_str = str_replace("<?php", "", $_execPhpfile_raw_php_str);
            $_execPhpfile_raw_php_str = str_replace("?>", "", $_execPhpfile_raw_php_str);

            ob_start();
            eval("$_execPhpfile_raw_php_str;");
            $_execPhpfile_exec_php_str = ob_get_contents();
            ob_end_clean();

            $_execPhpfile_read = preg_replace("/(<\?php|<\?)(.*?)\?>/si", $_execPhpfile_exec_php_str, $_execPhpfile_read, 1);

            $_execPhpfile_php_idx++;
        }
        return $_execPhpfile_read;
    }
}

?>