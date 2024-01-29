<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Template('home/index.html.twig')]
    #[Route('/', name: 'app_home')]
    public function index()
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }

        return [
            'controller_name' => 'HomeController',
        ];
    }
}
