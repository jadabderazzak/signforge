<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Service\HtmlSanitizer;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted("ROLE_USER")]
/**
 * Controller for managing client entities (listing, creation, and updating).
 *
 * All actions require the user to have ROLE_USER.
 */
final class ClientController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {}

    #[Route('/client', name: 'app_client')]
    /**
     * Displays the list of all clients.
     *
     * @param ClientRepository $repoClient
     * @return Response
     */

    public function index(ClientRepository $repoClient): Response
    {
        $clients = $repoClient->findBy([
            'user' => $this->getUser()
        ]);
        return $this->render('client/index.html.twig', [
            'clients' => $clients,
        ]);
    }

     #[Route('/client/add', name: 'app_client_add')]
     /**
     * Handles the creation of a new client.
     *
     * - Displays a form to add a client.
     * - Sanitizes all user input using HtmlSanitizer.
     * - Persists the new client to the database.
     * - Shows a flash message and redirects to the client list on success.
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param HtmlSanitizer $sanitizer
     * @return Response
     */
    public function add(Request $request, EntityManagerInterface $manager, HtmlSanitizer $sanitizer): Response
    {
        $client = new Client();
       
        $form = $this->createForm(ClientType::class, $client);
         $form->handleRequest($request);

       if ($form->isSubmitted() && $form->isValid()) {
        // Get submitted Client entity from the form
        $client = $form->getData();

        // ✅ Sanitize all user-submitted fields to prevent unwanted HTML or scripts
        $client->setName($sanitizer->purify($client->getName()));
        $client->setCompany($sanitizer->purify($client->getCompany()));
        $client->setAdress($sanitizer->purify($client->getAdress()));
        $client->setEmail($sanitizer->purify($client->getEmail()));
        $client->setTaxNumber($sanitizer->purify($client->getTaxNumber()));
        $client->setUser($this->getUser());
        // ✅ Persist the new client to the database
        $manager->persist($client);
        $manager->flush();

        // ✅ Flash message to confirm success
        $this->addFlash("success",  $this->translator->trans("New client has been successfully added."));

        // ✅ Redirect to client list
        return $this->redirectToRoute("app_client");
    }

        return $this->render('client/add.html.twig', [
            'form' => $form,
            'update' => false
        ]);
    }

     #[Route('/client/update/{slug}', name: 'app_client_update')]
     /**
     * Handles the update of an existing client.
     *
     * - Loads the client by slug.
     * - Displays a pre-filled form.
     * - Sanitizes all input on submit.
     * - Saves changes to the database.
     * - Shows a flash message and redirects to the client list.
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param HtmlSanitizer $sanitizer
     * @param ClientRepository $repoClient
     * @return Response
     */

    public function update(Request $request, EntityManagerInterface $manager, HtmlSanitizer $sanitizer, ClientRepository $repoClient): Response
    {
        $client = $repoClient->findOneBy([
            'slug' => $request->get('slug')
        ]);

        if(!$client){
            $this->addFlash("danger", $this->translator->trans("This client does not exist."));
            return $this->redirectToRoute("app_client");
        }
       
        $form = $this->createForm(ClientType::class, $client);
         $form->handleRequest($request);

       if ($form->isSubmitted() && $form->isValid()) {
        // Get submitted Client entity from the form
        $client = $form->getData();

        // ✅ Sanitize all user-submitted fields to prevent unwanted HTML or scripts
        $client->setName($sanitizer->purify($client->getName()));
        $client->setCompany($sanitizer->purify($client->getCompany()));
        $client->setAdress($sanitizer->purify($client->getAdress()));
        $client->setEmail($sanitizer->purify($client->getEmail()));
        $client->setTaxNumber($sanitizer->purify($client->getTaxNumber()));

        // ✅ Persist the new client to the database
       
        $manager->flush();

        // ✅ Flash message to confirm success
        $this->addFlash("success", $this->translator->trans("New client has been successfully added."));

        // ✅ Redirect to client list
        return $this->redirectToRoute("app_client");
    }

        return $this->render('client/add.html.twig', [
            'form' => $form,
            'update' => true
        ]);
    }
}
