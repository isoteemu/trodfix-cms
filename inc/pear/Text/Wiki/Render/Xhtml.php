<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
/**
 * Format class for the Xhtml rendering
 *
 * PHP versions 4 and 5
 *
 * @category   Text
 * @package    Text_Wiki
 * @author     Paul M. Jones <pmjones@php.net>
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    CVS: $Id: Xhtml.php,v 1.8 2005/07/29 10:15:05 toggg Exp $
 * @link       http://pear.php.net/package/Text_Wiki
 */

/**
 * Format class for the Xhtml rendering
 *
 * @category   Text
 * @package    Text_Wiki
 * @author     Paul M. Jones <pmjones@php.net>
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Text_Wiki
 */
class Text_Wiki_Render_Xhtml extends Text_Wiki_Render {

    var $conf = array(
    	'translate' => HTML_ENTITIES,
    	'quotes'    => ENT_COMPAT,
    	'charset'   => 'ISO-8859-1'
    );

    function pre()
    {
        // attempt to translate HTML entities in the source.
        // get the config options.
        $type = $this->getConf('translate', HTML_ENTITIES);
        $quotes = $this->getConf('quotes', ENT_COMPAT);
        $charset = $this->getConf('charset', 'ISO-8859-1');

        // have to check null and false because HTML_ENTITIES is a zero
        if ($type === HTML_ENTITIES) {

			// keep a copy of the translated version of the delimiter
			// so we can convert it back.
			$new_delim = htmlentities($this->wiki->delim, $quotes, $charset);

			// convert the entities.  we silence the call here so that
			// errors about charsets don't pop up, per counsel from
			// Jan at Horde.  (http://pear.php.net/bugs/bug.php?id=4474)
			$this->wiki->source = @htmlentities(
				$this->wiki->source,
				$quotes,
				$charset
			);

			// re-convert the delimiter
			$this->wiki->source = str_replace(
				$new_delim, $this->wiki->delim, $this->wiki->source
			);

		} elseif ($type === HTML_SPECIALCHARS) {

			// keep a copy of the translated version of the delimiter
			// so we can convert it back.
			$new_delim = htmlspecialchars($this->wiki->delim, $quotes,
			    $charset);

			// convert the entities.  we silence the call here so that
			// errors about charsets don't pop up, per counsel from
			// Jan at Horde.  (http://pear.php.net/bugs/bug.php?id=4474)
			$this->wiki->source = @htmlspecialchars(
				$this->wiki->source,
				$quotes,
				$charset
			);

			// re-convert the delimiter
			$this->wiki->source = str_replace(
				$new_delim, $this->wiki->delim, $this->wiki->source
			);
		}
    }

    function post()
    {
        return;
    }

}
?>
