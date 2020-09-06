<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageMagick;

use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidBinaryImageDataException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidColorException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidImageDimensionException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessingStrategy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageMagick\ImageMagickInscribeStrategy
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageMagick\ValidateImageDimensionsTrait
 */
class ImageMagickInscribeStrategyTest extends TestCase
{
    final protected function setUp(): void
    {
        if (! extension_loaded('imagick')) {
            $this->markTestSkipped('The PHP extension imagick is not installed');
        }
    }
    
    public function testImageProcessorStrategyInterfaceIsImplemented(): void
    {
        $strategy = new ImageMagickInscribeStrategy(1, 1, 'none');
        $this->assertInstanceOf(ImageProcessingStrategy::class, $strategy);
    }

    public function testExceptionIsThrownIfWidthIsNotAnInteger(): void
    {
        $this->expectException(\TypeError::class);
        new ImageMagickInscribeStrategy('foo', 1, 'none');
    }

    public function testExceptionIsThrownIfWidthIsNotPositive(): void
    {
        $this->expectException(InvalidImageDimensionException::class);
        $this->expectExceptionMessage('Image width should be greater then zero, got 0.');
        (new ImageMagickInscribeStrategy(0, 1, 'none'))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfHeightIsNotAnInteger(): void
    {
        $this->expectException(\TypeError::class);
        new ImageMagickInscribeStrategy(1, 'foo', 'none');
    }

    public function testExceptionIsThrownIfHeightIsNotPositive(): void
    {
        $this->expectException(InvalidImageDimensionException::class);
        $this->expectExceptionMessage('Image height should be greater then zero, got -1.');
        (new ImageMagickInscribeStrategy(1, -1, 'none'))->processBinaryImageData('');
    }


    public function testExceptionIsThrownIfBackgroundColorIsNotAString(): void
    {
        $this->expectException(\TypeError::class);
        new ImageMagickInscribeStrategy(1, 1, []);
    }

    public function testExceptionIsThrownIfInvalidBackgroundColorIsSpecified(): void
    {
        $this->expectException(InvalidColorException::class);
        (new ImageMagickInscribeStrategy(1, 1, 'foo'))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfImageStreamIsNotValid(): void
    {
        $this->expectException(InvalidBinaryImageDataException::class);
        (new ImageMagickInscribeStrategy(1, 1, 'none'))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfImageFormatIsNotSupported(): void
    {
        $this->expectException(InvalidBinaryImageDataException::class);

        $imageStream = file_get_contents(__DIR__ . '/../fixture/blank.ico');

        (new ImageMagickInscribeStrategy(1, 1, 'none'))->processBinaryImageData($imageStream);
    }

    public function testImageIsResizedToGivenDimensions(): void
    {
        $requiredWidth = 15;
        $requiredHeight = 10;

        $imageStream = file_get_contents(__DIR__ . '/../fixture/image.jpg');

        $imageMagickInscribeStrategy = new ImageMagickInscribeStrategy($requiredWidth, $requiredHeight, 'none');
        $result = $imageMagickInscribeStrategy->processBinaryImageData($imageStream);
        $resultImageInfo = getimagesizefromstring($result);

        $this->assertEquals($requiredWidth, $resultImageInfo[0]);
        $this->assertEquals($requiredHeight, $resultImageInfo[1]);
        $this->assertEquals('image/jpeg', $resultImageInfo['mime']);
    }
}
