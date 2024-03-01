<?php

namespace App\Test\Twig;

use App\Twig\RandomNumberExtension;
use PHPUnit\Framework\TestCase;

final class RandomNumberExtensionTest extends TestCase
{
    private ?RandomNumberExtension $numberGenerator;
    private ?array $stringValues;
    protected function setUp(): void
    {
        $this->numberGenerator = new RandomNumberExtension();
        $this->stringValues = [
            'lorem ipsum',
            'reliable leader',
            'jungle tenant',
            'burst species',
            'enjoy confuse'
        ];
    }

    public function testGenerateNumber(): void
    {
        $limit = 500;
        $values = [];
        for ($i = 0; $i < 5; $i++) {
            $intValue = $this->numberGenerator->generateNumber($this->stringValues[$i], $limit);

            if ($i != 0) {
                $this->assertNotEquals($values[$i - 1], $intValue);
            }
            $this->assertLessThan($limit, $intValue);

            $values[] = $intValue;
        }
    }

    protected function tearDown(): void
    {
        $this->numberGenerator = null;
        $this->stringValues = null;
    }
}