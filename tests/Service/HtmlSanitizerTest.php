<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\HtmlSanitizer;

class HtmlSanitizerTest extends TestCase
{
    private HtmlSanitizer $sanitizer;

    protected function setUp(): void
    {
        $this->sanitizer = new HtmlSanitizer();
    }

    public function testPurifyRemovesScriptTags(): void
    {
        $dirty = '<p>Safe</p><script>alert("XSS")</script>';
        $clean = $this->sanitizer->purify($dirty);

        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringContainsString('<p>Safe</p>', $clean);
    }

    public function testPurifyAllowsBasicTags(): void
    {
        $dirty = '<ul><li>One</li><li>Two</li></ul>';
        $clean = $this->sanitizer->purify($dirty);

        $this->assertStringContainsString('<ul>', $clean);
        $this->assertStringContainsString('<li>One</li>', $clean);
    }

    public function testPurifyStripsDisallowedAttributes(): void
    {
        $dirty  = '<p onclick="evil()" style="color:red;">Click me</p>';
        $dirty .= '<a href="http://example.com" target="_blank" onclick="evil()">Link</a>';
        $clean = $this->sanitizer->purify($dirty);

        // Disallowed attributes (onclick, style, target) should be removed
        $this->assertStringNotContainsString('onclick=', $clean);
        $this->assertStringNotContainsString('style=', $clean);
        $this->assertStringNotContainsString('target=', $clean);

        // Only href remains on <a>
        $this->assertStringContainsString('href="http://example.com"', $clean);
    }

    public function testPurifyReturnsEmptyStringOnNull(): void
    {
        $this->assertSame('', $this->sanitizer->purify(null));
    }
}
