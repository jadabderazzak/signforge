<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use App\Repository\CompanyRepository;
use App\Repository\DocumentRepository;
use App\Repository\TypeDocumentRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[IsGranted("ROLE_USER")]
/**
 * Controller for displaying the user dashboard (board).
 *
 * Accessible only to authenticated users with ROLE_USER.
 */
final class BoardController extends AbstractController
{
 #[Route('/board', name: 'app_board')]
    /**
     * Displays the main dashboard for the currently logged-in user.
     *
     * The dashboard shows key statistics such as total documents, total clients,
     * pending invoices, total revenue, and recent documents. It also includes
     * graphical data such as revenue by month and documents by type.
     * 
     * Additionally, it verifies whether the user has completed the initial setup
     * steps (creating a company and adding at least one client), and sends that
     * state to the template for onboarding display.
     *
     * @param DocumentRepository $documentRepository Repository to access documents.
     * @param ClientRepository $clientRepository Repository to count total clients.
     * @param ClientRepository $repoClient Redundant repository for onboarding check (same as $clientRepository).
     * @param CompanyRepository $repoCompany Repository to retrieve the user's company.
     *
     * @return Response
     */
    public function index(
        DocumentRepository $documentRepository,
        ClientRepository $clientRepository,
        CompanyRepository $repoCompany
    ): Response {
        // Basic dashboard statistics
        $stats = [
            'totalDocuments' => $documentRepository->count([
                'user' => $this->getUser()
            ]),
            'totalClients' => $clientRepository->count([
                'user' => $this->getUser()
            ]),
            'pendingInvoices' => $documentRepository->count([
                'status' => false,
                'user' => $this->getUser()
            ]),
            'totalRevenue' => $documentRepository->getTotalRevenue($this->getUser()),
        ];

        // Retrieve the user's company (if it exists)
        $company = $repoCompany->findOneBy([
            'user' => $this->getUser()
        ]);

        // Count the number of clients (for onboarding display)
        $clients = $clientRepository->count([
            'user'=> $this->getUser()
        ]);

        // Determine currency symbol or fallback to default
        $symbol = $company ? $company->getCurrency() : '$';

        // Data for document type distribution (e.g. invoice, quote)
        $documentTypes = $documentRepository->getCountByType($this->getUser());
  
        // Data for revenue over the past 12 months
        $monthlyRevenue = $documentRepository->getMonthlyRevenue($this->getUser());

        // Render the dashboard template with all data
        return $this->render('board/index.html.twig', [
            'recentDocuments' => $documentRepository->findRecentDocuments(5, $this->getUser()),
            'stats' => $stats,
            'documentTypes' => $documentTypes,
            'monthlyRevenue' => $monthlyRevenue,
            'symbol' => $symbol,
            'company' => $company ? true : false, // used for onboarding block
            'clients' => $clients ? true : false, // used for onboarding block
        ]);
    }

    
  
}
