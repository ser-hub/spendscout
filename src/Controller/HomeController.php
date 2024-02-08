<?php

namespace App\Controller;

use App\Entity\Entry;
use App\Entity\Tag;
use App\Entity\Currency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('', name: 'app_home')]
    #[Template('home/index.html.twig')]
    public function index(EntityManagerInterface $entityManager)
    {
        return [
            'entries' => $entityManager->getRepository(Entry::class)->findAll(),
            'user_tags' => $this->getUser()->getTags(),
            'default_tags' => $entityManager->getRepository(Tag::class)->findDefaultTags(),
            'currencies' => $entityManager->getRepository(Currency::class)->findAll(),
        ];
    }
}
