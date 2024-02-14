<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Entry;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = null;
        $entries = [];

        for ($i = 1; $i <= 3; $i++) {
            $user = $this->createUser(
                "Test". $i,
                "Testov",
                "test" . $i . "testov@email.com",
                "123456tT*"
            );

            $manager->persist($user);

            $entries = $this->createRandomEntries();
            foreach ($entries as $entry) {
                $entry->setUser($user);
                $manager->persist($entry);
            }
        }

        $manager->flush();
    }

    public function createUser(
        string $firstName,
        string $lastName,
        string $email,
        string $plainPassword,
    ) {
        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $plainPassword
            )
        );

        return $user;
    }

    private function createRandomEntries(): array 
    {
        $entries = [];

        for ($i = 1; $i <= rand(5, 10); $i++) {
            $entries[] = $this->createEntry("dummy" . $i);
        }

        return $entries;
    }

    private function createEntry(string $name): Entry
    {
        $entry = new Entry();
        $entry->setIsExpense(rand(0, 1));
        $entry->setName($name);
        $entry->setTag($this->getReference("tag_" . rand(1, 18)));
        $entry->setAmount(rand(1, 10000) + ((float)rand() / (float)getrandmax()));
        $entry->setCurrency($this->getReference("currency_" . rand(1, 22)));
        $date = new DateTime();
        $date->setDate(2024 - rand(1, 5), rand(1, 12), rand(1, 31));
        $entry->setDate($date);

        return $entry;
    }
}
