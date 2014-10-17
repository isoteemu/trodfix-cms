<?php
/***************************************************************************
       smarty.modifiers.inc.php  -  Alkemisti cms.
           -------------------
    begin                : Sat Apr 29 2006
    copyright            : (C) 2006 by Teemu A
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

function smarty_modifier_accesskey($str, $ackonly = false) {
	static $used;

	if(!isset($used)) $used = array();
	if(isset($used[$str])) {
		$ack = $used[$str];
	} else {
		$chars = str_split($str);
		foreach($chars as $char) {
			if($char == " ") continue;
			if(in_array(strtolower($char), $used) || in_array(strtoupper($char), $used)) continue;
			$used[$str] = $char;
			$ack =& $used[$str];
			break;
		}
	}

	if($ack) {
		if($ackonly) return $ack;
		return 'accesskey="'.strtolower($ack).'"';
	}
	return "";
}

function smarty_modifier_accesskey_visual($str) {
	$ack = smarty_modifier_accesskey($str,true);
	if(!$ack) return $str;
	$tag = '<span class="accesskey">'.$ack.'</span>';

	$str = substr_replace($str, $tag, strpos($str, $ack),1);
	return $str;
}

function smarty_register_extra_modifiers(&$smarty) {
	// Lista modifiereista
	$modifiers = array(
		'ack' 	=> 'smarty_modifier_accesskey',
		'ackv' 	=> 'smarty_modifier_accesskey_visual'
	);
	foreach($modifiers as $smartymod => $function) {
		if(is_callable($function))
			$smarty->register_modifier($smartymod, $function);
		else
			log::trace("smarty modifier %s to function %s is not callable", $smartymod, $function);
	}
}

?>