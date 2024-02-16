<?php

class Captcha
{
    private $width;
    private $height;
    private $length;
    private $font;
    private $fontSize;

    public function __construct($width = 120, $height = 40, $length = 6, $font = __DIR__ . '/scorn.ttf', $fontSize = 16)
    {
        $this->width = $width;
        $this->height = $height;
        $this->length = $length;
        $this->font = $font;
        $this->fontSize = $fontSize;
    }

    private function generateCode()
    {
        $characters = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $this->length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public function createImage()
    {
        $code = $this->generateCode();
        $_SESSION['captcha'] = $code;

        $image = imagecreatetruecolor($this->width, $this->height);
        $background = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, $this->width, $this->height, $background);

        // Aggiunta di distorsioni
        for ($i = 0; $i < $this->length; $i++) {
            $letter = $code[$i];
            $angle = rand(-15, 15);
            $textbox = imagettfbbox($this->fontSize, $angle, $this->font, $letter);
            $x = ($this->width / $this->length) * $i + ($textbox[4] - $textbox[0]) / 4;
            $y = ($this->height - ($textbox[5] - $textbox[1])) / 2 + rand(-5, 5);
            imagettftext($image, $this->fontSize, $angle, $x, $y, $textColor, $this->font, $letter);
        }

        // Aggiunta di linee casuali
        for ($i = 0; $i < 5; $i++) {
            $lineColor = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
            imageline($image, rand(0, $this->width), rand(0, $this->height), rand(0, $this->width), rand(0, $this->height), $lineColor);
        }

        header('Content-Type: image/jpeg');
        imagejpeg($image);
        imagedestroy($image);
    }

    public function createImageBase64() {
        $code = $this->generateCode();
        $_SESSION['captcha'] = $code;

        $image = imagecreatetruecolor($this->width, $this->height);
        $background = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, $this->width, $this->height, $background);

// Aggiunta di distorsioni
        for ($i = 0; $i < $this->length; $i++) {
            $letter = $code[$i];
            $angle = rand(-15, 15);
            $textbox = imagettfbbox($this->fontSize, $angle, $this->font, $letter);
            $x = ($this->width / $this->length) * $i + ($textbox[4] - $textbox[0]) / 4;
            $y = ($this->height - ($textbox[5] - $textbox[1])) / 2 + rand(-5, 5);
            imagettftext($image, $this->fontSize, $angle, $x, $y, $textColor, $this->font, $letter);
        }

        // Aggiunta di linee casuali
        for ($i = 0; $i < 5; $i++) {
            $lineColor = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
            imageline($image, rand(0, $this->width), rand(0, $this->height), rand(0, $this->width), rand(0, $this->height), $lineColor);
        }

        ob_start(); // Inizia la cattura dell'output
        imagejpeg($image); // Genera l'immagine JPEG e la invia all'output buffer
        $contents = ob_get_contents(); // Prende i contenuti dell'output buffer
        ob_end_clean(); // Pulisce e chiude l'output buffer

        imagedestroy($image);

        return 'data:image/jpeg;base64,' . base64_encode($contents); // Restituisce la stringa codificata in base64
    }
}
