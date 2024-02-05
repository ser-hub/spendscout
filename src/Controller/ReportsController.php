<?php

namespace App\Controller;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;

class ReportsController extends AbstractController
{
    #[Route('/reports', name: 'app_reports')]
    #[Template('reports/index.html.twig')]
    public function index(EntityManagerInterface $entityManager): array
    {
        return [
            'tags' => $entityManager->getRepository(Tag::class)->findAll(),
        ];
    }
}
