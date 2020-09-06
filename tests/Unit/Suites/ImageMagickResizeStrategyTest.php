<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageMagick;

use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidBinaryImageDataException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidImageDimensionException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessingStrategy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageMagick\ImageMagickResizeStrategy
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageMagick\ValidateImageDimensionsTrait
 */
class ImageMagickResizeStrategyTest extends TestCase
{
    final protected function setUp(): void
    {
        if (! extension_loaded('imagick')) {
            $this->markTestSkipped('The PHP extension imagick is not installed');
        }
    }

    public function testImageProcessorStrategyInterfaceIsImplemented(): void
    {
        $strategy = new ImageMagickResizeStrategy(1, 1);
        $this->assertInstanceOf(ImageProcessingStrategy::class, $strategy);
    }

    public function testExceptionIsThrownIfWidthIsNotAnInteger(): void
    {
        $this->expectException(\TypeError::class);
        new ImageMagickResizeStrategy('foo', 1);
    }

    public function testExceptionIsThrownIfWidthIsNotPositive(): void
    {
        $this->expectException(InvalidImageDimensionException::class);
        $this->expectExceptionMessage('Image width should be greater then zero, got 0.');

        (new ImageMagickResizeStrategy(0, 1))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfHeightIsNotAnInteger(): void
    {
        $this->expectException(\TypeError::class);
        new ImageMagickResizeStrategy(1, 'foo');
    }

    public function testExceptionIsThrownIfHeightIsNotPositive(): void
    {
        $this->expectException(InvalidImageDimensionException::class);
        $this->expectExceptionMessage('Image height should be greater then zero, got -1.');

        (new ImageMagickResizeStrategy(1, -1))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfImageStreamIsNotValid(): void
    {
        $this->expectException(InvalidBinaryImageDataException::class);

        (new ImageMagickResizeStrategy(1, 1))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfImageFormatIsNotSupported(): void
    {
        $this->expectException(InvalidBinaryImageDataException::class);

        $imageStream = file_get_contents(__DIR__ . '/../fixture/blank.ico');
        (new ImageMagickResizeStrategy(1, 1))->processBinaryImageData($imageStream);
    }

    public function testImageIsResizedToGivenDimensions(): void
    {
        $width = 15;
        $height = 10;

        $imageStream = file_get_contents(__DIR__ . '/../fixture/image.jpg');

        $result = (new ImageMagickResizeStrategy($width, $height))->processBinaryImageData($imageStream);
        $resultImageInfo = getimagesizefromstring($result);

        $this->assertEquals($width, $resultImageInfo[0]);
        $this->assertEquals($height, $resultImageInfo[1]);
        $this->assertEquals('image/jpeg', $resultImageInfo['mime']);
    }
}
