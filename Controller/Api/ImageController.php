<?php

declare(strict_types=1);

class ImageController extends BaseController
{
    protected $_httpStatusCode = [
        "500" => "HTTP/1.1 500 Internal Server Error",
        "200" => "HTTP/1.1 200 OK",
    ];

    function __construct()
    {
        //echo $_SERVER["REQUEST_METHOD"];
    }

    function resize_image($file, $w, $h, $crop = FALSE)
    {
        $ext =  pathinfo($file, PATHINFO_EXTENSION);
        list($width, $height) = getimagesize($file);
        if ($width > $w || $height >> $h) {
            if ($w > 0 && $h > 0) {
                $r = $width / $height;
                if ($crop) {
                    if ($width > $height) {
                        $width = ceil($width - ($width * abs($r - $w / $h)));
                    } else {
                        $height = ceil($height - ($height * abs($r - $w / $h)));
                    }
                } else {
                    if ($w / $h > $r) {
                        $width = $h * $r;
                        $height = $h;
                    } else {
                        $width = $w;
                        $height = $w / $r;
                    }
                }
            }
        }
        $imgResized = null;
        switch ($ext) {
            case 'jpg':
                $src = imagecreatefromjpeg($file);
                $imgResized = imagescale($src,  (int) $width, (int) $height);
                imagejpeg($imgResized);
                break;
            case 'png':
                $src = imagecreatefrompng($file);
                $imgResized = imagescale($src,  (int) $width, (int) $height);
                imagepng($imgResized);
                break;
            default:
        }
        ob_start(); //Turn on output buffering
        $output = ob_get_contents(); // get the image as a string in a variable
        ob_end_clean(); //Turn off output buffering and clean it
        return strlen($output); //size in bytes
        // if ($imgResized != null) {

        // } else {
        //     return false;
        // }
    }

    public function thumbnail()
    {
        $jId = $_GET["jId"];
        $fId = $_GET["fId"];
        try {
            $ext =  pathinfo(WEB_PATH_ATTACH_FILE . "/${jId}/${fId}", PATHINFO_EXTENSION);
            switch ($ext) {
                case 'jpg':
                    header("Content-type: image/jpeg");
                    break;
                case 'png':
                    header("Content-type: image/png");
                    break;
                default:
            }
            echo $this->resize_image(WEB_PATH_ATTACH_FILE . "/${jId}/${fId}", 150, 150, false);
        } catch (\Throwable $th) {
            print_r('sssssssssssssss');
            throw $th;
        }
    }

    public function view()
    {
        $jId = $_GET["jId"];
        $fId = $_GET["fId"];
        try {
            $ext =  pathinfo(WEB_PATH_ATTACH_FILE . "/${jId}/${fId}", PATHINFO_EXTENSION);
            switch ($ext) {
                case 'jpg':
                    header("Content-type: image/jpeg");
                    break;
                case 'png':
                    header("Content-type: image/png");
                    break;
                default:
            }
            echo $this->resize_image(WEB_PATH_ATTACH_FILE . "/${jId}/${fId}", null, null, false);
        } catch (\Throwable $th) {
            print_r('sssssssssssssss');
            throw $th;
        }
    }
}