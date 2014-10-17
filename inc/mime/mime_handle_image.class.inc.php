<?php
/***************************************************************************
    mime_handle_image.class.inc.php  -  base for image mime handlers
           -------------------
    begin                : Sat Aug 10 2005
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

define("THUMBNAIL_HEIGHT", 150);
define("THUMBNAIL_WIDTH", 150);

require_once(BASE."/inc/mime_handle.class.inc.php");

class mime_handle_image extends mime_handle {

    var $cachePath;
    var $cacheName;
    var $thumbUri;
    var $imageparams = array();
    var $source;
    var $resource;

    var $thumbimage;

    function mime_handle_image($file="") {
        $this->mime_handle( $file );
    }

    function canHandle() {
        if(! extension("gd") ) return _("GD extension is not available. Can't handle images");
        if( $this->_cachePath() === false) {
            return _("Can't save thumbnails anywhere.");
        }
        return true;
    }

    function render() {
        global $uri;

        $_x = config::getSetting("Site", "thumbWidth", THUMBNAIL_WIDTH);
        $_y = config::getSetting("Site", "thumbHeight", THUMBNAIL_HEIGHT);

        $this->cacheName = $_x."x".$_y."-".basename($this->file)."-".filemtime($this->file).".png";

        // current working path is not set before canHandle.
        // re-check cachePath so thumbs uri is updated.
        $this->params['thumbFile'] = backslash($this->_cachePath(null,true)).$this->cacheName;
        if( ! file_exists($this->params['thumbFile']) || ! is_readable($this->params['thumbFile'])) {
            if(function_exists("memory_get_usage")) $mem = memory_get_usage();
            log::trace(sprintf(_("No thumbfile %s exists for image %s. Creating..."),basename($this->params['thumbFile']), basename($this->file)));
            if(!$this->_decodeImage()) {
                log::trace(sprintf(_("Image %s decoding failed :("),basename($this->file)));
                return false;
            }
            $this->params['x'] = imagesx($this->source);
            $this->params['y'] = imagesy($this->source);

            if(($this->params['x']/$_x) < ($this->params['y']/$_y)) {
                $this->params['thumb_x'] = $_x;
                $this->params['thumb_y'] = ($_x/$this->params['x'])*$this->params['y'];
            } else {
                $this->params['thumb_y'] = $_y;
                $this->params['thumb_x'] = ($_y/$this->params['y'])*$this->params['x'];
            }

            if($this->_generateThumb()) imagedestroy($this->source);

            if(!$this->_saveThumb($this->params['thumbFile'])) {
                log::trace(sprintf(_("Saving thumb '%s' failed"),$this->params['thumbFile']));
                return false;
            } else {
                // Free memory by destroying source resource
                imagedestroy($this->resource);
            }

            if(isset($mem)) log::trace(sprintf(_("Image processing too %d to %d (diff:%d) bytes of memory"),$mem, memory_get_usage(), memory_get_usage()-$mem));
        }

        // Put thumbnail info into container
        mime_handle_image_container::push($this->params['thumbFile']);
        mime_handle_image_container::name(basename(removeExt($this->file)));
        mime_handle_image_container::url(backslash($this->_thumbUri()).$this->cacheName);
        mime_handle_image_container::viewUrl($uri->urli(array('thumbimage' => rawurlencode(mime_handle_image_container::name()))));
        mime_handle_image_container::imageUrl($uri->translatePath($this->file));

        if($_x && $_y) mime_handle_image_container::size($_y, $_x);

        if($desc = $this->_description($this->file)) {
            mime_handle_image_container::description($desc);
        }

        // If this image is wanted to be show, add own special entry
        if(isset($_REQUEST['thumbimage'])) {
            if(rawurldecode($_GET['thumbimage']) == removeExt(basename($this->file))) {
                log::trace(_("Oh baby, we got a match! she wants me, wants me badly!"));
                $img = array();
                $img['name'] = urldecode($_GET['thumbimage']);
                $img['url']  = $uri->translatePath($this->file);
                $img['desc'] = $desc;

                if($this->params['x']) $img['width'] = $this->params['x'];
                if($this->params['y']) $img['height'] = $this->params['y'];

                if(!isset($img['width']) || !isset($img['height'])) {
                    $_imginfo       = getimagesize($this->file);
                    $img['width']   = $_imginfo[0];
                    $img['height']  = $_imginfo[1];
                }
                if($desc) {
                    $img['desc'] = $desc;
                }
                $this->thumbimage = $img;
            }
        }
        return true;
    }

    function post() {
        global $smarty, $params;
        if(mime_handle_image_container::count() < 1) {
            log::trace(sprintf(_("No images in image container (%d). WTF?"), mime_handle_image_container::count()));
            return false;
        }

        if(!empty($this->thumbimage)) {
            // Set next and prev
            if(mime_handle_image_container::nextEntry($this->params['thumbFile'])) {
                $this->thumbimage['next'] = mime_handle_image_container::fetch();
            }
            if(mime_handle_image_container::prevEntry($this->params['thumbFile'])) {
                $this->thumbimage['prev'] = mime_handle_image_container::fetch();
            }

            $smarty->assign(array('thumbimage' => $this->thumbimage));
        }

        // Using $globals make me feel dirty...
        // But somehow, we need to know if thumbnail page has
        // been created.
        if(!isset($GLOBALS['_mime_handle_image'])) $GLOBALS['_mime_handle_image'] = 1;
        if($GLOBALS['_mime_handle_image'] >= mime_handle_image_container::count()) {

            // Buildup HTML
            $smarty->assign(array('thumbs' => mime_handle_image_container::fetchAll()));
            $params['precontent'] .= $smarty->fetch("thumbnails.tpl");
        }
        $GLOBALS['_mime_handle_image']++;
        return true;
    }

    function _cachePath($path=null, $recheck = false) {
        if($path !== null) $this->cachePath = $path;
        if(empty($this->cachePath) || $recheck) {
            $thumbUri = backslash($this->path)."@THUMBS";
            $thumbDir = backslash(dirname($this->file))."@THUMBS";
            if(is_writable($thumbDir)) {
                $this->cachePath = $thumbDir;
                $this->_thumbUri($thumbUri);
            } elseif(mkdir($thumbDir, 0700)) {
                $this->cachePath = $thumbDir;
                $this->_thumbUri($thumbUri);
            } else {
                return false;
            }
        }
        return $this->cachePath;
    }

    function _thumbUri($uri=null) {
        if($uri !== null) $this->thumbUri = $uri;
        return $this->thumbUri;
    }

    function _decodeImage() {
        log::trace(_("I should be extended. Doit, doitnow!"));
        return false;
    }

    function _generateThumb() {
        if(function_exists("ImageCreateTrueColor")) {
            $this->resource = ImageCreateTrueColor( $this->params['thumb_x'], $this->params['thumb_y']);
        } else {
            log::trace(_("Your PHPs GD does not support ImageCreateTrueColor. Using plain ImageCreate"));
            $this->resource = ImageCreate( $this->params['thumb_x'], $this->params['thumb_y']);
        }
        if(!is_resource($this->source)) {
            log::trace(sprintf(_("\$this->source is not resource %"),$this->source));
            return false;
        }
        if(!is_resource($this->resource)) {
            log::trace(sprintf(_("\$this->resource is not resource %"),$this->resource));
            return false;
        }
        if(!function_exists("imagecopyresampled")) {
            log::trace(_("Your PHPs GD does not support imagecopyresampled. Using plain imagecopyresized."));
            $imgcopy = "imagecopyresized";
        } else {
            $imgcopy = "imagecopyresampled";
        }
        if($imgcopy($this->resource, $this->source, 0, 0, 0, 0,
                            $this->params['thumb_x'], $this->params['thumb_y'],
                            $this->params['x'], $this->params['y'])) {
            return true;
        } else {
            log::trace(sprintf(_("Thumbnail %s creation failed by using %s"),$this->cacheName, $imgcopy));
            return false;
        }
    }

    function _saveThumb($thumb) {
        log::trace(sprintf(_("Creating thumbfile %s"),basename($thumb)));
        if( $this->cachePath === false ) return true;

        // Use temp file to create cache file, to prevent file locking problems
        $_tmp_file = tempnam(misc::getTempDir(), config::getSetting('Site', 'identifier', $_SERVER['SERVER_NAME'])."_thumb");

        if(!is_resource($this->resource)) {
            log::trace(sprintf(_("\$this->resource is not resource %"),$this->resource));
            return false;
        }

        if( ! imagepng($this->resource, $_tmp_file)) {
            log::trace(sprintf(_("Could not save png thumbnail %s for %s"),$_tmp_file, $this->file));
            return false;
        }

        if (file_exists($thumb)) {
            log::trace(sprintf(_("Thumb file %s existed. Removing..."), $thumb));
            unlink($thumb);
        }

        if(rename($_tmp_file, $thumb)) {
            chmod($thumb, 0661);
            return true;
        } else {
            log::trace(_("Failed while renaming file, trying copy()"));
            if(copy($_tmp_file, $thumb)) {
                chmod($thumb, 0661);
                unlink($_tmp_file);
                return true;
            } else {
                log::trace(_("Could not eaven copy file. You're in shit!"));
            }
        }
        return false;
    }

    /**
     * @todo use mime interface
     */
    function _description($file) {
        $name = removeExt(basename($file));
        global $currentBob;
        if(!$currentBob->pseudoExists("@EXIF")) {
            log::trace(_("No @EXIF dir."));
            return false;
        }
        $descFile = dirname($this->file).DIRECTORY_SEPARATOR."@EXIF".DIRECTORY_SEPARATOR.$name.".txt";
        if (file_exists($descFile) || is_readable($descFile) ) {
            return misc::readFile($descFile);
        } else {
            log::trace(sprintf(_("No description file '%s' or not readable."), $descFile));
            return false;
        }
    }
}

