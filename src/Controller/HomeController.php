<?php

namespace App\Controller;

use App\Entity\Entry;
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
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }

        return [
            'entries' => $entityManager->getRepository(Entry::class)->findAll(),
        ];
    }
}
