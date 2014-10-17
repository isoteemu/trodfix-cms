<?php
/***************************************************************************
    mime_handle_png.class.inc.php  -  PNG mime handler
           -------------------
    begin                : Sat Aug 10 2004
    copyright            : (C) 2005 by Teemu A
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

include_once(BASE."/inc/mime/mime_handle_image.class.inc.php");

class mime_handle_jpg extends mime_handle_image {
    function mime_handle_jpg($file="") {
        $this->mime_handle_image( $file );
    }

    function canHandle() {
        if(($gd = parent::canHandle()) != true ) return $gd;
        if(!function_exists("imagecreatefromjpeg")) {
            return "function ImageCreateFromJPEG missing from GD; Can't handle JPG files";
        }
        return true;
    }

    function _decodeImage() {
        if($this->source = ImageCreateFromJPEG($this->file)) return true;
        return false;
    }

    function _description($file) {
        if(!($desc = parent::_description($file))) {
            $desc = $this->_readExif($file);
        }
        return $desc;
    }

    function _readExif($name) {
        if( function_exists("exif_read_data")) {
            $exif = @exif_read_data($name);
            $comment = "";
            if( isset($exif['COMMENT'])) {
                foreach( $exif['COMMENT'] as $tmp['cmt'] ) {
                    if( $comment != "" ) $comment .= "<br />";
                    $comment .= simpleHtfy($tmp['cmt']);
                }
            }
            return $comment;
        } else {
            log::trace("Your PHP does not support exif_read_data. Can't use embedded comments");
        }
        return false;
    }
}
