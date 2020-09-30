<?php
/**
* Image Handler
*
* Uses adapted code from the PHP Thumb Library <http://phpthumb.gxdlabs.com>
* Copyright (c) 2009, Ian Selby/Gen X Design
*/
class Image_Handler {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;
    /**
    * File path to the image
    * @var string
    */
    var $file_path = null;
    /**
    * File name
    * @var string
    */
    var $file_name = null;
    /**
    * File format (jpg, gif, etc)
    * @var string
    */
    var $file_format = null;
    /**
    * File size
    * @var int
    */
    var $file_size = null;
    /**
     * The prior image (before manipulation)
     *
     * @var resource
     */
    protected $image;
    /**
     * The working image (used during manipulation)
     *
     * @var resource
     */
    protected $image_working;
    /**
     * The current dimensions of the image
     *
     * @var array
     */
    protected $current_dimensions;
    /**
     * The new, calculated dimensions of the image
     *
     * @var array
     */
    protected $new_dimensions;
    /**
     * The options for this class
     *
     * This array contains various options that determine the behavior in
     * various functions throughout the class.  Functions note which specific
     * option key / values are used in their documentation
     *
     * @var array
     */
    protected $options;
    /**
     * The maximum width an image can be after resizing (in pixels)
     *
     * @var int
     */
    protected $max_width;
    /**
     * The maximum height an image can be after resizing (in pixels)
     *
     * @var int
     */
    protected $max_height;
    /**
     * The percentage to resize the image by
     *
     * @var int
     */
    protected $percent;
    /**
    * Copy the file rather than resize
    * Used for animated GIfs or for optimization of file sizes
    */
    protected $copy_only;
    /**
    * If image is animated or not
    */
    public $animated = false;
    

    /**
    * Image Handler Contructor
    * @param object $PMDR
    * @return void
    */
    function __construct($PMDR, $file = null, $options = array()) {
        $this->PMDR = $PMDR;

        if(!extension_loaded('gd')) {
            throw new Exception('GD extension not loaded.');
        }
        if(!is_null($file)) {
            $this->loadImage($file);
        }
        $this->setOptions($options);
    }

    /**
    * Shortcut function to quickly process an image
    * @param mixed $file
    * @param string $to
    * @param array $options
    */
    public function process($file, $to, $options = array()) {
        $this->resetOptions();
        try {
            $this->loadImage($file);
            $this->setOptions($options);
            $this->save($to);
        } catch (Exception $e) {
            return false;
        }
        return $this->file_format;
    }

    /**
    * Verify an image according to a set of criteria
    * @param mixed $file Either the $_FILES image location, URL, or file path to an image
    * @return boolean
    */
    public function verifyImage($file) {
        if(is_array($file) AND isset($file['tmp_name']) AND is_uploaded_file($file['tmp_name']) AND $file['size'] > 0) {
            $this->file_path = $file['tmp_name'];
            $this->file_name = $file['name'];
            $this->file_size = filesize($this->file_path);
        } elseif(valid_url($file)) {
            $this->file_path = $file;
            $this->file_name = basename(parse_url($file,PHP_URL_PATH));
            $ch = curl_init($this->file_path);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_exec($ch);
            $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $this->file_size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
            // In case of a URL redirect, we get the last URL or else other image function like getimagesize will not work
            $this->file_path = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);
            if($return_code != 200) {
                trigger_error('Image URL does not reutrn a 200 status code: '.$this->file_path, E_USER_NOTICE);
                return false;
            }
        } elseif(is_string($file) AND file_exists($file)) {
            $this->file_path = $file;
            $this->file_name = basename($file);
            $this->file_size = filesize($this->file_path);
            if(!is_readable($this->file_path)) {
                trigger_error('File is not readable: '.$this->file_path, E_USER_NOTICE);
                return false;
            }
        } else {
            trigger_error('File not a valid file or URL: '.$this->file_path, E_USER_NOTICE);
            return false;
        }

        $format_info = $this->get_image_size($this->file_path);

