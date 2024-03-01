<?php

namespace App\Test\Utilities;

use App\Entity\Currency;
use App\Entity\Entry;
use App\Utilities\EntryCalculator;
use PHPUnit\Framework\TestCase;

final class EntryCalculatorTest extends TestCase
{
    private ?EntryCalculator $entryCalculator;
    private ?array $testEntries;
    protected function setUp(): void
    {
        $this->entryCalculator = new EntryCalculator;
        $bgnCurrency = new Currency();
        $bgnCurrency->setCode('BGN');
        $gbpCurrency = new Currency();
        $gbpCurrency->setCode('GBP');

        for ($i = 1; $i <= 5; $i++){
            $testEntry = new Entry();
            $testEntry->setIsExpense(true);
            $testEntry->setAmount($i * 3);
            if ($i % 2 == 0) {
                $testEntry->setCurrency($bgnCurrency);
            } else {
                $testEntry->setCurrency($gbpCurrency);
                $testEntry->setAmount($testEntry->getAmount() + 0.51);
            }
            $this->testEntries[] = $testEntry;
        }

        for ($i = 1; $i <= 5; $i++){
            $testEntry = new Entry();
            $testEntry->setIsExpense(false);
            $testEntry->setAmount($i * 4);
            if ($i % 2 == 0) {
                $testEntry->setCurrency($bgnCurrency);
            } else {
                $testEntry->setCurrency($gbpCurrency);
                $testEntry->setAmount($testEntry->getAmount() + 0.51);
            }
            $this->testEntries[] = $testEntry;
        }
    }

    public function testCalculateExpenses(): void
    {
        $expenses = $this->entryCalculator::calculateExpenses($this->testEntries);
        $this->assertSame([
            'GBP' => 28.53,
            'BGN' => 18.0
        ], $expenses);
    }

    public function testCalculateExpensesBGN(): void
    {
        $expenses = $this->entryCalculator::calculateExpenses($this->testEntries, 'BGN');
        $this->assertSame([
            'BGN' => 18.0
        ], $expenses);
    }

    public function testCalculateExpensesGBP(): void
    {
        $expenses = $this->entryCalculator::calculateExpenses($this->testEntries, 'GBP');
        $this->assertSame([
            'GBP' => 28.53
        ], $expenses);
    }

    public function testCalculateIncome(): void
    {
        $income = $this->entryCalculator::calculateIncome($this->testEntries);
        $this->assertSame([
            'GBP' => 37.53,
            'BGN' => 24.0
        ], $income);
    }

    public function testCalculateIncomeBGN(): void
    {
        $income = $this->entryCalculator::calculateIncome($this->testEntries, 'BGN');
        $this->assertSame([
            'BGN' => 24.0
        ], $income);
    }

    public function testCalculateIncomeGBP(): void
    {
        $income = $this->entryCalculator::calculateIncome($this->testEntries, 'GBP');
        $this->assertSame([
            'GBP' => 37.53
        ], $income);
    }

    protected function tearDown(): void
    {
        $this->entryCalculator = null;
        $this->testEntries = null;
    }
}