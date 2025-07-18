<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private $client;
    private UserRepository $users;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container   = self::getContainer();
        $this->users = $container->get(UserRepository::class);
        $this->em    = $container->get(EntityManagerInterface::class);
    }

    public function testLoginPageIsSuccessful(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        // Just ensure there's a form on the page
        $this->assertSelectorExists('form');
    }

    public function testRegisterPageIsSuccessful(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="register"]');
    }

    public function testRegisterPasswordMismatchShowsError(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->filter('form[name="register"]')->form([
            'register[name]'             => 'Tester',
            'register[email]'            => 'tester1@example.com',
            'register[password]'         => 'password123',
            'register[confirm_password]' => 'different456',
        ]);

        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(200);

        // Check that the error text appears somewhere in the response
        $content = $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Passwords do not match.', $content);
    }

    public function testRegisterSuccessCreatesUserAndRedirects(): void
    {
        $email  = 'unique'.uniqid().'@example.com';
        $before = count($this->users->findAll());

        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->filter('form[name="register"]')->form([
            'register[name]'             => 'NewUser',
            'register[email]'            => $email,
            'register[password]'         => 'securePass1',
            'register[confirm_password]' => 'securePass1',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();

        $after = count($this->users->findAll());
        $this->assertSame($before + 1, $after);

        $user = $this->users->findOneBy(['email' => $email]);
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('NewUser', $user->getName());
    }
}