        if($format_info === false) {
            trigger_error('File is not a valid image: '.$this->file_path, E_USER_NOTICE);
            return false;
        }

        $mime_type = isset($format_info['mime']) ? $format_info['mime'] : null;

        switch($mime_type) {
            case 'image/gif':
                $this->file_format = 'gif';
                $this->animated = $this->is_animated_gif($this->file_path);
                break;
            case 'image/jpeg':
                $this->file_format = 'jpg';
                break;
            case 'image/png':
                $this->file_format = 'png';
                break;
            case 'application/x-shockwave-flash':
                $this->file_format = 'swf';
                break;
            default:
                trigger_error('Image format not supported: '. $mime_type, E_USER_NOTICE);
                return false;
        }

        $gd_info = gd_info();

        switch($this->file_format) {
            case 'gif':
                if(!$gd_info['GIF Create Support']) {
                    trigger_error('GIF support not available.', E_USER_NOTICE);
                    return false;
                }
                break;
            case 'jpg':
                if(!$gd_info['JPG Support'] AND !$gd_info['JPEG Support']) {
                    trigger_error('JPG support not available.', E_USER_NOTICE);
                    return false;
                }
            case 'png':
               if(!$gd_info['PNG Support']) {
                    trigger_error('PNG support not available.', E_USER_NOTICE);
                    return false;
                }
                break;
            case 'swf':
                break;
            default:
                trigger_error('Invalid format support: '.$this->file_format, E_USER_NOTICE);
                return false;
                break;
        }
        $this->current_dimensions = array('width'=>$format_info[0],'height'=>$format_info[1]);
        return true;
    }

    /**
    * Load the image file
    * @param mixed $file
    */
    public function loadImage($file) {
        if(!$this->verifyImage($file)) {
            throw new Exception('Unable to verify image');
        }
        
        if( $this->animated OR 
            $this->file_format == 'swf' OR 
            (
                !empty($this->options) AND 
                $this->options['width'] == $this->current_dimensions['width'] AND 
                $this->options['height'] == $this->current_dimensions['height']
            )) {
            $this->copy_only = true;
        }

        if(!$this->copy_only) {
            switch($this->file_format) {
                case 'gif':
                    $this->image = imagecreatefromgif($this->file_path);
                    break;
                case 'jpg':
                    $this->image = imagecreatefromjpeg($this->file_path);
                    break;
                case 'png':
                    $this->image = imagecreatefrompng($this->file_path);
                    break;
            }
            if(!$this->image) {
                throw new Exception('Unable to create image');
            }
        }
        return true;
    }

    /**
    * Reset the options
    */
    public function resetOptions() {
        $this->options = array();
        $this->copy_only = false;
    }

    /**
    * Set the options
    * @param array $options
    */
    public function setOptions($options = array()) {
        if(!is_array($this->options)) {
            $this->options = array();
        }

        if(!is_array($options)) {
            throw new Exception('setOptions requires an array');
        }

        if(is_array($this->options) AND sizeof($this->options) == 0) {
            $defaultOptions = array(
                'enlarge'=>false,
                'quality'=>100,
                'correctPermissions'=>false,
                'preserveAlpha'=>true,
                'alphaMaskColor'=>array(255,255,255),
                'preserveTransparency'=>true,
                'transparencyMaskColor'=>array(0,0,0),
                'width'=>0,
                'height'=>0,
                'remove_old'=>false,
                'watermark'=>false,
            );
        } else {
            $defaultOptions = $this->options;
        }

        $this->options = array_merge($defaultOptions, $options);
    }

    /**
    * Save the image to a path using the same type/extension as the original
    * @param string $path
    * @param string $format
    */
    public function saveToPath($path, $format = null) {
        $this->save(rtrim($path,'/').'/'.$this->file_name,$format);
    }

    /**
    * Save the image to a specific file path with optional format
    * @param string $file_name
    * @param mixed $format
    * @return Image_Handler
    */
    public function save($file_name, $format = null) {
        $file_name = str_replace('*',$this->file_format,$file_name);
        $validFormats = array('gif','jpg','png');
        $format = ($format !== null) ? strtolower($format) : $this->file_format;
        if(!in_array($format, $validFormats)) {
            throw new Exception('Invalid format type specified in save function: '.$format);
        }

        if(!is_writeable(dirname($file_name))) {
            if($this->options['correctPermissions'] === true) {
                @chmod(dirname($file_name), 0777);
                if(!is_writeable(dirname($file_name))) {
                    throw new Exception('File is not writeable, and could not correct permissions: '.$file_name);
                }
            } else {
                throw new Exception('File not writeable: '.$file_name);
            }
        }

        if($this->options['remove_old']) {
            @unlink(find_file(str_replace('.'.pathinfo($file_name,PATHINFO_EXTENSION),'.*',$file_name)));
        }

        if($this->copy_only) {
            if(is_uploaded_file($this->file_path)) {
                return move_uploaded_file($this->file_path,$file_name);
            } else {
                copy($this->file_path, $file_name);
                chmod($file_name,0755);
                return true;
            }
        } else {
            if($this->options['crop'] AND $this->options['height'] > 0 AND $this->options['width'] > 0) {
                $this->adaptiveResize($this->options['width'], $this->options['height']);
            } else {
                $this->resize($this->options['width'], $this->options['height']);
            }
            // Add setting for watermark
            if($this->options['watermark'] AND $this->PMDR->get('Templates')->path('images/watermark.png')) {
                $this->createWatermark($this->PMDR->get('Templates')->path('images/watermark.png'));
            }
            switch($format) {
                case 'gif':
                    imagegif($this->image, $file_name);
                    break;
                case 'jpg':
                    imagejpeg($this->image, $file_name, $this->options['quality']);
                    break;
                case 'png':
                    imagepng($this->image, $file_name);
                    break;
            }
        }

        return $this;
    }

    /**
     * Shows an image
     *
     * This function will show the current image by first sending the appropriate header
     * for the format, and then outputting the image data. If headers have already been sent,
     * a runtime exception will be thrown
     *
     * @return GdThumb
     */
    public function show() {
        if(headers_sent()) {
            throw new Exception('Cannot show image, headers have already been sent');
        }

        switch($this->file_format) {
            case 'gif':
                header('Content-type: image/gif');
                imagegif($this->image);
                break;
            case 'jpg':
                header('Content-type: image/jpeg');
                imagejpeg($this->image, null, $this->options['quality']);
                break;
            case 'png':
                header('Content-type: image/png');
                imagepng($this->image);
                break;
        }

        return $this;
    }

    /**
    * Check if a GIF is animated
    * @param string $img
    * @return bool
    */
    public function is_animated_gif($img) {
        if(empty($img)) return false;

        $contents = file_get_contents($img);
        $location = 0;
        $count = 0;

        if((substr($contents, 0, 6) != 'GIF89a') AND (substr($contents, 0, 6) != 'GIF87a')) {
            return false;
        }

        while ($count < 2) {
            $first_occurance = strpos($contents,"\x00\x21\xF9\x04",$location);
            if ($first_occurance === FALSE) {
                break;
            } else {
                $location = $first_occurance+1;
                $second_occurance = strpos($contents,"\x00\x2C",$location);
                if ($second_occurance === FALSE) {
                    break;
                } else {
                    if ($first_occurance+8 == $second_occurance) {
                        $count++;
                    }
                    $location = $second_occurance+1;
                }
            }
        }
        return ($count > 1) ? true : false;
    }

    /**
    * Get the current image width
    * @return int Width
    */
    public function getCurrentWidth() {
        return $this->current_dimensions['width'];
    }

    /**
    * Get the current image height
    * @return int Image height
    */
    public function getCurrentHeight() {
        return $this->current_dimensions['height'];
    }

    /**
    * Create a watermark on the image
    * @param string $mask_file
    * @return Image_Handler
    */
    public function createWatermark($mask_file) {
        $stamp_image = NULL;
        $marge_right = 10;
        $marge_bottom = 10;

        list($sx, $sy, $stamp_type) = getimagesize($mask_file);
        switch($stamp_type) {
            case 1:
                $stamp_image = imagecreatefromgif($mask_file);
                imagecopy($this->image, $stamp_image, imagesx($this->image) - $sx - $marge_right, imagesy($this->image) - $sy - $marge_bottom, 0, 0, imagesx($stamp_image), imagesy($stamp_image));
                break;
            case 2:
                $stamp_image = imagecreatefromjpeg($mask_file);
                imagecopymerge($this->image, $stamp_image, imagesx($this->image) - $sx - $marge_right, imagesy($this->image) - $sy - $marge_bottom, 0, 0, imagesx($stamp_image), imagesy($stamp_image), 60);
                break;
            case 3:
                $stamp_image = imagecreatefrompng($mask_file);
                imagecopy($this->image, $stamp_image, imagesx($this->image) - $sx - $marge_right, imagesy($this->image) - $sy - $marge_bottom, 0, 0, imagesx($stamp_image), imagesy($stamp_image));
                break;
        }
        return $this;
    }

    /**
     * Resizes an image to be no larger than $maxWidth or $maxHeight
     *
     * If either param is set to zero, then that dimension will not be considered as a part of the resize.
     * Additionally, if $this->options['resizeUp'] is set to true (false by default), then this function will
     * also scale the image up to the maximum dimensions provided.
     *
     * BEN: We modified this function due to resizing problems:
     * https://github.com/masterexploder/PHPThumb/issues/43
     *
     * @param int $maxWidth The maximum width of the image in pixels
     * @param int $maxHeight The maximum height of the image in pixels
     * @param bool $calcImageSizeStrict Used internally when this function is called by adaptiveResize().
     * @return GdThumb
     */
    public function resize($maxWidth = 0, $maxHeight = 0, $calcImageSizeStrict = false) {
        // make sure our arguments are valid
        if(!is_numeric($maxWidth)) {
            throw new Exception('$maxWidth must be numeric');
        }

        if(!is_numeric($maxHeight)) {
            throw new Exception('$maxHeight must be numeric');
        }

        // make sure we're not exceeding our image size if we're not supposed to
        // Changed to accept 1 or 0 - ben
        if (!$this->options['enlarge']) {
            $this->max_height = (intval($maxHeight) > $this->current_dimensions['height']) ? $this->current_dimensions['height'] : $maxHeight;
            $this->max_width = (intval($maxWidth) > $this->current_dimensions['width']) ? $this->current_dimensions['width'] : $maxWidth;
        } else {
            $this->max_height = intval($maxHeight);
            $this->max_width = intval($maxWidth);
        }

        // get the new dimensions...
        if($calcImageSizeStrict === true) {
            $this->calcImageSizeStrict($this->current_dimensions['width'], $this->current_dimensions['height']);
        } else {
            $this->calcImageSize($this->current_dimensions['width'], $this->current_dimensions['height']);
        }

        // create the working image
        if(function_exists('imagecreatetruecolor')) {
            $this->image_working = imagecreatetruecolor($this->new_dimensions['newWidth'], $this->new_dimensions['newHeight']);
        } else {
            $this->image_working = imagecreate($this->new_dimensions['newWidth'], $this->new_dimensions['newHeight']);
        }

        $this->preserveAlpha();

        // and create the newly sized image
        imagecopyresampled
        (
            $this->image_working,
            $this->image,
            0,
            0,
            0,
            0,
            $this->new_dimensions['newWidth'],
            $this->new_dimensions['newHeight'],
            $this->current_dimensions['width'],
            $this->current_dimensions['height']
        );

        // update all the variables and resources to be correct
        $this->image                     = $this->image_working;
        $this->current_dimensions['width']     = $this->new_dimensions['newWidth'];
        $this->current_dimensions['height']     = $this->new_dimensions['newHeight'];

        return $this;
    }

    /**
     * Adaptively Resizes the Image
     *
     * This function attempts to get the image to as close to the provided dimensions as possible, and then crops the
     * remaining overflow (from the center) to get the image to be the size specified
     *
     * @param int $maxWidth
     * @param int $maxHeight
     * @return GdThumb
     */
    public function adaptiveResize($width, $height) {
        // make sure our arguments are valid
        if (!is_numeric($width) || $width  == 0) {
            throw new Exception('$width must be numeric and greater than zero');
        }

        if(!is_numeric($height) || $height == 0) {
            throw new Exception('$height must be numeric and greater than zero');
        }

        // make sure we're not exceeding our image size if we're not supposed to
        // -ben
        if (!$this->options['enlarge']) {
            $this->max_height = (intval($height) > $this->current_dimensions['height']) ? $this->current_dimensions['height'] : $height;
            $this->max_width = (intval($width) > $this->current_dimensions['width']) ? $this->current_dimensions['width'] : $width;
        } else {
            $this->max_height = intval($height);
            $this->max_width = intval($width);
        }

        $this->calcImageSizeStrict($this->current_dimensions['width'], $this->current_dimensions['height']);

        // resize the image to be close to our desired dimensions
        $this->resize($this->new_dimensions['newWidth'], $this->new_dimensions['newHeight'],true);

        // reset the max dimensions...
        // -ben
        if(!$this->options['enlarge']) {
            $this->max_height = (intval($height) > $this->current_dimensions['height']) ? $this->current_dimensions['height'] : $height;
            $this->max_width = (intval($width) > $this->current_dimensions['width']) ? $this->current_dimensions['width'] : $width;
        } else {
            $this->max_height = intval($height);
            $this->max_width  = intval($width);
        }

        // create the working image
        if(function_exists('imagecreatetruecolor')) {
            $this->image_working = imagecreatetruecolor($this->max_width, $this->max_height);
        } else {
            $this->image_working = imagecreate($this->max_width, $this->max_height);
        }

        $this->preserveAlpha();

        $cropWidth = $this->max_width;
        $cropHeight = $this->max_height;
        $cropX = 0;
        $cropY = 0;

        // now, figure out how to crop the rest of the image...
        if ($this->current_dimensions['width'] > $this->max_width) {
            $cropX = intval(($this->current_dimensions['width'] - $this->max_width) / 2);
        } elseif ($this->current_dimensions['height'] > $this->max_height) {
            $cropY = intval(($this->current_dimensions['height'] - $this->max_height) / 2);
        }

        imagecopyresampled
        (
            $this->image_working,
            $this->image,
            0,
            0,
            $cropX,
            $cropY,
            $cropWidth,
            $cropHeight,
            $cropWidth,
            $cropHeight
        );

        // update all the variables and resources to be correct
        $this->image                     = $this->image_working;
        $this->current_dimensions['width']     = $this->max_width;
        $this->current_dimensions['height']     = $this->max_height;

        return $this;
    }

    /**
     * Resizes an image by a given percent uniformly
     *
     * Percentage should be whole number representation (i.e. 1-100)
     *
     * @param int $percent
     * @return GdThumb
     */
    public function resizePercent($percent = 0) {
        if(!is_numeric($percent)) {
            throw new Exception ('$percent must be numeric');
        }

        $this->percent = intval($percent);

        $this->calcImageSizePercent($this->current_dimensions['width'], $this->current_dimensions['height']);

        if(function_exists('imagecreatetruecolor')) {
            $this->image_working = imagecreatetruecolor($this->new_dimensions['newWidth'], $this->new_dimensions['newHeight']);
        } else {
            $this->image_working = imagecreate($this->new_dimensions['newWidth'], $this->new_dimensions['newHeight']);
        }

        $this->preserveAlpha();

        ImageCopyResampled(
            $this->image_working,
            $this->image,
            0,
            0,
            0,
            0,
            $this->new_dimensions['newWidth'],
            $this->new_dimensions['newHeight'],
            $this->current_dimensions['width'],
            $this->current_dimensions['height']
        );

        $this->image = $this->image_working;
        $this->current_dimensions['width'] = $this->new_dimensions['newWidth'];
        $this->current_dimensions['height'] = $this->new_dimensions['newHeight'];

        return $this;
    }

    /**
     * Crops an image from the center with provided dimensions
     *
     * If no height is given, the width will be used as a height, thus creating a square crop
     *
     * @param int $cropWidth
     * @param int $cropHeight
     * @return GdThumb
     */
    public function cropFromCenter($cropWidth, $cropHeight = null) {
        if (!is_numeric($cropWidth)) {
            throw new Exception('$cropWidth must be numeric');
        }

        if ($cropHeight !== null && !is_numeric($cropHeight)) {
            throw new Exception('$cropHeight must be numeric');
        }

        if ($cropHeight === null) {
            $cropHeight = $cropWidth;
        }

        $cropWidth = ($this->current_dimensions['width'] < $cropWidth) ? $this->current_dimensions['width'] : $cropWidth;
        $cropHeight = ($this->current_dimensions['height'] < $cropHeight) ? $this->current_dimensions['height'] : $cropHeight;

        $cropX = intval(($this->current_dimensions['width'] - $cropWidth) / 2);
        $cropY = intval(($this->current_dimensions['height'] - $cropHeight) / 2);

        $this->crop($cropX, $cropY, $cropWidth, $cropHeight);

        return $this;
    }

    /**
     * Vanilla Cropping - Crops from x,y with specified width and height
     *
     * @param int $startX
     * @param int $startY
     * @param int $cropWidth
     * @param int $cropHeight
     * @return GdThumb
     */
    public function crop($startX, $startY, $cropWidth, $cropHeight) {
        // validate input
        if(!is_numeric($startX)) {
            throw new Exception('$startX must be numeric');
        }

        if(!is_numeric($startY)) {
            throw new Exception('$startY must be numeric');
        }

        if(!is_numeric($cropWidth)) {
            throw new Exception('$cropWidth must be numeric');
        }

        if(!is_numeric($cropHeight)) {
            throw new Exception('$cropHeight must be numeric');
        }

        // do some calculations
        $cropWidth = ($this->current_dimensions['width'] < $cropWidth) ? $this->current_dimensions['width'] : $cropWidth;
        $cropHeight = ($this->current_dimensions['height'] < $cropHeight) ? $this->current_dimensions['height'] : $cropHeight;

        // ensure everything's in bounds
        if (($startX + $cropWidth) > $this->current_dimensions['width']) {
            $startX = ($this->current_dimensions['width'] - $cropWidth);
        }

        if (($startY + $cropHeight) > $this->current_dimensions['height']) {
            $startY = ($this->current_dimensions['height'] - $cropHeight);
        }

        if ($startX < 0) {
            $startX = 0;
        }

        if ($startY < 0) {
            $startY = 0;
        }

        // create the working image
        if (function_exists('imagecreatetruecolor')) {
            $this->image_working = imagecreatetruecolor($cropWidth, $cropHeight);
        } else {
            $this->image_working = imagecreate($cropWidth, $cropHeight);
        }

        $this->preserveAlpha();

        imagecopyresampled
        (
            $this->image_working,
            $this->image,
            0,
            0,
            $startX,
            $startY,
            $cropWidth,
            $cropHeight,
            $cropWidth,
            $cropHeight
        );

        $this->image = $this->image_working;
        $this->current_dimensions['width'] = $cropWidth;
        $this->current_dimensions['height'] = $cropHeight;

        return $this;
    }

    /**
     * Rotates image either 90 degrees clockwise or counter-clockwise
     *
     * @param string $direction
     * @retunrn GdThumb
     */
    public function rotateImage($direction = 'CW') {
        if($direction == 'CW') {
            $this->rotateImageNDegrees(90);
        } else {
            $this->rotateImageNDegrees(-90);
        }
        return $this;
    }

    /**
     * Rotates image specified number of degrees
     *
     * @param int $degrees
     * @return GdThumb
     */
    public function rotateImageNDegrees($degrees) {
        if(!is_numeric($degrees)) {
            throw new Exception('$degrees must be numeric');
        }

        if(!function_exists('imagerotate')) {
            throw new Exception('Your version of GD does not support image rotation.');
        }

        $this->image_working = imagerotate($this->image, $degrees, 0);

        $newWidth = $this->current_dimensions['height'];
        $newHeight = $this->current_dimensions['width'];
        $this->image = $this->image_working;
        $this->current_dimensions['width'] = $newWidth;
        $this->current_dimensions['height'] = $newHeight;

        return $this;
    }

    /**
     * Preserves the alpha or transparency for PNG and GIF files
     *
     * Alpha / transparency will not be preserved if the appropriate options are set to false.
     * Also, the GIF transparency is pretty skunky (the results aren't awesome), but it works like a
     * champ... that's the nature of GIFs tho, so no huge surprise.
     *
     * This functionality was originally suggested by commenter Aimi (no links / site provided) - Thanks! :)
     *
     */
    protected function preserveAlpha() {
        if($this->file_format == 'png' && $this->options['preserveAlpha'] === true) {
            imagealphablending($this->image_working, false);

            $colorTransparent = imagecolorallocatealpha
            (
                $this->image_working,
                $this->options['alphaMaskColor'][0],
                $this->options['alphaMaskColor'][1],
                $this->options['alphaMaskColor'][2],
                0
            );

            imagefill($this->image_working, 0, 0, $colorTransparent);
            imagesavealpha($this->image_working, true);
        }
        if($this->file_format == 'gif' && $this->options['preserveTransparency'] === true) {
            $colorTransparent = imagecolorallocate
            (
                $this->image_working,
                $this->options['transparencyMaskColor'][0],
                $this->options['transparencyMaskColor'][1],
                $this->options['transparencyMaskColor'][2]
            );

            imagecolortransparent($this->image_working, $colorTransparent);
            imagetruecolortopalette($this->image_working, true, 256);
        }
    }

    /**
     * Calculates a new width and height for the image based on $this->max_width and the provided dimensions
     *
     * @return array
     * @param int $width
     * @param int $height
     */
    protected function calcWidth($width, $height) {
        $newWidthPercentage = (100 * $this->max_width) / $width;
        $newHeight = ($height * $newWidthPercentage) / 100;

        return array
        (
            'newWidth'=>intval($this->max_width),
            'newHeight'=>intval($newHeight)
        );
    }

    /**
     * Calculates a new width and height for the image based on $this->max_width and the provided dimensions
     *
     * @return array
     * @param int $width
     * @param int $height
     */
    protected function calcHeight($width, $height) {
        $newHeightPercentage = (100 * $this->max_height) / $height;
        $newWidth = ($width * $newHeightPercentage) / 100;

        return array
        (
            'newWidth'=>ceil($newWidth),
            'newHeight'=>ceil($this->max_height)
        );
    }

    /**
     * Calculates a new width and height for the image based on $this->percent and the provided dimensions
     *
     * @return array
     * @param int $width
     * @param int $height
     */
    protected function calcPercent($width, $height) {
        $newWidth = ($width * $this->percent) / 100;
        $newHeight = ($height * $this->percent) / 100;

        return array(
            'newWidth'=>ceil($newWidth),
            'newHeight'=>ceil($newHeight)
        );
    }

    /**
     * Calculates the new image dimensions
     *
     * These calculations are based on both the provided dimensions and $this->max_width and $this->max_height
     *
     * @param int $width
     * @param int $height
     */
    protected function calcImageSize($width, $height) {
        $newSize = array('newWidth'=>$width,'newHeight'=>$height );
        if($this->max_width > 0) {
            $newSize = $this->calcWidth($width, $height);
            if ($this->max_height > 0 && $newSize['newHeight'] > $this->max_height) {
                $newSize = $this->calcHeight($newSize['newWidth'], $newSize['newHeight']);
            }
        }
        if($this->max_height > 0) {
            $newSize = $this->calcHeight($width, $height);
            if ($this->max_width > 0 && $newSize['newWidth'] > $this->max_width) {
                $newSize = $this->calcWidth($newSize['newWidth'], $newSize['newHeight']);
            }
        }
        $this->new_dimensions = $newSize;
    }

    /**
     * Calculates new image dimensions, not allowing the width and height to be less than either the max width or height
     *
     * @param int $width
     * @param int $height
     */
    protected function calcImageSizeStrict($width, $height) {
        // first, we need to determine what the longest resize dimension is..
        if($this->max_width >= $this->max_height) {
            // and determine the longest original dimension
            if($width > $height) {
                $new_dimensions = $this->calcHeight($width, $height);
                if($new_dimensions['newWidth'] < $this->max_width) {
                    $new_dimensions = $this->calcWidth($width, $height);
                }
            } elseif($height >= $width) {
                $new_dimensions = $this->calcWidth($width, $height);
                if ($new_dimensions['newHeight'] < $this->max_height) {
                    $new_dimensions = $this->calcHeight($width, $height);
                }
            }
        } elseif($this->max_height > $this->max_width) {
            if($width >= $height) {
                $new_dimensions = $this->calcWidth($width, $height);
                if ($new_dimensions['newHeight'] < $this->max_height) {
                    $new_dimensions = $this->calcHeight($width, $height);
                }
            } elseif($height > $width) {
                $new_dimensions = $this->calcHeight($width, $height);
                if ($new_dimensions['newWidth'] < $this->max_width) {
                    $new_dimensions = $this->calcWidth($width, $height);
                }
            }
        }

        $this->new_dimensions = $new_dimensions;
    }

    /**
     * Calculates new dimensions based on $this->percent and the provided dimensions
     *
     * @param int $width
     * @param int $height
     */
    protected function calcImageSizePercent($width, $height) {
        if($this->percent > 0) {
            $this->new_dimensions = $this->calcPercent($width, $height);
        }
    }

    /**
     * Flips the image vertically
     */
    protected function imageFlipVertical() {
        $x_i = imagesx($this->image_working);
        $y_i = imagesy($this->image_working);

        for ($x = 0; $x < $x_i; $x++) {
            for ($y = 0; $y < $y_i; $y++) {
                imagecopy($this->image_working, $this->image_working, $x, $y_i - $y - 1, $x, $y, 1, 1);
            }
        }
    }

    /**
     * Converts a hex color to rgb tuples
     *
     * @return mixed
     * @param string $hex
     * @param bool $asString
     */
    protected function hex2rgb($hex, $asString = false) {
        // strip off any leading #
        if(0 === strpos($hex, '#')) {
           $hex = substr($hex, 1);
        } elseif (0 === strpos($hex, '&H')) {
           $hex = substr($hex, 2);
        }

        // break into hex 3-tuple
        $cutpoint = ceil(strlen($hex) / 2)-1;
        $rgb = explode(':', wordwrap($hex, $cutpoint, ':', $cutpoint), 3);

        // convert each tuple to decimal
        $rgb[0] = (isset($rgb[0]) ? hexdec($rgb[0]) : 0);
        $rgb[1] = (isset($rgb[1]) ? hexdec($rgb[1]) : 0);
        $rgb[2] = (isset($rgb[2]) ? hexdec($rgb[2]) : 0);

        return ($asString ? "{$rgb[0]} {$rgb[1]} {$rgb[2]}" : $rgb);
    }

    /**
    * Get an images size and other details
    * @param string $image Image
    * @return array Image details
    */
    protected function get_image_size($image) {
        if($image = getimagesize($image)) {
            $image[2] = image_type_to_extension($image[2],false);
            if($image[2] == 'jpeg') {
                $image[2] = 'jpg';
            }
            return $image;
        } else {
            return false;
        }
    }

    /**
    * Destroy the image resources
    */
    public function __destruct() {
        if(is_resource($this->image)) {
            imagedestroy($this->image);
        }
        if(is_resource($this->image_working)) {
            imagedestroy($this->image_working);
        }
    }
}
?>