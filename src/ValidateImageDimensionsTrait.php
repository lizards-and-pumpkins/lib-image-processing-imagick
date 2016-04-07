<?php

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageMagick;

use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidImageDimensionException;

trait ValidateImageDimensionsTrait
{
    /**
     * @param int $width
     * @param int $height
     */
    private function validateImageDimensions($width, $height)
    {
        if (! is_int($width)) {
            throw new InvalidImageDimensionException(
                sprintf('Expected integer as image width, got %s.', gettype($width))
            );
        }

        if (! is_int($height)) {
            throw new InvalidImageDimensionException(
                sprintf('Expected integer as image height, got %s.', gettype($height))
            );
        }

        if ($width <= 0) {
            throw new InvalidImageDimensionException(
                sprintf('Image width should be greater then zero, got %s.', $width)
            );
        }

        if ($height <= 0) {
            throw new InvalidImageDimensionException(
                sprintf('Image height should be greater then zero, got %s.', $height)
            );
        }
    }
}
