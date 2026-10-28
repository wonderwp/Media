<?php

declare(strict_types=1);

namespace WonderWp\Component\Media\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WonderWp\Component\HttpFoundation\Result;
use WonderWp\Component\Media\Medias;

final class MediasTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wwp_test_filters'] = [];
        $GLOBALS['wwp_test_removed_filters'] = [];
        $GLOBALS['wpdb'] = new \wpdb();
    }

    public function testGetIdFromUrlReturnsGuidMatch(): void
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        $wpdb->guidCol = ['42'];

        $this->assertEquals(42, Medias::getIdFromUrl('https://example.test/wp-content/uploads/photo.jpg'));
    }

    public function testGetIdFromUrlFallsBackToAttachedFileMeta(): void
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        $wpdb->guidCol = [];
        $wpdb->metaCol = ['99'];

        $this->assertEquals(99, Medias::getIdFromUrl('https://example.test/wp-content/uploads/2024/scaled-photo.jpg'));
    }

    public function testUploadToAddsAndRemovesUploadDirFilter(): void
    {
        $file = ['name' => 'test.jpg', 'tmp_name' => '/tmp/test.jpg'];

        Medias::uploadTo($file, '/custom-dir');

        $this->assertNotEmpty($GLOBALS['wwp_test_filters']['upload_dir'] ?? []);
        $this->assertSame([Medias::class, 'wpse183245UploadDir'], $GLOBALS['wwp_test_filters']['upload_dir'][0]['callback']);
        $this->assertCount(1, $GLOBALS['wwp_test_removed_filters']);
        $this->assertSame('upload_dir', $GLOBALS['wwp_test_removed_filters'][0]['hook']);
    }

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
