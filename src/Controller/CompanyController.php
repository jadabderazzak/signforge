<?php

namespace App\Controller;

use App\Entity\Company;
use App\Form\CompanyType;
use App\Service\HtmlSanitizer;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted("ROLE_USER")]
final class CompanyController extends AbstractController
{
    #[Route('/company', name: 'app_company')]
    /**
     * Displays the current user's company information.
     *
     * If the user has no company registered, redirects them to the company creation form.
     *
     * @param CompanyRepository $repoCompany
     * @return Response
     */
    public function index(CompanyRepository $repoCompany): Response
    {
        $company = $repoCompany->findOneBy([
            'user' => $this->getUser()
        ]);

        if(!$company){
            return $this->redirectToRoute("app_company_add");
        }

        return $this->render('company/index.html.twig', [
            'company' => $company
        ]);
    }

    #[Route('/company/add', name: 'app_company_add')]
    /**
     * Handles creation of a new company for the current user.
     *
     * - Displays the company creation form.
     * - Sanitizes all input fields using HtmlSanitizer.
     * - Validates and uploads a logo if provided.
     * - Persists the company to the database.
     * - Redirects to the company dashboard after success.
     *
     * @param Request $request
     * @param HtmlSanitizer $sanitizer
     * @param EntityManagerInterface $manager
     * @return Response
     */

