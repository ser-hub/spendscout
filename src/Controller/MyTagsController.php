<?php

namespace App\Controller;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MyTagsController extends AbstractController
{
    #[Route('/my-tags', name: 'app_my_tags')]
    #[Template('my_tags/index.html.twig')]
    public function index(EntityManagerInterface $entityManager): array
    {
        return [
            'tags' => $entityManager->getRepository(Tag::class)->findAll(),
        ];
    }
}