class mime_handle_image_container {

    var $imguri;

    var $images = array();

    var $_pointer;

    function &singleton() {
        static $container;
        if(! isset($container)) {
            $container = new mime_handle_image_container();
        } elseif(!is_object($container->_pointer)) {
            end($this->images);
            $key = key($this->images);
            log::trace(sprintf(_("_pointer is not an object(?). Assigning to images['%s']"), $key));
            $this->_pointer =& $this->images[$key];
        }
        return $container;
    }

    function push($img) {
        $container =& mime_handle_image_container::singleton();
        $container->images[$img] = new mime_handle_image_container_entry($img);
        $container->_pointer =& $container->images[$img];
    }

    function file($name=null) {
        $container =& mime_handle_image_container::singleton();
        return $container->_pointer->file($name);
    }

    function size($height=null, $width=null) {
        $container =& mime_handle_image_container::singleton();
        $container->_pointer->height($height);
        $container->_pointer->width($width);
    }

    function url($url=null) {
        $container =& mime_handle_image_container::singleton();
        return $container->_pointer->url($url);
    }

    function imageUrl($url=null) {
        $container =& mime_handle_image_container::singleton();
        return $container->_pointer->imageUrl($url);
    }

    function viewUrl($url=null) {
        $container =& mime_handle_image_container::singleton();
        return $container->_pointer->viewUrl($url);
    }