    public function add(Request $request, HtmlSanitizer $sanitizer, EntityManagerInterface $manager): Response
    {
        $company = new Company();
        // ✅ Set default text of additional information if empty
       if (empty($company->getBankDetails())) {
        $company->setBankDetails(
            'Due within 30 days. Currency: GBP. <br>' .
            'Bank Details  <br>
             Bank: HSBC Bank  <br>
            Account Number: 98765432109876543210  <br>
            IBAN: GB29HBUK12345698765432  <br>
            SWIFT/BIC: HBUKGB4B  <br>'.
            'Please ensure payment to the correct bank account as stated above. <br>' .
            'Late payment may incur additional charges.'
        );
        }
        $form = $this->createForm(CompanyType::class, $company);
        $extentions = ['jpeg', 'jpg', 'png', 'JPEG', 'JPG', 'PNG','svg','SVG'];
       
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $company = $form->getData();
            // ✅ Sanitize all user-input fields before saving
            // This ensures that malicious HTML, scripts, or unexpected tags are removed or cleaned

            // Company name – plain text input (no HTML expected, but we sanitize to be safe)
            $company->setName($sanitizer->purify($company->getName()));

            // Currency– plain text input (no HTML expected, but we sanitize to be safe)
            $company->setCurrency($sanitizer->purify($company->getCurrency()));

            // Address – plain text input (no HTML expected, but we sanitize to be safe)
            $company->setAdress($sanitizer->purify($company->getAdress()));

            // Phone – plain text input (no HTML expected)
            $company->setPhone($sanitizer->purify($company->getPhone()));
            
            // Footer – CKEditor field, may contain multiple lines and formatted content
             $company->setFooter($sanitizer->purify($company->getFooter()));
            // Tax Identification – may be plain string, but sanitize in case of injected input
            $company->setTaxIdentification($sanitizer->purify($company->getTaxIdentification()));

            // Registration Number – plain string (e.g. "RC12345"), still sanitized for safety
            $company->setRegistrationNumber($sanitizer->purify($company->getRegistrationNumber()));

            // Bank details – CKEditor field, may contain multiple lines and formatted content
            $company->setBankDetails($sanitizer->purify($company->getBankDetails()));

            $logo = $request->files->get('file');
            if ($logo) {
                $uploads_directory = $this->getParameter(
                    'logos_directory'
                );
                // Extension verification
                $extension = $logo->guessExtension();
                if (!in_array($extension, $extentions)) {
                    $this->addFlash('danger', '❌ Invalid logo format. Only JPG and PNG files are allowed.');
                    return $this->redirectToRoute('app_company_add');
                }

                // Size verification
                if (filesize($logo) > 5000000) {
                    $this->addFlash('danger', '❌ Logo is too large. Maximum allowed size is 5MB.');
                     return $this->redirectToRoute('app_company_add');
                }
                    if ($company->getLogo()) {
                        $fs = new Filesystem();
                        $fs->remove(
                            $uploads_directory . '/' . $company->getLogo()
                        );
                    }
                
                    if (
                        filesize($logo) < 50000000 &&
                        in_array($logo->guessExtension(), $extentions)
                    ) {

                        $filename =
                            md5(\uniqid()) . '.' . $logo->guessExtension();
                        $logo->move($uploads_directory, $filename); 
                        $company->setLogo($filename);
                     }

            }
            $company->setUser($this->getUser());
            
            // ✅ Persist and flush the sanitized Company entity into the database

            $manager->persist($company); // Stage the entity for insertion (or update if it already exists)
            $manager->flush(); // Commit the changes to the database

            // ✅ Notify the user that the company has been successfully created
            $this->addFlash("success", "✅ The company has been successfully registered.");

            // ✅ Redirect to the company listing or dashboard route
            return $this->redirectToRoute("app_company");

        }
        return $this->render('company/add.html.twig', [
            'form' => $form
        ]);
    }


     #[Route('/company/update/{slug}', name: 'app_company_update')]
     /**
     * Handles update of an existing company by slug.
     *
     * - Loads the company for the given slug.
     * - Displays the company form pre-filled.
     * - Sanitizes all input fields using HtmlSanitizer.
     * - Replaces the logo if a new file is uploaded and valid.
     * - Saves all changes to the database.
     * - Redirects to the company dashboard after success.
     *
     * @param CompanyRepository $repoCompany
     * @param Request $request
     * @param HtmlSanitizer $sanitizer
     * @param EntityManagerInterface $manager
     * @return Response
     */

    public function update(CompanyRepository $repoCompany, Request $request, HtmlSanitizer $sanitizer, EntityManagerInterface $manager): Response
    {
       $company = $repoCompany->findOneBy([
        'slug' => $request->get('slug')
       ]);

       if(!$company){
        $this->addFlash("danger", "❌ No company found for your account.");
        return $this->redirectToRoute("app_board");
       }
       // ✅ Set default text of additional information if empty
       if (empty($company->getBankDetails())) {
        $company->setBankDetails(
            'Due within 30 days. Currency: GBP. <br>' .
            'Bank Details  <br>
             Bank: HSBC Bank  <br>
            Account Number: 98765432109876543210  <br>
            IBAN: GB29HBUK12345698765432  <br>
            SWIFT/BIC: HBUKGB4B  <br>'.
            'Please ensure payment to the correct bank account as stated above. <br>' .
            'Late payment may incur additional charges.'
        );
        }
        $form = $this->createForm(CompanyType::class, $company);
        $extentions = ['jpeg', 'jpg', 'png', 'JPEG', 'JPG', 'PNG'];
       
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $company = $form->getData();
            // ✅ Sanitize all user-input fields before saving
            // This ensures that malicious HTML, scripts, or unexpected tags are removed or cleaned

            // Company name – plain text input (no HTML expected, but we sanitize to be safe)
            $company->setName($sanitizer->purify($company->getName()));

            // Address – plain text input (no HTML expected, but we sanitize to be safe)
            $company->setAdress($sanitizer->purify($company->getAdress()));

            // Currency– plain text input (no HTML expected, but we sanitize to be safe)
            $company->setCurrency($sanitizer->purify($company->getCurrency()));

            // Phone – plain text input (no HTML expected)
            $company->setPhone($sanitizer->purify($company->getPhone()));
            
            // Footer – CKEditor field, may contain multiple lines and formatted content
             $company->setFooter($sanitizer->purify($company->getFooter()));
            // Tax Identification – may be plain string, but sanitize in case of injected input
            $company->setTaxIdentification($sanitizer->purify($company->getTaxIdentification()));

            // Registration Number – plain string (e.g. "RC12345"), still sanitized for safety
            $company->setRegistrationNumber($sanitizer->purify($company->getRegistrationNumber()));

            // Bank details – CKEditor field, may contain multiple lines and formatted content
            $company->setBankDetails($sanitizer->purify($company->getBankDetails()));

            $logo = $request->files->get('file');
            if ($logo) {
                $uploads_directory = $this->getParameter(
                    'logos_directory'
                );
                // Extension verification
                $extension = $logo->guessExtension();
                if (!in_array($extension, $extentions)) {
                    $this->addFlash('danger', '❌ Invalid logo format. Only JPG and PNG files are allowed.');
                    return $this->redirectToRoute('app_company_update', [
                        'slug' => $company->getSlug()
                    ]);
                }

                // Size verification
                if (filesize($logo) > 5000000) {
                    $this->addFlash('danger', '❌ Logo is too large. Maximum allowed size is 5MB.');
                     return $this->redirectToRoute('app_company_update', [
                        'slug' => $company->getSlug()
                    ]);
                }


                    if ($company->getLogo()) {
                        $fs = new Filesystem();
                        $fs->remove(
                            $uploads_directory . '/' . $company->getLogo()
                        );
                    }
                
                    if (
                        filesize($logo) < 50000000 &&
                        in_array($logo->guessExtension(), $extentions)
                    ) {

                        $filename =
                            md5(\uniqid()) . '.' . $logo->guessExtension();
                        $logo->move($uploads_directory, $filename); 
                        $company->setLogo($filename);
                     }

            }
            $company->setUser($this->getUser());
            
            // ✅ Persist and flush the sanitized Company entity into the database

           
            $manager->flush(); // Commit the changes to the database

            // ✅ Notify the user that the company has been successfully created
            $this->addFlash("success", "✅ The company has been successfully updated.");

            // ✅ Redirect to the company listing or dashboard route
            return $this->redirectToRoute("app_company");

        }
        return $this->render('company/add.html.twig', [
            'form' => $form
        ]);
    }


    
}
