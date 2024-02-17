<?php

namespace App\Utilities;

class EntryCalculator
{
    public static function calculateExpenses($entries, string $currency = null): array
    {
        $totalExpenses = [];
        foreach ($entries as $entry) {
            if ($entry->isIsExpense()) {
                $currencyCode = $entry->getCurrency()->getCode();
                if (array_key_exists($currencyCode, $totalExpenses)) {
                    $totalExpenses[$currencyCode] += $entry->getAmount();
                } else {
                    $totalExpenses[$currencyCode] = $entry->getAmount();
                }
            }
        }

        if ($currency && array_key_exists($currency, $totalExpenses)) {
            return [$currency => $totalExpenses[$currencyCode]];
        }

        return $totalExpenses;
    }

    public static function calculateIncome($entries, string $currency = null): array
    {
        $totalIncome = [];
        foreach ($entries as $entry) {
            if (!$entry->isIsExpense()) {
                $currencyCode = $entry->getCurrency()->getCode();
                if (array_key_exists($currencyCode, $totalIncome)) {
                    $totalIncome[$currencyCode] += $entry->getAmount();
                } else {
                    $totalIncome[$currencyCode] = $entry->getAmount();
                }
            }
        }


        if ($currency && array_key_exists($currency, $totalIncome)) {
            return [$currency => $totalIncome[$currencyCode]];
        }

        return $totalIncome;
    }
}
