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

class mime_handle_png extends mime_handle_image {
    function mime_handle_png($file="") {
        $this->mime_handle_image( $file );
    }

    function canHandle() {
        if(($gd = parent::canHandle()) != true ) return $gd;
        if(!function_exists("imagecreatefrompng")) {
            return _("function ImageCreateFromPNG missing from GD; Can't handle PNG files");
        }
        return true;
    }

    function _decodeImage() {
        if($this->source = ImageCreateFromPNG($this->file)) return true;
        return false;
    }

    function _saveThumb($thumb) {
        if( function_exists("imagesavealpha")) {
            imagesavealpha($this->resource, true);
        }
        return parent::_saveThumb($thumb);
    }
}