    function name($name=null) {
        $container =& mime_handle_image_container::singleton();
        return $container->_pointer->name($name);
    }

    function description($desc=null) {
        $container =& mime_handle_image_container::singleton();
        return $container->_pointer->desc($desc);
    }


    function count() {
        $container =& mime_handle_image_container::singleton();
        return count($container->images);
    }

    /**
     * Seek next image entry.
     * Seeks next image entry in images array, and
     * repositions _pointer to that.
     * @param $key seek next from this key
     * @return string Image key name
     */
    function nextEntry($key=null) {
        $container =& mime_handle_image_container::singleton();
        if($key === null) $key = $container->_pointer->file();
        reset($container->images);
        while(current($container->images)!== false) {
            if(key($container->images) == $key) {
                next($container->images);
                if(!current($container->images)) {
                    log::trace(_("No next entry. Rewinding array..."));
                    reset($container->images);
                }
                $container->_pointer =& $container->images[key($container->images)];
                return key($container->images);
            }
            next($container->images);
        }
        return false;
    }
    /**
     * Seek previous image entry.
     * Seeks previous image entry in images array, and
     * repositions _pointer to that.
     * @param $key seek previous from this key
     * @return string Image key name
     */
    function prevEntry($key=null) {
        $container =& mime_handle_image_container::singleton();
        if($key === null) $key = $container->_pointer->file();
        reset($container->images);
        while(current($container->images)!== false) {
            if(key($container->images) == $key) {
                prev($container->images);
                if(!current($container->images)) {
                    log::trace(_("No prev entry. choosing last..."));
                    end($container->images);
                }
                $container->_pointer =& $container->images[key($container->images)];
                return key($container->images);
            }
            next($container->images);
        }
        return false;
    }

