<?php

namespace App\Test\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UserRepositoryTest extends KernelTestCase
{
    private ?UserRepository $userRepository;
    protected function setUp(): void
    {
        self::bootKernel([
            'debug'       => false,
        ]);

        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $this->userRepository = $entityManager->getRepository(User::class);
    }

    public function testSearchUserDetails(): void
    {
        $keyword = 'testov';
        $searchResults = $this->userRepository->searchUserDetails($keyword);

        foreach ($searchResults as $searchResult) {
            $this->assertTrue(strcasecmp($keyword, $searchResult['firstName']) == 0 ||
                strcasecmp($keyword, $searchResult['lastName']) == 0 ||
                strcasecmp($keyword, $searchResult['firstName'] == 0));
        }
    }
    protected function tearDown(): void
    {
        $this->userRepository = null;
    }
}
