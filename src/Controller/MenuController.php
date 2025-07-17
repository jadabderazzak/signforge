<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller responsible for rendering menus (public and authenticated).
 *
 * Contains actions for displaying the public menu (accessible to all)
 * and the authenticated menu (visible to logged-in users).
 */
final class MenuController extends AbstractController
{
    /**
     * Displays the main public menu for all users (unauthenticated).
     *
     * @return Response
     */
    #[Route('/menu', name: 'app_menu')]
    /**
     * Displays the main public menu.
     *
     * Typically shown on the homepage or before user authentication.
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('menu/index.html.twig');
    }

    
    #[Route('/menu_authenticated', name: 'app_menu_authenticated')]
    /**
     * Displays the menu intended for authenticated users.
     *
     * Typically shown after login, may include dashboard or admin sections.
     *
     * @return Response
     */
    public function authenticated(): Response
    {
        return $this->render('menu/menu_auth.html.twig');
    }
}