    function fetch($key=null) {
        $container =& mime_handle_image_container::singleton();
        if($key === null) $key = $container->_pointer->file();
        if(!isset($container->images[$key])) {
            log::trace(sprintf(_("Container does not contain key %s"), $key));
            return false;
        }
        return array(
            "file"   => $container->images[$key]->file(),
            "name"   => $container->images[$key]->name(),
            "url"    => $container->images[$key]->url(),
            "viewUrl"=> $container->images[$key]->viewUrl(),
            "imageUrl"=>$container->images[$key]->imageUrl(),
            "desc"   => $container->images[$key]->desc(),
            "height" => $container->images[$key]->height(),
            "width"  => $container->images[$key]->width()
        );
    }

    function fetchAll() {
        $container =& mime_handle_image_container::singleton();
        $r = array();
        $keys = array_keys($container->images);
        foreach($keys as $key) {
            $r[$key] = $container->fetch($key);
        }

        return $r;
    }
}

class mime_handle_image_container_entry {

    var $file;
    var $name;
    var $url;
    var $desc;
    var $height;
    var $width;
    var $viewUrl;
    var $imageUrl;

    function mime_handle_image_container_entry($file) {
        $this->file = $file;
    }

    function height($height=null) {
        if($height !== null ) $this->heigth = $height;
        if(!$this->height) $this->_calculateSize();
        return $this->height;
    }

    function width($width=null) {
        if($width !== null ) $this->width = $width;
        if(!$this->width) $this->_calculateSize();
        return $this->width;
    }

    function file($file=null) {
        if($file !== null ) $this->file = $file;
        return $this->file;
    }

    function name($name=null) {
        if($name !== null ) $this->name = $name;
        return $this->name;
    }

    function url($url=null) {
        if($url !== null ) $this->url = $url;
        return $this->url;
    }

    function viewUrl($url=null) {
        if($url !== null ) $this->viewUrl = $url;
        return $this->viewUrl;
    }

    function imageUrl($url=null) {
        if($url !== null ) $this->imageUrl = $url;
        return $this->imageUrl;
    }

    function desc($desc=null) {
        if($desc !== null ) $this->desc = $desc;
        return $this->desc;
    }

    function _calculateSize() {
        if(!($img = getimagesize($this->file()))) {
            log::trace(sprintf(_("Could not get image size for %s"),basename($this->file())));
            return false;
        }
        $this->height = $img[1];
        $this->width  = $img[0];
    }

}
