<?php

namespace Mercurio\Utils;

/**
 * Image management and generation
 * @package Mercurio
 * @subpackage Utilitary Classes
 *
 * Since uploading and storing user submited files is insecure
 * Mercurio should never allow it, instead use this class to create new, jpeg compressed and safe files.
 * Alternatively you can use included samayo/bulletproof package.
 * 
 * To avoid using insecure $_FILES array info 
 * this class only accepts an input name and searches for the tmp_ file in it.
 * To guarantee safety of the process
 * tmp image permissions are set to 0666 if possible (might not work on every server).
 * To avoid duplicate files and file name exploits every image is named after its sha1 hash.
 *
 * @param string $hash Hash file name
 * @param int $maxfilesize Maximum file size in bytes
 * @param string $file
 * @param string $path
 * 
 * @see https://github.com/samayo/bulletproof
 *
 */
class Image {
	
	/**
	 * Final file hash name
	 */
	public $hash;

	/**
	 * Maximum file allowed size in bytes
	 */
	public $maxfilesize;

	/**
	 * Image resource created with source()
	 */
	public $src;

	private $file, $path;

	/**
	 * @param int $max Maximum allowed input file size in bytes
	 */
	public function __construct(int $max = 2097152) {
		$this->maxfilesize = $max;
	}

	/**
	 * Determine if file is an image and prepare it
	 * @return resource Generated copy of input file
	 * @throws object Exception on error
	 */
	private function source() {
		if (filesize($this->file) < $this->maxfilesize) {
			if (getimagesize($this->file)) {
				$ext = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->file);
				if ($ext === 'image/jpeg' || $ext === 'image/jpg') {
					return imagecreatefromjpeg($this->file);
				} elseif ($ext === 'image/png') {
					return imagecreatefrompng($this->file);
				} elseif ($ext === 'image/gif') {
					return imagecreatefromgif($this->file);
				} elseif ($ext === 'image/webp') {
					return imagecreatefromwebp($this->file);
				} else {
					throw new \Mercurio\Exception\User\ImageMIMEUnknown;
				}
			} else {
				throw new \Mercurio\Exception\User\ImageInvalid;
			}
		} else {
			throw new \Mercurio\Exception\User\ImageInvalid;
		}
	}

	/**
	 * Set up a canvas and draw the final image
	 * @param int $width Desired output width of the image
	 * @param int $height Desired output height of the image
	 * @param bool $crop Tells the method wether to crop or resize an image inside the new dimensions
	 */
	private function canvas($width, $height, $crop = true){
		$src = $this->src;
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
		
		imagecopyresampled($img, $src, 0, 0, 0, 0, $width, $height, $srcw, $srch);
		imagedestroy($src);
		
		$this->hash = sha1_file($this->file) . '.jpg';
		/**
		 * JPEG is chosen because it's more lightweight than other formats and because png allows for exploitation
		 * @see https://www.idontplaydarts.com/2012/06/encoding-web-shells-in-png-idat-chunks/ */
		imagejpeg($img, $this->path.$this->hash, 95);
		imagedestroy($img);
	}

	/**
	 * Upload image to desired path and with desired dimensions
	 * @param string $file Path to temporary uploaded filed
	 * @param string $path Desired path of destination
	 * @param int $width Desired output width of the image
	 * @param int|bool $ratio Tells the method wether to calc the output height based on the new width or use the defined integer (will crop the image), if left to true will crop the image with an aspect ratio based height
	 * @return string Image file path
	 * @throws \Mercurio\Exception\User\ImageInvalid
	 * @throws \Mercurio\Exception\User\ImageMIMEUnknown
	 */
	public function upload($file, $path, $width, $ratio = false){
		$this->file = $file;
		$this->path = preg_replace('/[\\\\\/]/', DIRECTORY_SEPARATOR, $path);

		// 0666 will prevent unexpected behaviour from files
		if (!is_dir($this->path)) mkdir($this->path, null, true);
		chmod($this->file, 0666);
		
		$this->src = $this->source();
		$src_width = imagesx($this->src);
		$src_height = imagesy($this->src);
		
		if (!$ratio) {
			$height = $width*$src_height/$src_width;
			$crop = false;
		} elseif (ctype_digit($ratio)) {
			$height = $ratio;
			$crop = true;
		} else {
			$height = $width*$src_height/$src_width;
			$crop = true;
		}
		
		$this->canvas($width, $height, $crop);
		return $this->path.$this->hash;
    }
}