<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    #[Template('admin/index.html.twig')]
    public function index(EntityManagerInterface $entityManager): array
    {
        return [
            'users' => $entityManager->getRepository(User::class)->findAll(),
        ];
    }
}
