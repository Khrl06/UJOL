<?php
namespace Tests\Unit;

class UrlHelperTest extends TestCase
{
    public function testBaseUrlReturnsDefinedBaseUrlWhenEmptyPath(): void
    {
        $url = base_url();
        $this->assertEquals(BASE_URL, $url);
    }

    public function testBaseUrlAppendsPathCleanly(): void
    {
        $url1 = base_url('login');
        $url2 = base_url('/login');

        $this->assertEquals(BASE_URL . '/login', $url1);
        $this->assertEquals(BASE_URL . '/login', $url2);
    }

    public function testBaseUrlHandlesSubroutes(): void
    {
        $url = base_url('admin/settings?tab=ai');
        $this->assertEquals(BASE_URL . '/admin/settings?tab=ai', $url);
    }

    public function testAssetReturnsCorrectAssetPath(): void
    {
        $assetUrl = asset('css/bootstrap.min.css');
        $this->assertStringContainsString('assets/css/bootstrap.min.css', $assetUrl);
    }
}
