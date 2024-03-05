<?php

namespace App\Controller;

use App\Entity\Entry;
use App\Entity\Tag;
use App\Entity\Currency;
use App\Utilities\EntryCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/reports', name: 'app_reports')]
class ReportsController extends AbstractController
{
    public function __construct(
        private EntryCalculator $entryCalculator,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: '_index')]
    #[Template('reports/index.html.twig')]
    public function index(
        ChartBuilderInterface $chartBuilder,
    ): array {
        $chart = $chartBuilder->createChart(Chart::TYPE_PIE);

        $userEntries = $this->getUser()->getEntries();
        $allCurrencies = $this->entityManager->getRepository(Currency::class)->findAll();

        $currency = null;
        if (!$userEntries->isEmpty()) {
            $expenses = $this->entryCalculator::calculateExpenses($userEntries);
            $income = $this->entryCalculator::calculateIncome($userEntries);

            if (count($expenses) > 0) {
                arsort($expenses);
                $currency = array_keys($expenses)[0];

                if (!array_key_exists($currency, $income)) {
                    $income[$currency] = 0;
                }
            } else {
                arsort($income);
                $currency = array_keys($income)[0];

                if (!array_key_exists($currency, $expenses)) {
                    $expenses[$currency] = 0;
                }
            }

            $chart->setData([
                'labels' => ['Expenses', 'Income'],
                'datasets' => [
                    [
                        'label' => $currency,
                        'backgroundColor' => ['#E6C715A0', '#E6C71540'],
                        'borderColor' => 'transparent',
                        'hoverBorderColor' => 'black',
                        'data' => [$expenses[$currency], $income[$currency]],
                    ],
                ],
            ]);
        } else {
            $chart->setData([
                'labels' => ['No data'],
                'datasets' => [
                    [
                        'label' => 'No data',
                        'backgroundColor' => ['#E6C715A0', '#E6C71540'],
                        'borderColor' => 'transparent',
                        'hoverBorderColor' => 'black',
                        'data' => [0],
                    ],
                ],
            ]);

            $currency = $allCurrencies[0]->getCode();
        }

        return [
            'user_tags' => $this->getUser()->getTags(),
            'default_tags' => $this->entityManager->getRepository(Tag::class)->findDefaultTags(),
            'pie_chart' => $chart,
            'currencies' => $allCurrencies,
            'set_currency' => $currency,
            'isEntriesEmpty' => $userEntries->isEmpty(),
        ];
    }

    #[Route('/report', name: '_report', methods: ['GET'])]
    public function getStatistics(
        #[MapQueryParameter] int $currencyId = null,
        #[MapQueryParameter] int $tagId = null,
        #[MapQueryParameter] string $dateFrom = null,
        #[MapQueryParameter] string $dateTo = null,
    ): JsonResponse {

        if ($currencyId != null) {
            $entries = $this->entityManager->getRepository(Entry::class)->findByFilters(
                $this->getUser()->getId(),
                $currencyId,
                $tagId,
                $dateFrom,
                $dateTo
            );
            $currency = $this->entityManager->getRepository(Currency::class)->find($currencyId)->getCode();

            if (!count($entries)) {
                return $this->json('No data');
            }

            $expenses = $this->entryCalculator::calculateExpenses($entries);
            $income = $this->entryCalculator::calculateIncome($entries);

            if (!array_key_exists($currency, $expenses)) {
                $expenses[$currency] = 0;
            }

            if (!array_key_exists($currency, $income)) {
                $income[$currency] = 0;
            }

            return $this->json([
                'expenses' => $expenses[$currency],
                'income' => $income[$currency]
            ]);
        } else {
            return $this->json("Invalid request. Query must contain currencyId.", 422);
        }
    }
};
