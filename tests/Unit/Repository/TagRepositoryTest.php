<?php

namespace App\Test\Repository;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TagRepositoryTest extends KernelTestCase
{
    private ?TagRepository $tagRepository;
    protected function setUp(): void
    {
        self::bootKernel([
            'debug'       => false,
        ]);

        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $this->tagRepository = $entityManager->getRepository(Tag::class);
    }

    public function testFindTagNamesOfUser(): void
    {
        $userId = 1;
        $defaultTagsCount = 18;
        $userTagsCount = 0;
        $tagNames = $this->tagRepository->findTagNamesOfUser($userId);

        $this->assertCount($defaultTagsCount + $userTagsCount, $tagNames);
    }
    protected function tearDown(): void
    {
        $this->tagRepository = null;
    }
}