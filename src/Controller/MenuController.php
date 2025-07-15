<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller responsible for rendering menus (public and authenticated).
 */
final class MenuController extends AbstractController
{
    /**
     * Displays the main public menu for all users (unauthenticated).
     *
     * @return Response
     */
    #[Route('/menu', name: 'app_menu')]
    public function index(): Response
    {
        return $this->render('menu/index.html.twig');
    }

    /**
     * Displays the menu for authenticated users (e.g., admin or logged-in users).
     *
     * @return Response
     */
    #[Route('/menu_authenticated', name: 'app_menu_authenticated')]
    public function authenticated(): Response
    {
        return $this->render('menu/menu_auth.html.twig');
    }
}
