<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Service\HtmlSanitizer;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Controller managing user authentication and registration.
 *
 * Contains routes for login, logout, and account creation.
 */
class SecurityController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {}
    #[Route(path: '/login', name: 'app_login')]
    /**
     * Handles the login form display and error handling.
     *
     * - Redirects already authenticated users to the dashboard.
     * - Displays the login form with last entered username and error messages if any.
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_board');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    /**
     * Handles user logout.
     *
     * This method is never executed directly. Symfony intercepts the request
     * and logs the user out via the security firewall configuration.
     *
     * @throws \LogicException
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/register', name: 'app_register')]
    /**
     * Handles new user registration.
     *
     * - Displays the registration form.
     * - Validates password confirmation.
     * - Sanitizes user input to prevent XSS.
     * - Persists the new user if valid and unique.
     * - Adds success or error flash messages.
     *
     * @param HtmlSanitizer $sanitizer
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function register(HtmlSanitizer $sanitizer,Request $request, EntityManagerInterface $manager): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

       
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
            $password = $form->get('password')->getData();
            $confirmPassword = $form->get('confirm_password')->getData();

            if ($password !== $confirmPassword) {
                $form->get('confirm_password')->addError(new FormError('Passwords do not match.'));
            } else {

                // Sanitize raw input to prevent XSS attacks
                // Even though Symfony escapes output by default in Twig, we sanitize user input before persisting it
                // to ensure no malicious HTML, scripts, or unwanted tags are stored in the database.
                // This adds an additional layer of security and protects other parts of the system (e.g., APIs, exports, or email rendering).
                $nameRaw = $request->get('register')['name'] ?? '';
                $emailRaw = $request->get('register')['email'] ?? '';

                $cleanName = $sanitizer->purify($nameRaw);
                $cleanEmail = $sanitizer->purify($emailRaw);

                $user = $form->getData();
                $user->setName($cleanName);
                $user->setEmail($cleanEmail);

                try {
                    // Persist and save the new user to the database
                    // After sanitizing and validating the form, we persist the user entity and flush to commit the changes.
                    // A success flash message is then shown to inform the user of successful registration.

                    $manager->persist($user);
                    $manager->flush();

                    $this->addFlash("success", $this->translator->trans("Your account has been successfully created. You can now log in."));
                    return $this->redirectToRoute("app_login");
                } catch (UniqueConstraintViolationException $e) {
                    $this->addFlash("danger", $this->translator->trans("This email address is already in use."));
                }

                
            }
        }
        return $this->render('security/register.html.twig',[
            'form' => $form->createView()
        ]);
    }
}
