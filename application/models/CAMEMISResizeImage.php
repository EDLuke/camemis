<?php

/**
 * Documents the file containing the Image2 class
 * 
 * @author Adrian Parker <iamhiddensomewhere@gmail.com>
 * @package Image2
 */

/**
 * The Image2 class (Original)
 * 
 * This class is used for handling images, and represents how I think PHP
 * should handle images. Obviously it does not provide the full scope of
 * capabilities PHP has (with regards to image manipulation), but it gets
 * some common tasks done with relative ease.
 * 
 * @package Original Image2 -> CAMEMISResizeImage
 */
class CAMEMISResizeImage {

    /**
     * Stores the image's width.
     * 
     * @var int
     */
    protected $_width;

    /**
     * Stores the image's height.
     * 
     * @var int
     */
    protected $_height;

    /**
     * Stores the image's mime-type.
     * 
     * @var string
     */
    protected $_mimetype;

    /**
     * Stores the image's resource.
     * 
     * @var resource
     */
    protected $_resource;

    /**
     * Stores the image's file location.
     * 
     * @var string
     */
    protected $_file_location;

    /**
     * Flag for whether or not the constructor was provided a proper file path.
     * 
     * @var bool
     * @see Image2::__constructor()
     */
    protected $_is_filepath;

    /**
     * Flag for whether or not the constructor was provided a file that exists.
     * 
     * @var bool
     * @see Image2::__constructor()
     */
    protected $_is_file;

    /**
     * Flag for whether or not the constructor was provided a file that is an image.
     * 
     * @var bool
     * @see Image2::__constructor()
     */
    protected $_is_image;

    /**
     * Flag for whether or not the constructor was provided a file that is a valid image.
     * 
     * @var bool
     * @see Image2::__constructor()
     */
    protected $_is_validimage;

    /**
     * The Image2 constructor
     * 
     * Using the provided file path, it populates the attributes of an Image2 object. 
     * 
     * Triggers errors if:
     * - this was provided an invalid file path (malformed)
     * - this was provided a file path that doesn't lead to a file
     * 
     * Throws exceptions if:
     * - this was provided a file that was not an image
     * - this was provided an image that is not acceptable
     * 
     * @param string
     * @return Image2
     * @throws NotAnImageException
     * @throws InvalidImageException
     */
    public function __construct($file_path = "") {

        ini_set("max_execution_time", "600");
        ini_set("memory_limit", "128M");

        $this->_is_filepath = false;
        $this->_is_file = false;
        $this->_is_image = false;
        $this->_is_validimage = false;

        $image_data = null;
        if (!is_file($file_path)) {
            trigger_error("CAMEMISResizeImage::__construct() provided an invalid file path (file path: $file_path)", E_USER_WARNING);
        } else if (!file_exists($file_path)) {
            $this->_is_filepath = true;
            trigger_error("CAMEMISResizeImage::__construct() provided a file that does not exist (file path: $file_path)", E_USER_WARNING);
        } else {
            $this->_is_filepath = true;
            $this->_is_file = true;
            $image_data = getimagesize($file_path);
        }

        if (is_null($image_data) == false) {
            if ($image_data == false) {
                throw new NotAnImageException("CAMEMISResizeImage::__construct() provided a file that is not an image");
            } else {
                $this->_is_image = true;

                $acceptable_mime_types = array("image/png", "image/jpeg", "image/gif");
                if (in_array($image_data["mime"], $acceptable_mime_types) == false) {
                    throw new InvalidImageException("CAMEMISResizeImage::__construct() provided an image that is not an acceptable");
                } else {
                    $this->_is_validimage = true;
                    $this->_width = $image_data[0];
                    $this->_height = $image_data[1];

                    $mimetype = $image_data["mime"];
                    $this->_mimetype = $mimetype;
                    switch ($this->_mimetype) {
                        case "image/jpeg":
                            $this->_resource = imagecreatefromjpeg($file_path);
                            break;
                        case "image/png":
                            $this->_resource = imagecreatefrompng($file_path);
                            break;
                        case "image/gif":
                            $this->_resource = imagecreatefromgif($file_path);
                            break;
                    }

                    $this->_file_location = $file_path;
                }
            }
        }
    }

