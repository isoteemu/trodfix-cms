<?php
/***************************************************************************
        mime_handle_sxw.class.inc.php  -  Base class form mime classes
           -------------------
    begin                : Sun Dec 05 2004
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

require_once(BASE."/inc/mime/mime_handle_ooo.class.inc.php");

class mime_handle_sxw extends mime_handle_ooo {
    function mime_handle_sxw( $file ) {
        $this->mime_handle_ooo($file);
    }
}

?>