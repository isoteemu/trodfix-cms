<?php

//    SWFHEADER CLASS - PHP SWF header parser
//    Original Copyright (C) 2004  Carlos Falo Herv�s
//    Modification Copyright (c) 2004  Teemu A <teemu@terrasolid.fi>
//
//    This library is free software; you can redistribute it and/or
//    modify it under the terms of the GNU Lesser General Public
//    License as published by the Free Software Foundation; either
//    version 2.1 of the License, or (at your option) any later version.
//
//    This library is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
//    Lesser General Public License for more details.
//
//    You should have received a copy of the GNU Lesser General Public
//    License along with this library; if not, write to the Free Software
//    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

include_once(BASE."/inc/mime_handle.class.inc.php");

class mime_handle_swf extends mime_handle {

    var $file;
    var $magic;
    var $compressed;
    var $version;
    var $size;
    var $width;
    var $height;
    var $fps;
    var $frames;

    function mime_handle_swf($file="") {
        $this->mime_handle( $file );
        $this->parse();
    }

    function parse($file="") {
        if( $file == "" ) {
            $file = $this->file;
        }

        $fp = @fopen($file, "rb");
        if(! $fp ) {
            user_error("Could not open file {$file}.", E_USER_ERROR);
            return FALSE;
        }

        // Magic
        $this->magic = fread($fp,3);
        if ($this->magic!="FWS" && $this->magic!="CWS") {
            user_error("{$file} Is not supported SWF filetype", E_USER_ERROR);
            return false;
        }

        // Compression
        if (substr($this->magic,0,1) == "C") {
            $this->compressed = true;
        } else {
            $this->compressed = false;
        }

        // Version
        $this->version = ord(fread($fp,1));

        // Size
        $lg = 0 ;
        // 4 LSB-MSB
        for ($i=0;$i<4;$i++) {
            $t = ord(fread($fp,1));
            $lg += ($t<<(8*$i));
        }
        $this->size = $lg;

        // RECT... we will "simulate" a stream from now on... read remaining file
        $buffer = fread($fp,$this->size) ;
        if ($this->compressed == true) {
            // First decompress GZ stream
            if( function_exists( "gzuncompress" )) {
                $buffer = gzuncompress($buffer,$this->size);
            } else {
                log::trace("Function gzuncompress() is missing from php. Can't parse swf");
                return false;
            }
        }
        $b      = ord(substr($buffer,0,1));
        $buffer = substr($buffer,1);
        $cbyte  = $b;
        $bits   = $b>>3;
        $cval   = "";
        // Current byte
        $cbyte &= 7;
        $cbyte<<= 5;
        // Current bit (first byte starts off already shifted)
        $cbit   = 2;
        // Must get all 4 values in the RECT
        for ($vals=0;$vals<4;$vals++) {
            $bitcount = 0;
            while ($bitcount<$bits) {
                if ($cbyte&128) {
                    $cval .= "1";
                } else {
                    $cval .="0";
                    }
                $cbyte<<=1;
                $cbyte &= 255;
                $cbit--;
                $bitcount++;
                // We will be needing a new byte if we run out of bits
                if ($cbit<0) {
                    $cbyte  = ord(substr($buffer,0,1));
                    $buffer = substr($buffer,1);
                    $cbit   = 7;
                    }
                }
            // O.k. full value stored... calculate
            $c       = 1;
            $val     = 0;
            // Reverse string to allow for SUM(2^n*$atom)
            $tval = strrev($cval) ;
            for ($n=0;$n<strlen($tval);$n++) {
                $atom = substr($tval,$n,1) ;
                if ($atom=="1") $val+=$c ;
                // 2^n
                $c*=2 ;
            }
            // TWIPS to PIXELS
            $val/=20 ;
            switch ($vals) {
                case 0:
                    // tmp value
                    $this->width = $val ;
                break ;
                case 1:
                    $this->width = $val - $this->width ;
                break ;
                case 2:
                    // tmp value
                    $this->height = $val ;
                break ;
                case 3:
                    $this->height = $val - $this->height ;
                break ;
                }
            $cval = "";
        }

        // Frame rate
        $this->fps = Array();
        for ($i=0;$i<2;$i++) {
            $t      = ord(substr($buffer,0,1));
            $buffer = substr($buffer,1);
            $this->fps[] = $t;
        }

        // Frames
        $this->frames = 0;
        for ($i=0;$i<2;$i++) {
            $t      = ord(substr($buffer,0,1));
            $buffer = substr($buffer,1);
            $this->frames += ($t<<(8*$i));
        }
        fclose($fp);
    }

    function render($trans = true, $qlty = "high") {
        if (! isset( $this->magic )) {
            log::trace( "Called render() but magic is empty");
        }
        $endl = chr(13);
        $name = basename(misc::removeExt($this->file));
        $fname = basename($this->file);
        $object = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=' . $this->version . ',0,0,0" width="' . $this->width . '" height="' . $this->height . '" id="' . $name . '" align="middle">'.$endl;
        $object .= '<param name="allowScriptAccess" value="sameDomain" />'.$endl;
        if ($trans) {
            $object .= '<param name="wmode" value="transparent" />'.$endl;
        }
        $object .= '<param name="movie" value="'.$this->path.$fname.'" />'.$endl;
        $object .= '<param name="quality" value="'.$qlty.'" />'.$endl;
        //$object .= '<param name="bgcolor" value="'.$bgcolor.'" />'.$endl;
        $object .= '<embed src="'.$this->path.$fname.'" ';
        if ($trans) $object .= 'wmode="transparent" ' ;
        $object .= 'quality="'.$qlty.'" width="'.$this->width.'" height="'.$this->height.'" name="'.$name.'" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />'.$endl;
        $object .= '</object>'.$endl;
        return $object;
    }
}


?>