<?php 

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RandomNumberExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('randomRGBFromString', [$this, 'generateNumber']),
        ];
    }

    public function generateNumber(string $text): int
    {
        $sum = 0;

        for ($i = 0; $i < strlen($text); $i++) {
            $sum += ord($text[$i]);
        }

        return $sum % 255;
    }
}