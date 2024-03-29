<?php
// $Id: Prefilter.php,v 1.1.1.1.2.2 2004/08/15 03:37:13 teemu Exp $


/**
* 
* "Pre-filter" the source text.
* 
* Convert DOS and Mac line endings to Unix, concat lines ending in a
* backslash \ with the next line, convert tabs to 4-spaces, add newlines
* to the top and end of the source text, compress 3 or more newlines to
* 2 newlines.
*
* @author Paul M. Jones <pmjones@ciaweb.net>
*
* @package Text_Wiki
*
*/

class Text_Wiki_Parse_Prefilter extends Text_Wiki_Parse {
	
	
	/**
	* 
	* Simple parsing method.
	*
	* @access public
	* 
	*/
	
	function parse()
	{
		// convert DOS line endings
		$this->wiki->source = str_replace("\r\n", "\n",
			$this->wiki->source);
		
		// convert Macintosh line endings
		$this->wiki->source = str_replace("\r", "\n",
			$this->wiki->source);
		
		// concat lines ending in a backslash
		$this->wiki->source = str_replace("\\\n", "",
			$this->wiki->source);
		
		// convert tabs to four-spaces
		$this->wiki->source = str_replace("\t", "    ",
			$this->wiki->source);
		   
		// add extra newlines at the top and end; this
		// seems to help many rules.
		$this->wiki->source = "\n" . $this->wiki->source . "\n\n";
		
		// finally, compress all instances of 3 or more newlines
		// down to two newlines.
		$find = "/\n{3,}/m";
		$replace = "\n\n";
		$this->wiki->source = preg_replace($find, $replace,
			$this->wiki->source);
	}

}
?>