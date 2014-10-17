<?php
/***************************************************************************
        css.class.inc.php  -  Css container
           -------------------
    begin                : Thu Sep 30 2004
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

class css {

    var $_fontSizeMultiplier = 0;
    var $_fonrSizeStack = array();

    var $css = array(
             'font' => array (
                'size' => array (
                    'normal' => 11,
                    'h1'     => 16,
                    'h2'     => 14,
                    'h3'     => 13,
                    'koko5'  => 25,
                ),
            ),
        );

    /*
    function &css() {

        // Register shutdown function to emulate __destruct() magic on > php5
        if( version_compare(phpversion(), "5.0.0") == "-1" ) {
            register_shutdown_function(array(&$this, '__destruct'));
        }

        if( isset( $_SESSION['css'] )) {
            if( gettype( $_SESSION['css'] ) != "string" ) {
                log::trace("Warning: Somebody has fucked my container.");
            } else {
                $this = unserialize( $_SESSION['css'] );
            }
        }
    }
    */

    function &__destruct() {
        $_SESSION['css'] = serialize( $this );
    }

    function dump() {
        print_r( $this );
    }

    function &get() {
        $css = &css::singleton();
        return $css->css;
    }

    function &growFontSize() {
        $css = &css::singleton();
        $css->_fontSizeMultiplier++;
        $css->_setFontSizes( $css->_fontSizeMultiplier );
        //$css->_setFontSizes( 4 );
    }

    function &shrinkFontSize() {
        $css = &css::singleton();
        $css->_fontSizeMultiplier--;
        $css->_setFontSizes( $css->_fontSizeMultiplier );
        //$css->_setFontSizes( 2 );
    }

    function &_setFontSizes( $size ) {
        if (! isset( $this->_fontSizeStack[$size])) {
            log::trace("Calculating new font sizes for multiplier x".$size);
            $this->_calculateFontSizes( $size );
        }
        $this->css['font']['size'] = array_merge( $this->css['font']['size'], $this->_fontSizeStack[$size] );
    }

    function &_calculateFontSizes( $multiplier ) {
        if(!isset( $this->_fontSizeStack[0] )) {
            $this->_fontSizeStack[0] = $this->css['font']['size'];
        }

        foreach( $this->css['font']['size'] as $style => $size ) {
            $newSize = $this->css['font']['size'][$style] + ($this->_fontSizeStack[0][$style] *  $multiplier / 5 );
            if( empty( $reported )) {
                log::trace("Size for $style has changed from $size to $newSize");
                $reported = true;
            }
            $this->_fontSizeStack[$multiplier][$style] = round($newSize);
        }
    }

    function &singleton() {
        static $css;
        if (!isset( $css )) {

            if( isset( $_SESSION['css'] )) {
                if( $uncss = unserialize( $_SESSION['css'] )) {
                    if ( is_a( $uncss, 'css' )) {
                        $css = &$uncss;
                        log::trace("Loaded CSS parameters from _SESSION");
                    } else {
                        log::trace("Loaded parameters does not seem to be my object");
                    }
                } else {
                    log::trace("Could not unserialize parameters");
                }
            }
            if( !isset( $css )) {
                log::trace("Generated new css");
                $css = &new css;
            }

            // Register shutdown function to emulate __destruct() magic on > php5
            if( version_compare(phpversion(), "5.0.0") == "-1" ) {
                register_shutdown_function(array(&$css, '__destruct'));
            }
        }
        return $css;
    }
}
?>