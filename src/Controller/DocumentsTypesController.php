<?php

namespace App\Controller;

use DateTime;
use App\Entity\TypeDocument;
use App\Form\DocumenttypeType;
use App\Service\HtmlSanitizer;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TypeDocumentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted("ROLE_USER")]
/**
 * Controller responsible for displaying available document types.
 *
 * Access restricted to users with ROLE_USER.
 */
final class DocumentsTypesController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {}
    #[Route('/documents/types', name: 'app_documents_types')]
    /**
     * Displays the list of all document types available in the system.
     *
     * @param TypeDocumentRepository $repoTypes
     * @return Response
     */
    public function index(TypeDocumentRepository $repoTypes): Response
    {
        $types = $repoTypes->findBy([]);
        return $this->render('documents_types/index.html.twig', [
            'types' => $types,
        ]);
    }
    #[Route('/document/type/add', name: 'app_document_type_add')]
    /**
     * Handles the creation of a new document type.
     *
     * - Displays a form to create a new document type.
     * - Sanitizes user input before persisting.
     * - Saves the type to the database.
     * - Redirects with a flash message on success.
     *
     * @param Request $request The HTTP request.
     * @param HtmlSanitizer $sanitizer Service to purify HTML input.
     * @param EntityManagerInterface $manager Doctrine entity manager for persistence.
     * @return Response
     */

    public function add(Request $request, HtmlSanitizer $sanitizer, EntityManagerInterface $manager): Response
    {
        $type = new TypeDocument();
        $form = $this->createForm(DocumenttypeType::class, $type);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            
            // 🛡️ Sanitize the name to remove any unwanted HTML or script injection
            $type->setName($sanitizer->purify($type->getName()));

            // 🛡️ Sanitize the label to ensure it's clean before saving
            $type->setLabel($sanitizer->purify($type->getLabel()));

            // 📅 Set the creation date of the document type
            $type->setCreatedAt(new \DateTime());

            // 💾 Persist and save the new document type in the database
            $manager->persist($type);
            $manager->flush();

            // ✅ Notify the user of success
            $this->addFlash("success", $this->translator->trans("Document type added successfully."));

            // 🔁 Redirect back to the document types list
            return $this->redirectToRoute("app_documents_types");
        }
        return $this->render('documents_types/add.html.twig', [
            'form' => $form->createView(),
            'update' => false
        ]);
    }
}
