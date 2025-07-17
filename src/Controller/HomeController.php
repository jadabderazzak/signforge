<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller for handling the public homepage of the application.
 */
final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    /**
 * Renders the homepage view.
 *
 * @return Response
 */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            
        ]);
    }
}