    /**
     * Resizes the image using the provided dimensions
     * 
     * Note: if there is no image to resize, this triggers an E_USER_WARNING error.
     * 
     * @param array
     * @return Image2
     */
    public function resize(array $dimensions) {
// is there an image resource to work with?
        if (isset($this->_resource)) {
// does the dimensions array have the appropriate values to work with?
            if (isset($dimensions["width"]) || isset($dimensions["height"])) {
// has the width value been omitted, while the height value given?
                if (!isset($dimensions["width"]) && isset($dimensions["height"])) {
// is the height an integer?
// -> if so, assign the height variable and assign the width variable scaled similarly to the height
                    if (is_int($dimensions["height"])) {
                        $width = (int) floor($this->_width * ($dimensions["height"] / $this->_height));
                        $height = $dimensions["height"];
                    }
                }
// or has the height value been omitted, and the width value given?
                else if (isset($dimensions["width"]) && !isset($dimensions["height"])) {
// is the width an integer?
// -> if so, assign the width variable and assign the height variable scaled similarly to the width
                    if (is_int($dimensions["width"])) {
                        $width = $dimensions["width"];
                        $height = (int) floor($this->_height * ($dimensions["width"] / $this->_width));
                    }
                }
// or both values were provided
                else {
// are both width and height values integers?
// -> if so, assign the width and height variables
                    if (is_int($dimensions["width"]) && is_int($dimensions["height"])) {
                        $width = $dimensions["width"];
                        $height = $dimensions["height"];
                    }
                }

// have the width and height variables been assigned?
// -> if so, proceed with cropping
                if (isset($width, $height)) {
// generating the placeholder resource
                    $resized_image = $this->newImageResource($width, $height);

// copying the original image's resource into the placeholder and resizing it accordingly
                    imagecopyresampled($resized_image, $this->_resource, 0, 0, 0, 0, $width, $height, $this->_width, $this->_height);

// assigning the new image attributes
                    $this->_resource = $resized_image;
                    $this->_width = $width;
                    $this->_height = $height;
                }
            } else {
                trigger_error("CAMEMISResizeImage::resize() was not provided the apporpriate arguments (given array must contain \"width\" and \"height\" elements)", E_USER_WARNING);
            }
        } else {
            trigger_error("CAMEMISResizeImage::resize() attempting to access a non-existent resource (check if the image was loaded by Image2::__construct())", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Crops the image using the provided dimensions and coordinates
     * 
     * Note: if there is no image to crop, this triggers an E_USER_WARNING error.
     * 
     * @param array $dimensions
     * @param array $coordinates
     * @return Image2
     */
    public function crop(array $dimensions, array $coordinates) {
// is there an image resource to work with?
        if (isset($this->_resource)) {
// do the dimensions have and coordinates have the appropriate values to work with (checking the keys)? 
            if (array_keys($dimensions) == array("width", "height") && array_keys($coordinates) == array("x1", "x2", "y1", "y2")) {
                $is_all_int = true;
                foreach (array_merge($dimensions, $coordinates) as $value) {
                    if (is_int($value) == false) {
                        $is_all_int = false;
                    }
                }
                if ($is_all_int == true) {
                    $width = $dimensions["width"];
                    $height = $dimensions["height"];
                    $x1 = $coordinates["x1"];
                    $x2 = $coordinates["x2"];
                    $y1 = $coordinates["y1"];
                    $y2 = $coordinates["y2"];

// generating the placeholder resource
                    $cropped_image = $this->newImageResource($width, $height);

// copying the original image's resource into the placeholder and cropping it accordingly
                    imagecopyresampled($cropped_image, $this->_resource, 0, 0, $x1, $y1, $width, $height, ($x2 - $x1), ($y2 - $y1));

// assigning the new image attributes
                    $this->_resource = $cropped_image;
                    $this->_width = $width;
                    $this->_height = $height;
                } else {
                    trigger_error("CAMEMISResizeImage::crop() was provided values that were not integers (check the dimensions or coordinates for strings or floats)", E_USER_WARNING);
                }
            } else {
                trigger_error("CAMEMISResizeImage::crop() was not provided the appropriate arguments (first given parameter must be an array and must contain \"width\" and \"height\" elements, and the second given parameter must be an array and contain \"x1\", \"x2\", \"y2\", and \"y2\" elements)", E_USER_WARNING);
            }
        } else {
            trigger_error("CAMEMISResizeImage::crop() attempting to access a non-existent resource (check if the image was loaded by CAMEMISResizeImage::__construct())", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Returns a new image resource (canvas) to be manipulated (painted on)
     * 
     * @param int $width
     * @param int $height
     * @return resource
     */
    private function newImageResource($width, $height) {
        $new_image = imagecreatetruecolor($width, $height);

// handling images with alpha channel information
        switch ($this->_mimetype) {
            case "image/png":
// integer representation of the color black (rgb: 0,0,0)
                $background = imagecolorallocate($new_image, 0, 0, 0);
// removing the black from the placeholder
                imagecolortransparent($new_image, $background);

// turning off alpha blending (to ensure alpha channel information is preserved, rather than removed (blending with the rest of the image in the form of black))
                imagealphablending($new_image, false);

// turning on alpha channel information saving (to ensure the full range of transparency is preserved)
                imagesavealpha($new_image, true);

                break;
            case "image/gif":
// integer representation of the color black (rgb: 0,0,0)
                $background = imagecolorallocate($new_image, 0, 0, 0);
// removing the black from the placeholder
                imagecolortransparent($new_image, $background);

                break;
        }

        return $new_image;
    }

    /**
     * Applies the grayscale filter to the image
     * 
     * Note: if there is no image to apply the filter to, this triggers an E_USER_WARNING error.
     * 
     * @return Image2
     */
    public function grayscale() {
        if (isset($this->_resource)) {
            imagefilter($this->_resource, IMG_FILTER_GRAYSCALE);
        } else {
            trigger_error("CAMEMISResizeImage::grayscale() attempting to access a non-existent resource (check if the image was loaded by CAMEMISResizeImage::__construct())", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Applies the invert filter to the image
     * 
     * Note: if there is no image to apply the filter to, this triggers an E_USER_WARNING error.
     * 
     * @return Image2
     */
    public function invert() {
        if (isset($this->_resource)) {
            imagefilter($this->_resource, IMG_FILTER_NEGATE);
        } else {
            trigger_error("CAMEMISResizeImage::invert() attempting to access a non-existent resource (check if the image was loaded by CAMEMISResizeImage::__construct())", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Applies the a sepia filter
     * 
     * This attempts -- using the colorize filter -- to mimick the 'sepia' effect, but not true sepia (because imagefilter cannot manipulate the intensity (or, more accurately, the HSL)).
     * 
     * Note: if there is no image to apply the filter to, this triggers an E_USER_WARNING error.
     * 
     * @return Image2
     */
    public function sepia() {
        if (isset($this->_resource)) {
            $this->grayscale();
            imagefilter($this->_resource, IMG_FILTER_COLORIZE, 64, 46, 11);
            $this->brightness(-0.08);
        } else {
            trigger_error("CAMEMISResizeImage::sepia() attempting to access a non-existent resource (check if the image was loaded by CAMEMISResizeImage::__construct())", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Adjusts the brightness of the image
     * 
     * The range of values is between -1 and 1, -1 removing all brightness (resulting in a black image) and 1 adding all brightness (resulting in a white image).
     * 
     * Note: if there is no image to adjust, this triggers an E_USER_WARNING error.
     * 
     * @param float
     * @return Image2
     */
    public function brightness($level = 0) {
        if (isset($this->_resource) && is_float($level) && $level >= -1 && $level <= 1) {
            $level = floor($level * 255);
            imagefilter($this->_resource, IMG_FILTER_BRIGHTNESS, $level);
        } else {
            trigger_error("CAMEMISResizeImage::brightness() attempting to access a non-existent resource (check if the image was loaded by CAMEMISResizeImage::__construct())", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Adjusts the contrast of the image
     * 
     * The range of values is between -1 and 1, -1 being removing a lot contrast (resulting in a very light image) and 1 adding a lot of contrast (resulting in a very dark image).
     * 
     * Note: if there is no image to adjust, this triggers an E_USER_WARNING error.
     * 
     * @param int
     * @return Image2
     */
    public function contrast($level = 0) {
        if (isset($this->_resource) && is_float($level) && $level >= -1 && $level <= 1) {
            $level = floor($level * 100);
            imagefilter($this->_resource, IMG_FILTER_CONTRAST, $level);
        } else {
            trigger_error("CAMEMISResizeImage::contrast() attempting to access a non-existent resource (check if the image was loaded by CAMEMISResizeImage::__construct())", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Saves the image to the last file location
     * 
     * Note: this over-writes the original image.
     * 
     * Also: if there is no image to save, this method triggers an E_USER_WARNING.
     * 
     * @return Image2
     */
    public function save() {
// is there an image resource and file location to work with?
        if (isset($this->_resource, $this->_file_location)) {
            switch ($this->_mimetype) {
                case "image/jpeg":
                    imagejpeg($this->_resource, $this->_file_location);
                    break;
                case "image/png":
                    imagepng($this->_resource, $this->_file_location);
                    break;
                case "image/gif":
                    imagegif($this->_resource, $this->_file_location);
                    break;
            }
        } else {
            trigger_error("CAMEMISResizeImage::save() attempting to access a non-existent resource (check if the image was loaded by CAMEMISResizeImage::__construct())", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Saves the image to the file location provided
     * 
     * Note: this over-writes whatever is there, and and creates directories where necessary.
     * 
     * Also: if there is no image to save, this method triggers an E_USER_WARNING error.
     * 
     * @return Image2
     */
    public function saveToFile($file_path = "") {
// is there an image resource to work with?
        if (isset($this->_resource)) {
            if (!is_dir(dirname($file_path))) {
                $parent_directory = dirname($file_path);
                $mode = 0777;
                $recursive = true;
                mkdir($parent_directory, $mode, $recursive);
            }
            switch ($this->_mimetype) {
                case "image/jpeg":
                    $file_extension = "jpg";
                    break;
                case "image/png":
                    $file_extension = "png";
                    break;
                case "image/gif":
                    $file_extension = "gif";
                    break;
            }

// filling out the file path
            $file_path = sprintf("%s.%s", $file_path, $file_extension);
            switch ($this->_mimetype) {
                case "image/jpeg":
                    imagejpeg($this->_resource, $file_path);
                    break;
                case "image/png":
                    imagepng($this->_resource, $file_path);
                    break;
                case "image/gif":
                    imagegif($this->_resource, $file_path);
                    break;
            }

// assigning the new image file_location attribute
            $this->_file_location = $file_path;

// changing the mode (so that all can make good use of the new image file)
            chmod($this->_file_location, 0777);
        } else {
            trigger_error("CAMEMISResizeImage::saveToFile() attempting to access a non-existent resource (check if the image was loaded by CAMEMISResizeImage::__construct())", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Returns the width of the image
     * 
     * Note: if the width is not set, this returns false.
     * 
     * @return int|false
     */
    public function getWidth() {
        $width = false;
        if (isset($this->_width)) {
            $width = $this->_width;
        }

        return $width;
    }

    /**
     * Returns the height of the image
     * 
     * Note: if the height is not set, this returns false.
     * 
     * @return int|false
     */
    public function getHeight() {
        $height = false;
        if (isset($this->_height)) {
            $height = $this->_height;
        }

        return $height;
    }

    /**
     * Returns the mime-type of the image
     * 
     * Note: if the mime-type is not set, this returns false.
     * 
     * @return int|false
     */
    public function getMimetype() {
        $mimetype = false;
        if (isset($this->_mimetype)) {
            $mimetype = $this->_mimetype;
        }

        return $mimetype;
    }

    /**
     * Returns the image resource
     * 
     * Note: if the resource is not set, this returns false.
     * 
     * @return resource|false
     */
    public function getResource() {
        $resource = false;
        if (isset($this->_resource)) {
            $resource = $this->_resource;
        }

        return $resource;
    }

    /**
     * Returns the file location
     * 
     * Note: if the file location is not set, this returns false.
     * 
     * @return string
     */
    public function getFileLocation() {
        $file_location = false;
        if (isset($this->_file_location)) {
            $file_location = $this->_file_location;
        }

        return $file_location;
    }

    /**
     * Returns whether or not the constructor received a valid file path
     * 
     * @return bool
     */
    public function isFilePath() {
        return $this->_is_filepath;
    }

    /**
     * Returns whether or not the constructor received a file that exists
     * 
     * @return bool
     */
    public function isFile() {
        return $this->_is_file;
    }

    /**
     * Returns whether or not the constructor received an image
     * 
     * @return bool
     */
    public function isImage() {
        return $this->_is_image;
    }

    /**
     * Returns whether ot not the constructor received a valid image
     * 
     * Note: a valid image is defined by the mime-type stored in the acceptable_mime_types array in the constructor.
     * 
     * @return bool
     */
    public function isValidImage() {
        return $this->_is_validimage;
    }

    /**
     * Destroys the image's resource
     * 
     * Note: if there is no resource to destroy, this triggers an error.
     * 
     * @return Image2
     */
    public function destroyResource() {
        if (isset($this->_resource)) {
            imagedestroy($this->_resource);
        } else {
            trigger_error("CAMEMISResizeImage::destroyResource() attempting to access a non-existent resource (check if the image was loaded by CAMEMISResizeImage::__construct())", E_USER_WARNING);
        }

        return $this;
    }

}