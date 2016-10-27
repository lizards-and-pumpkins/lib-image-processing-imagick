<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageMagick;

use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidBinaryImageDataException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidColorException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessingStrategy;

class ImageMagickInscribeStrategy implements ImageProcessingStrategy
{
    use ValidateImageDimensionsTrait;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var string
     */
    private $backgroundColor;

    public function __construct(int $width, int $height, string $backgroundColor)
    {
        $this->width = $width;
        $this->height = $height;
        $this->backgroundColor = $backgroundColor;
    }

    public function processBinaryImageData(string $binaryImageData) : string
    {
        $this->validateImageDimensions($this->width, $this->height);
        $this->validateBackgroundColor();

        $image = new \Imagick();

        try {
            $image->readImageBlob($binaryImageData);
        } catch (\ImagickException $e) {
            throw new InvalidBinaryImageDataException($e->getMessage());
        }

        $image->resizeImage($this->width, $this->height, \Imagick::FILTER_LANCZOS, 1, true);
        $canvas = $this->inscribeImageIntoCanvas($image);

        return $canvas->getImageBlob();
    }

    private function inscribeImageIntoCanvas(\Imagick $image) : \Imagick
    {
        $dimensions = $image->getImageGeometry();
        $x = (int) round(($this->width - $dimensions['width']) / 2);
        $y = (int) round(($this->height - $dimensions['height']) / 2);

        $canvas = new \Imagick();
        $canvas->newImage($this->width, $this->height, $this->backgroundColor, $image->getImageFormat());
        $canvas->compositeImage($image, \Imagick::COMPOSITE_OVER, $x, $y);

        return $canvas;
    }

    private function validateBackgroundColor()
    {
        try {
            (new \ImagickPixel())->setColor($this->backgroundColor);
        } catch (\Exception $e) {
            if ($e instanceof \ImagickException || $e instanceof \ImagickPixelException) {
                throw new InvalidColorException($e->getMessage());
            }
        }
    }
}
