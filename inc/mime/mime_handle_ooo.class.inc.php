<?php
/***************************************************************************
        mime_handle_ooo.class.inc.php  -  OpenOffice viewer
           -------------------
    begin                : Sun Dec 05 2004
    copyright            : (C) 2004 by Teemu A
                           (C) 2003-2004 Marko Djukic <marko@oblo.com>
                           (C) 2003-2004 Jan Schneider <jan@horde.org>
    email                : teemu@terrasolid.fi
 ***************************************************************************/

/***************************************************************************
 *                                                                         *
 * See the enclosed file COPYING for license information (LGPL). If you    *
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.   *
 *                                                                         *
 ***************************************************************************/

/**
 * This is mainly ripof from Horde project (http://www.horde.org)
 */

include_once(BASE."/inc/mime_handle.class.inc.php");

class mime_handle_ooo extends mime_handle {
    function mime_handle_ooo( $file ) {
        $this->mime_handle($file);
    }

    /**
     * Tests if xslt extension exists
     */
    function canHandle() {
        if( PHP_VERSION >= 5 ) {
            if(! extension("xsl")) return _("No XSL extension for PHP5. Can't generate OpenOffice files");
        } else {
            if(! extension("xslt") && ! function_exists('domxml_xslt_stylesheet_file'))
                return _("No XSLT extension. Can't generate OpenOffice files");
        }
        if(! extension("zlib")) return "No zlib extension. Can't unpack OpenOffice files";
        if(! include_once("Zip.php")) return "Can't load Zip class file";
        if(! class_exists("Archive_Zip")) return "No Archive_Zip class.";
        return true;
    }

    function render() {
        require_once("Zip.php");
        $tmpdir = backslash(misc::createTempDir(true));

        $xml_tags  = array('text:p', 'table:table ', 'table:table-row', 'table:table-cell', 'table:number-columns-spanned=');
        $html_tags = array('p', 'table border="0" cellspacing="1" cellpadding="0" ', 'tr bgcolor="#cccccc"', 'td', 'colspan=');

        define( 'ARCHIVE_ZIP_TEMPORARY_DIR', backslash($tmpdir) );
        $zip =& new Archive_Zip($this->file);
        $list = $zip->listContent();

        foreach ($list as $key => $file) {
            if ($file['filename'] == 'content.xml' ||
                $file['filename'] == 'styles.xml' ||
                $file['filename'] == 'meta.xml') {
                /*
                $content = $zip->extract($this->mime_part->getContents(),
                    array('action' => HORDE_COMPRESS_ZIP_DATA,
                          'info'   => $list,
                          'key'    => $key));
                */
                $zip->extract(array('set_chmod' => 400,
                                    'add_path' => $tmpdir,
                                    'by_name' => $file['filename']));
            }
        }
        if (PHP_VERSION >= 5) {
            $xsl = new DomDocument;
            $xsl->load(dirname(__FILE__) . '/ooo/main_html.xsl');

            $xml = new DomDocument;
            $xml->load($tmpdir . 'content.xml');

            $result = new xsltprocessor;
            $result->importStyleSheet($xsl);

            return $this->formatHtmlOutput($result->transformToXML($xml));

        } elseif (function_exists('domxml_xslt_stylesheet_file')) {
            // Use DOMXML
            $xslt = domxml_xslt_stylesheet_file(dirname(__FILE__) . '/ooo/main_html.xsl');
            $dom  = domxml_open_file($tmpdir . 'content.xml');
            $result = @$xslt->process($dom, array('metaFileURL' => $tmpdir . 'meta.xml', 'stylesFileURL' => $tmpdir . 'styles.xml', 'disableJava' => true));
            return $this->formatHtmlOutput($xslt->result_dump_mem($result));
        } else {
            // Use XSLT
            $xslt = xslt_create();
            $result = @xslt_process($xslt, $tmpdir . 'content.xml',
                                    dirname(__FILE__) . '/ooo/main_html.xsl', null, null,
                                    array('metaFileURL' => $tmpdir . 'meta.xml', 'stylesFileURL' => $tmpdir . 'styles.xml', 'disableJava' => true));
            if (!$result) {
                $result = xslt_error($xslt);
            }
            xslt_free($xslt);
            return $this->formatHtmlOutput($result);
        }
    }

    /**
     * get only body from html page
     * @todo Get meta tags
     */
    function formatHtmlOutput($str) {
        $str = mb_convert_encoding($str, config::getSetting("Site", "encoding", "ISO-8859-15"), "UTF-8");

        // Push metatags
        //$this->pushMeta(get_meta_tags( $str ));

        $regex = "'<body[^>]*?>.*?</body>'si";
        if ( preg_match( $regex, $str, $bodyContent )) {
            $bodyRegex = Array(
               "'<body[^>]*?>'si",
               "'</body>'si");
            $fixed = preg_replace($bodyRegex, "", $bodyContent[0]);
            $cleaned = trim( $fixed );
            return $cleaned;
        }
        log::trace("No body tags found.");
        return $str;
    }

    function cssPush($str) {
        global $params;
        if(! isset( $params['header'] )) {
            user_error("\$params['header'] does not exists", E_USER_WARNING);
        }
        $str = mb_convert_encoding($str, config::getSetting("Site", "encoding", "ISO-8859-15"), "UTF-8");
                $regex = "'<style[^>]*?>.*?</style>'si";
        if ( preg_match( $regex, $str, $bodyContent )) {
            $bodyRegex = Array(
               "'<style[^>]*?>'si",
               "'</style>'si");
            $fixed = preg_replace($bodyRegex, "", $bodyContent[0]);
            $cleaned = trim( $fixed );
            $params['header']['css'] .= $cleaned;
        }
    }
}
?>