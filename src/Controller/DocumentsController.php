<?php

namespace App\Controller;

use Twig\Environment;
use App\Entity\Document;
use App\Service\PdfService;
use App\Entity\DocumentItem;
use App\Service\HtmlSanitizer;
use App\Repository\ClientRepository;
use App\Repository\CompanyRepository;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TypeDocumentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted("ROLE_USER")]
final class DocumentsController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {}
    #[Route('/documents', name: 'app_documents')]
    /**
     * Lists all documents for the currently logged-in user.
     *
     * @param DocumentRepository $repoDocument
     * @return Response
     */
    public function index(DocumentRepository $repoDocument, CompanyRepository $repoCompany, TypeDocumentRepository $repoType): Response
    {
        $documents = $repoDocument->findBy([
            'user' => $this->getUser()
        ],[
            'id' => "DESC"
        ]);
        $company = $repoCompany->findOneBy([
            'user' => $this->getUser()
        ]);
        $currency = $company ? $company->getCurrency() : '$';
        $types = $repoType->findBy([]);

        return $this->render('documents/index.html.twig', [
            'documents' => $documents,
            'currency' => $currency,
            'types' => $types
        ]);
    }

    #[Route('/document/{slug}', name: 'app_document_by_type')]
    /**
     * Displays all documents of a specific type for the currently logged-in user.
     *
     * This route expects a document type slug (e.g. "invoice", "quote") and filters
     * the list of documents accordingly. If the type is not found, the user is redirected
     * back to the full documents list with an error flash message.
     *
     * @param Request $request The HTTP request containing the slug parameter.
     * @param DocumentRepository $repoDocument The repository used to fetch documents.
     * @param CompanyRepository $repoCompany The repository used to retrieve the user's company (for currency).
     * @param TypeDocumentRepository $repoType The repository used to retrieve document types.
     *
     * @return Response The rendered view of filtered documents.
     */
    public function app_document_by_type(
        Request $request,
        DocumentRepository $repoDocument,
        CompanyRepository $repoCompany,
        TypeDocumentRepository $repoType
    ): Response {
        $typeDocument = $repoType->findOneBy([
            'slug' => $request->get('slug')
        ]);

        if (!$typeDocument) {
            $this->addFlash('danger', $this->translator->trans('Type Document not found.'));
            return $this->redirectToRoute('app_documents');
        }

        $documents = $repoDocument->findBy([
            'user' => $this->getUser(),
            'type' => $typeDocument
        ], [
            'id' => "DESC"
        ]);

        $company = $repoCompany->findOneBy([
            'user' => $this->getUser()
        ]);

        $types = $repoType->findBy([]);
        $currency = $company ? $company->getCurrency() : '$';
        return $this->render('documents/by_types.html.twig', [
            'documents' => $documents,
            'currency' => $currency,
            'types' => $types,
            'type' => $typeDocument->getLabel()
        ]);
    }


    #[Route('/document/preview/{slug}', name: 'app_document_preview')]
    /**
     * Displays the preview of a single document belonging to the logged-in user.
     *
     * @param Request $request
     * @param DocumentRepository $repoDocument
     * @param CompanyRepository $repoCompany
     * @return Response
     */
    public function preview(Request $request, DocumentRepository $repoDocument, CompanyRepository $repoCompany): Response
    {
        $document = $repoDocument->findOneBy([
            'user' => $this->getUser(),
            'slug' => $request->get('slug')
        ]);
        $company = $repoCompany->findOneBy([
            'user' => $this->getUser()
        ]);
        $currency = $company ? $company->getCurrency() : '$';
        return $this->render('documents/preview.html.twig', [
            'document' => $document,
            'currency' => $currency,
            'company' => $company
        ]);
    }


    #[Route('/document/validate/{slug}', name: 'app_document_validate')]
    /**
     * Validates a draft document and sets its status to "validated".
     *
     * This action is only available for the currently logged-in user.
     * Once validated, the document status changes from draft (false) to true.
     * A success flash message is shown and the user is redirected to the documents list.
     *
     * @param Request $request
     * @param DocumentRepository $repoDocument
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function validate(Request $request, DocumentRepository $repoDocument, EntityManagerInterface $manager): Response
    {
        $document = $repoDocument->findOneBy([
            'user' => $this->getUser(),
            'slug' => $request->get('slug')
        ]);

        $document->setStatus(true);
        $manager->flush();
        $this->addFlash('success', $this->translator->trans('The document has been successfully validated.'));
        return $this->redirectToRoute("app_documents");
    }

    #[Route('/documents/{slug}/pdf', name: 'app_documents_pdf')]
    /**
     * Generates and displays the PDF version of a document for the current user.
     *
     * Renders the document as a PDF using a Twig template and a custom PdfService.
     *
     * @param Request $request
     * @param DocumentRepository $repoDoc
     * @param PdfService $pdfService
     * @param Environment $twig
     * @param CompanyRepository $repoCompany
     * @return Response
     */
    public function pdf(Request $request, DocumentRepository $repoDoc, PdfService $pdfService, Environment $twig, CompanyRepository $repoCompany): Response
    {
        $document = $repoDoc->findOneBy([
        'slug' => $request->get('slug'),
        'user' => $this->getUser()
        ]);

        if (!$document) {
            $this->addFlash('danger', $this->translator->trans('Document not found.'));
            return $this->redirectToRoute('app_documents');
        }

        $company = $repoCompany->findOneBy([
            'user' => $this->getUser()
        ]);

        if (!$company) {
            $this->addFlash('danger', $this->translator->trans('Company not found.'));
            return $this->redirectToRoute('app_documents');
        }

        $currency = $company ? $company->getCurrency() : '$';

        $html = $twig->render('documents/pdf.html.twig', [
            'document' => $document,
            'company' => $company,
            'currency' => $currency,
        ]);

        $pdfOutput = $pdfService->generatePdf($html, $company->getFooter());

        return new Response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
        ]);
    }

    #[Route('/documents/add', name: 'app_documents_add')]
    /**
     * Displays the document creation form for the current user.
     *
     * Fetches all available document types and clients. Requires that the user's company is created.
     * If the company is not found, the user is redirected to the company creation page.
     *
     * @param TypeDocumentRepository $repoTypes
     * @param CompanyRepository $repoCompany
     * @param ClientRepository $repoClient
     * @return Response
     */
    public function add(TypeDocumentRepository $repoTypes, CompanyRepository $repoCompany, ClientRepository $repoClient): Response
    {
        $company = $repoCompany->findOneBy([
            'user' => $this->getUser()
        ]);

        if (!$company) {
            $this->addFlash('warning', $this->translator->trans('Please create your company before adding a document.'));
            return $this->redirectToRoute("app_company_add");
        }

        $types = $repoTypes->findBy([]);
        $clients = $repoClient->findBy([
            'user' => $this->getUser()
        ]);
        $currency = $company ? $company->getCurrency() : '$';
        return $this->render('documents/add.html.twig', [
            'types' => $types,
            'currency' => $currency,
            'company' => $company,
            'clients' => $clients
        ]);
    }

    #[Route('/documents/create', name: 'app_documents_create', methods: ['POST'])]
    /**
     * Handles the creation of a new document via POST request (AJAX).
     *
     * Steps:
     * 1. Validates essential form fields (client ID, document number, document type).
     * 2. Checks if a document with the same number already exists for the current user.
     * 3. Validates the presence and structure of document items.
     * 4. Fetches the client entity from the database.
     * 5. Fetches the document type entity from the database.
     * 6. Creates a new Document entity and persists it.
     * 7. Creates and persists associated DocumentItem entities with calculation of total.
     * 8. Sets the total on the document and flushes everything to the database.
     *
     * Returns a JSON response indicating success or detailed error messages in case of failure.
     *
     * @param Request $request The HTTP request containing form data.
     * @param EntityManagerInterface $manager The entity manager to persist data.
     * @param HtmlSanitizer $sanitiser HTML sanitizer to clean input fields.
     * @param ClientRepository $repoClient Repository to fetch client entity.
     * @param TypeDocumentRepository $repoType Repository to fetch document type entity.
     * @param DocumentRepository $repoDocument Repository to check for existing documents.
     *
     * @return JsonResponse JSON response with success or error message.
     */
    public function create(
        Request $request,
        EntityManagerInterface $manager,
        HtmlSanitizer $sanitiser,
        ClientRepository $repoClient,
        TypeDocumentRepository $repoType,
        DocumentRepository $repoDocument
    ): JsonResponse {
        // Step 1: Validate essential form fields
        $clientId = (int)$request->get('client_id');
        $documentNumber = $sanitiser->purify($request->get('document_number'));
        $documentType = (int)$request->get('document_type');

        if (!$clientId || !$documentNumber || !$documentType) {
            return new JsonResponse(['error' => $this->translator->trans('Please fill in all required fields: client, document number and type.')], 400);
        }

        // Step 2: Cheking if Document number exists in the database

        $document = $repoDocument->findOneBy([
            'documentNumber' => $documentNumber,
            'user' => $this->getUser()
        ]);

        if ($document) {
            return new JsonResponse(['error' => $this->translator->trans('A document with this number already exists. Please choose a unique document number.')], 400);
        }

        // Step 3: Retrieve and validate item list
        $items = $request->request->all('items');
        if (!$items || !is_array($items)) {
            return new JsonResponse(['error' => $this->translator->trans('At least one item must be provided in the document.')], 400);
        }

        foreach ($items as $item) {
            // Validate description and numeric fields
            if (empty($item['description'])) {
                return new JsonResponse(['error' => $this->translator->trans('Each item must include a description.')], 400);
            }
            if (!is_numeric($item['qty']) || !is_numeric($item['unit_price'])) {
                return new JsonResponse(['error' => $this->translator->trans('Quantity and unit price must be valid numeric values.')], 400);
            }
        }

        // Step 4: Fetch client from database
        $client = $repoClient->find($clientId);
        if (!$client) {
            return new JsonResponse(['error' => $this->translator->trans('Selected client was not found. Please refresh the page and try again.')], 400);
        }

        // Step 5: Fetch document type
        $type = $repoType->find($documentType);
        if (!$type) {
            return new JsonResponse(['error' => $this->translator->trans('Selected document type is invalid.')], 400);
        }

        // Step 6: Create and persist the Document entity
        $document = new Document();
        $document->setClient($client)
                ->setUser($this->getUser())
                ->setStatus(false)
                ->setDocumentNumber($documentNumber) // Sanitize input
                ->setType($type)
                ->setCreatedAt(new \DateTime());

        $manager->persist($document);

        // Step 7: Create and persist each DocumentItem
        $total_for_document = 0 ;
        foreach ($items as $item) {
            $description = $sanitiser->purify($item['description']);
            $qty = (float)$item['qty'];
            $unitPrice = (float)$item['unit_price'];
            $discount = (float)($item['discount'] ?? 0);
            $tax = (float)($item['tax'] ?? 0);

            // 💰 Calculate total = (qty * unitPrice) - discount + tax
            $total = ($unitPrice * $qty) * (1 - $discount / 100) * (1 + $tax / 100);
            $total_for_document += $total;
            $docItem = new DocumentItem();
            $docItem->setDocument($document)
                ->setTitle($description)
                ->setQuantity($qty)
                ->setUnitPrice($unitPrice)
                ->setDiscount($discount)
                ->setTaxe($tax)
                
                ->setTotal($total);

            $manager->persist($docItem);
        }
        // Update total for document 
        $document->setTotal($total_for_document);
        // Step 8: Finalize and save to database
        $manager->flush();

        return new JsonResponse(['success' => true]);
    }


}
