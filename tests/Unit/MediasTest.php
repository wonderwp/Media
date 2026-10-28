<?php

declare(strict_types=1);

namespace WonderWp\Component\Media\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WonderWp\Component\HttpFoundation\Result;
use WonderWp\Component\Media\Medias;

final class MediasTest extends TestCase
{
    public function testUploadToWithEmptyFileReturnsForbiddenResult(): void
    {
        $result = Medias::uploadTo(null);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertSame(403, $result->getCode());
    }

    public function testUploadDirFilterAppliesOverrideFromContainer(): void
    {
        $container = \WonderWp\Component\DependencyInjection\Container::getInstance();
        $container['upload_dir_override'] = '/custom';

        $dirs = Medias::wpse183245UploadDir([
            'basedir' => '/var/uploads',
            'baseurl' => 'http://example.test/uploads',
            'subdir' => '',
            'path' => '/var/uploads',
            'url' => 'http://example.test/uploads',
        ]);

        $this->assertSame('/custom', $dirs['subdir']);
        $this->assertSame('/var/uploads/custom', $dirs['path']);
        $this->assertSame('http://example.test/uploads/custom', $dirs['url']);

        unset($container['upload_dir_override']);
    }
}
