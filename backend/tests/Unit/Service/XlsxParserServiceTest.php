<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\XlsxParserService;
use PHPUnit\Framework\TestCase;

class XlsxParserServiceTest extends TestCase
{
    private XlsxParserService $parser;

    protected function setUp(): void
    {
        $this->parser = new XlsxParserService();
    }

    public function testParsePrice(): void
    {
        $reflection = new \ReflectionClass($this->parser);
        $method = $reflection->getMethod('parsePrice');
        $method->setAccessible(true);

        $this->assertEquals('900.00', $method->invoke($this->parser, '900,00'));
        $this->assertEquals('1899.00', $method->invoke($this->parser, '1899,00'));
        $this->assertEquals('1066.00', $method->invoke($this->parser, '1066,00'));
    }

    public function testParseBoolean(): void
    {
        $reflection = new \ReflectionClass($this->parser);
        $method = $reflection->getMethod('parseBoolean');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($this->parser, 'true'));
        $this->assertTrue($method->invoke($this->parser, '1'));
        $this->assertTrue($method->invoke($this->parser, 'yes'));
        $this->assertTrue($method->invoke($this->parser, 'да'));
        $this->assertFalse($method->invoke($this->parser, 'false'));
        $this->assertFalse($method->invoke($this->parser, '0'));
        $this->assertFalse($method->invoke($this->parser, 'no'));
        $this->assertFalse($method->invoke($this->parser, 'нет'));
    }

    public function testParseNumeric(): void
    {
        $reflection = new \ReflectionClass($this->parser);
        $method = $reflection->getMethod('parseNumeric');
        $method->setAccessible(true);

        $this->assertEquals(200, $method->invoke($this->parser, '200'));
        $this->assertEquals(300, $method->invoke($this->parser, '300'));
        $this->assertNull($method->invoke($this->parser, 'invalid'));
    }
}