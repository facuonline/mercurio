<?php
/**
 * IMG class
 * @package Mercurio
 * @subpackage Included Classes
 *
 * Image Management and Generation
 * Since uploading and storing user submited files is insecure
 * Mercurio should never allow it, instead use this class to create new, jpeg compressed and safe files.
 * 
 * To avoid using insecure $_FILES array info 
 * this class only accepts an input name and searches for the tmp_ file in it.
 * To guarantee safety of the process
 * tmp image permissions are set to 0666 if possible (might not work on every server).
 * To avoid duplicate files and file name exploits every image is named after its sha1 hash.
 *
 * @var array $hash Array with new, sha1 based, filenames
 * @var int $mxflsz 
 *
 * @var string $file Input tmp_name file
 * @var string $path Stores path to save image
 */

namespace Mercurio\Utils;
class IMG {
	
	public $hash, $mxflsz;
	private $file, $path;

	/**
	 * @param int $max Maximum allowed input file size in bytes
	 * @param string $path Destination of final file
	 */
	public function __construct(int $max = 2097152) {
		$this->mxflsz = $max;
	}

	/**
	 * Determine if file is an image and prepare it
	 * @throws object Exception on error
	 */
	private function ext() {
		if (filesize($this->file) < $this->mxflsz) {
			if (getimagesize($this->file)) {
				$ext = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->file);
				if ($ext == 'image/jpeg') {
					return imagecreatefromjpeg($this->file);
				} elseif ($ext === 'image/jpg') {
					return imagecreatefromjpeg($this->file);
				} elseif ($ext === 'image/png') {
					return imagecreatefrompng($this->file);
				} elseif ($ext === 'image/gif') {
					return imagecreatefromgif($this->file);
				} elseif ($ext === 'image/webp') {
					return imagecreatefromwebp($this->file);
				} else {
					throw new Exception("Image file is not of a valid MIME type", 1);
				}
			} else {
				throw new Exception("Image file is not of a valid MIME type", 1);
			}
		} else {
			throw new Exception("Image file size is bigger than maximum expected", 1);
		}
	}

	/**
	 * Set up a canvas and draw the final image
	 * @param int $width Desired output width of the image
	 * @param int $height Desired output height of the image
	 * @param bool $crop Tells the method wether to crop or resize an image inside the new dimensions
	 * @param int|bool $thumbnail Tells the method wether to create or not a second image with an specified widht as integer and height and crop based on parent image */
	private function canvas($width, $height, $crop, $thumbnail = false){
		$src = $this->ext($this->file);
		$srcw = imagesx($src);
		$srch = imagesy($src);
		$img = imagecreatetruecolor($width, $height);
		imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255));
		imagealphablending($img, TRUE);
		// to properly crop an image we take the min value and use it as max in the final img
		if ($crop) {
			if ($srcw < $srch) {
				$srch = $srcw;
			} else {
				$srcw = $srch;
			}
        }
        $this->hash['sha'] = sha1_file($this->file).'.jpg';
		if ($thumbnail) {
			$this->hash['min'] = 'upload_min_'.sha1_file($this->file).'.jpg';
			$hash = $this->hash['min'];
		} else {
			$this->hash['max'] = 'upload_'.sha1_file($this->file).'.jpg';
			$hash = $this->hash['max'];
		}
		imagecopyresampled($img, $src, 0, 0, 0, 0, $width, $height, $srcw, $srch);
		imagedestroy($src);
		/**
		 * JPEG is chosen because it's more lightweight than other formats and because png allows for exploitation: https://www.idontplaydarts.com/2012/06/encoding-web-shells-in-png-idat-chunks/ */
		imagejpeg($img, $this->path.$hash, 95);
		imagedestroy($img);
	}

	/**
	 * Upload image to desired path and with desired dimensions
	 * @param string $file input file name
	 * @param string $path Desired path of destination
	 * @param int $width Desired output width of the image
	 * @param int|bool $ratio Tells the method wether to calc the output height based on the new width or use the defined height (will crop the image), if left to true will crop the image with an aspect ratio based height
	 * @param int|bool $thumbnail Tells the method wether to create or not a second image with an specified widht as integer and height and crop based on parent image */
	public function new($file, $path, $width, $ratio = false, $thumbnail = false){
		chmod($_FILES[$file]['tmp_name'], 0666);
		$this->file = $_FILES[$file]['tmp_name'];
		$src = $this->ext();
		$this->path = $path;
		$hash = sha1_file($this->file).'.jpg';
		$srcw = imagesx($src);
		$srch = imagesy($src);
		if (!$ratio) {
			$height = $width*$srch/$srcw;
			$crop = false;
		} elseif (ctype_digit($ratio)) {
			$height = $ratio;
			$crop = true;
		} else {
			$height = $width*$srch/$srcw;
			$crop = true;
		}
		$this->canvas($width, $height, $crop);
		// make thumbnail
		if ($thumbnail) {
			if (!$ratio) {
				$height = $thumbnail*$srch/$srcw;
				$crop = false;
			} elseif (ctype_digit($ratio)) {
				$ratio = $thumbnail*$srcw/$srch;
				$height = $ratio*$srch/$srcw;
				$crop = true;
			} else {
				$height = $thumbnail*$srch/$srcw;
				$crop = true;
			}
			$this->canvas($thumbnail, $height, $crop, true);
		}
    }
}