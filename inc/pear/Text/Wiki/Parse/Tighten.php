<?php
// $Id: Tighten.php,v 1.1.1.1.2.2 2004/08/15 03:37:13 teemu Exp $


/**
* 
* The rule removes all remaining newlines.
*
* @author Paul M. Jones <pmjones@ciaweb.net>
*
* @package Text_Wiki
*
*/

class Text_Wiki_Parse_Tighten extends Text_Wiki_Parse {
	
	
	/**
	* 
	* Apply tightening directly to the source text.
	*
	* @access public
	* 
	*/
	
	function parse()
	{
		$this->wiki->source = str_replace("\n", '',
			$this->wiki->source);
	}
}
?>