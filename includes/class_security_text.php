<?php
/**
* Class SecurityImageText
* Generates a security image used for form submissons
*/
class Captcha_Image {
    /**
    * Registry object
    * @var Registry
    */
    var $PMDR;
    /**
    * Image
    * @var resource
    */
    var $image;
    /**
    * Image height
    * @var int
    */
    var $iHeight;
    /**
    * Image width
    * @var int
    */
    var $iWidth;
    /**
    * Font height
    * @var int
    */
    var $fHeight = 9;
    /**
    * Font width
    * @var int
    */
    var $fWidth = 15;
    /**
    * Starting x position
    * @var int
    */
    var $xPos = 0;
    /**
    * Fonts
    * @var array
    */
    var $fonts = array(2,4);

    /**
    * SecurityImageText Constructor
    * @param image $jpeg Image for background
    * @param integer $fHeight Font height
    * @param integer $fwidth Font width
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->image = ImageCreateFromJPEG($this->PMDR->get('Templates')->path('images/security.jpg'));
        $this->iHeight = ImageSY($this->image);
        $this->iWidth = ImageSX($this->image);
    }

    /**
    * Add text to image
    * @param string $text Text to add to image
    * @param integer $r Red color value
    * @param integer $g Green color value
    * @param integer $b Blue color value
    * @return boolean True if text added, false if failed
    */
    function addText($text,$r=38,$g=38,$b=38) {
        $length = $this->fWidth * strlen($text);

        if($length >= ($this->iWidth-($this->fWidth*2))) {
            return false;
        }

        $this->xPos = floor(($this->iWidth - $length) / 2);

        $fColor = ImageColorAllocate($this->image,$r,$g,$b);

        srand((float)microtime()*1000000);
        $fonts=array(2,4);

         if(!function_exists('imagettftext')) {
        	$yStart=floor ( $this->iHeight / 2 ) - $this->fHeight;
        	$yEnd=$yStart + $this->fHeight;
        	$yPos=range($yStart,$yEnd);
         } else {
	        $yPos[0] = 25;
            $yPos[1] = 30;
            $yPos[2] = 35;
            $yPos[3] = 32;
         }

        $slant[0] = 1;
        $slant[1] = 10;
        $slant[2] = 20;
        $slant[3] = -10;

        for($strPos=0;$strPos < $length; $strPos++ ) {
            shuffle($fonts);
            shuffle($yPos);
            shuffle($slant);

            if(function_exists('imagettftext')) {
                Imagettftext ($this->image, 20, $slant[0], $this->xPos, $yPos[0],$fColor, "../files/fonts/font.ttf",substr($text,$strPos,1) )or die ("error");
            } else {
                ImageString($this->image,
                            $fonts[0],
                            $this->xPos,
                            $yPos[0],
                            substr($text,$strPos,1),
                            $fColor);
            }
            $this->xPos+=$this->fWidth;
        }
        return true;
    }

    /**
    * Get Image
    * @return iamge
    */
    function getImage () {
        return $this->image;
    }

    /**
    * Get the HTML img tag with the src set
    * @return string Image tag
    */
    function getHTML() {
        return '<img src="'.BASE_URL.'/includes/security_text.php">';
    }
}
?>