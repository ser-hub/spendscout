<?php

namespace App\Test\Repository;

use App\Entity\Entry;
use App\Repository\EntryRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class EntryRepositoryTest extends KernelTestCase
{
    private ?EntryRepository $entryRepository;
    protected function setUp(): void
    {
        self::bootKernel([
            'debug'       => false,
        ]);

        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $this->entryRepository = $entityManager->getRepository(Entry::class);
    }

    public function testFindByFiltersUserId(): void
    {
        $userId = 3;
        $resultEntries = $this->entryRepository->findByFilters($userId);

        foreach ($resultEntries as $resultEntry) {
            $this->assertEquals($userId, $resultEntry->getUser()->getId());
        }
    }

    public function testFindByFiltersCurrencyId(): void
    {
        $currencyId = $this->entryRepository->find(rand(1, 19))->getCurrency()->getId();
        $resultEntries = $this->entryRepository->findByFilters(null, $currencyId);

        foreach ($resultEntries as $resultEntry) {
            $this->assertEquals($currencyId, $resultEntry->getCurrency()->getId());
        }
    }

    public function testFindByFiltersTagId(): void
    {
        $tagId = $this->entryRepository->find(rand(1, 19))->getTag()->getId();
        $resultEntries = $this->entryRepository->findByFilters(null, null, $tagId);

        foreach ($resultEntries as $resultEntry) {
            $this->assertEquals($tagId, $resultEntry->getTag()->getId());
        }
    }

    public function testFindByFiltersDateFrom(): void
    {
        $dateFrom = '2019-01-01';
        $resultEntries = $this->entryRepository->findByFilters(null, null, null, $dateFrom);

        $dateTime = new DateTime($dateFrom);

        foreach ($resultEntries as $resultEntry) {
            $this->assertGreaterThanOrEqual($dateTime, $resultEntry->getDate());
        }
    }

    public function testFindByFiltersDateTo(): void
    {
        $dateTo = '2024-12-31';
        $resultEntries = $this->entryRepository->findByFilters(null, null, null, null, $dateTo);

        $dateTime = new DateTime($dateTo);

        foreach ($resultEntries as $resultEntry) {
            $this->assertLessThanOrEqual($dateTime, $resultEntry->getDate());
        }
    }

    public function testFindByFiltersAll(): void
    {
        $targetEntry = $this->entryRepository->find(rand(1, 19));
        $userId = $targetEntry->getUser()->getId();
        $tagId = $targetEntry->getTag()->getId();
        $currencyId = $targetEntry->getCurrency()->getId();
        $dateFrom = '2019-01-01';
        $dateTo = '2023-12-31';
        $resultEntries = $this->entryRepository->findByFilters($userId, $currencyId, $tagId, $dateFrom, $dateTo);

        $dateTo = new DateTime($dateTo);
        $dateFrom = new DateTime($dateFrom);

        foreach ($resultEntries as $resultEntry) {
            $this->assertEquals($userId, $resultEntry->getUser()->getId());
            $this->assertEquals($currencyId, $resultEntry->getCurrency()->getId());
            $this->assertEquals($tagId, $resultEntry->getTag()->getId());
            $this->assertGreaterThan($dateFrom, $resultEntry->getDate());
            $this->assertLessThan($dateTo, $resultEntry->getDate());
        }
    }

    protected function tearDown(): void
    {
        $this->entryRepository = null;
    }
}