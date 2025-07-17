<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

    #[Route('/change/locales/{locale}', name: 'change_locales')]
        public function change_locales($locale, Request $request): Response
        {
          
            $session = $request->getSession();
            $session->set('_locale', $locale);
    
          $request->setLocale($session->get('_locale'));
          
    
           
    
            return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('app_home'));   
        }

}
