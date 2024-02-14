<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TagFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tags = [
            "Food",
            "Transportation",
            "Housing",
            "Insurance",
            "Debt",
            "Utilities",
            "Personal care",
            "Entertainment",
            "Health and fitness",
            "Education",
            "Savings",
            "Salary",
            "Investment",
            "Business",
            "Rent",
            "Pension",
            "Government benefits",
            "Gifts",
        ];

        $i = 1;
        foreach ($tags as $tag) {
            $tagObj = $this->createTag($tag);
            $manager->persist($tagObj);
            $this->addReference('tag_' . $i++, $tagObj);
        }

        $manager->flush();
    }

    private function createTag(string $name): Tag
    {
        $tag = new Tag();
        $tag->setName($name);
        
        return $tag;
    }
}
