<?php

namespace App\Tests\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ClientControllerTest extends WebTestCase
{
    private KernelBrowser    $http;
    private UserRepository   $users;
    private ClientRepository $clients;
    private object           $testUser;

    protected function setUp(): void
    {
        $this->http    = static::createClient();
        $container     = self::getContainer();
        $this->users   = $container->get(UserRepository::class);
        $this->clients = $container->get(ClientRepository::class);

        // assume an “admin@admin.com” user fixture exists
        $this->testUser = $this->users->findOneByEmail('admin@admin.com');
        $this->http->loginUser($this->testUser);
    }

    public function testIndexPageShowsClientList(): void
    {
        $crawler = $this->http->request('GET', '/client');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');                // expecting a table of clients
    }

    public function testAddClientIncreasesDatabaseCount(): void
{
    // Count before
    $before = count($this->clients->findBy(['user' => $this->testUser]));

    // Submit the add form
    $crawler = $this->http->request('GET', '/client/add');
    $form = $crawler->filter('form[name="client"]')->form([
        'client[name]'      => 'New Client',
        'client[company]'   => 'Test Co',
        'client[adress]'    => '123 St',
        'client[email]'     => 'new@example.com',
        'client[taxNumber]' => 'TAX-111',
    ]);
    $this->http->submit($form);
    $this->assertResponseRedirects('/client');
    $this->http->followRedirect();

    // Count after
    $after = count($this->clients->findBy(['user' => $this->testUser]));

    // Exactly one new record
    $this->assertSame($before + 1, $after);

    // Additionally verify the specific record exists
    $saved = $this->clients->findOneBy([
        'user'  => $this->testUser,
        'email' => 'new@example.com'
    ]);
    $this->assertInstanceOf(Client::class, $saved);
    $this->assertSame('New Client', $saved->getName());
}


    public function testUpdateNonexistentClientRedirectsWithoutChange(): void
    {
        $countBefore = count($this->clients->findBy(['user' => $this->testUser]));
        $this->http->request('GET', '/client/update/non-existent-slug');
        $this->assertResponseRedirects('/client');
        $this->http->followRedirect();

        $countAfter = count($this->clients->findBy(['user' => $this->testUser]));
        $this->assertSame($countBefore, $countAfter);
    }

    public function testUpdateExistingClientPersistsChanges(): void
    {
        // Create and persist a client fixture
        $client = new Client();
        $client->setName('Orig')
               ->setCompany('OrigCo')
               ->setAdress('Orig Addr')
               ->setEmail('orig@example.com')
               ->setTaxNumber('TAX-000')
               ->setUser($this->testUser);
        $em = self::getContainer()->get('doctrine')->getManager();
        $em->persist($client);
        $em->flush();

        // submit the update form
        $crawler = $this->http->request('GET', '/client/update/'.$client->getSlug());
        $form = $crawler->filter('form[name="client"]')->form([
            'client[name]'      => 'Updated',
            'client[company]'   => 'UpdatedCo',
            'client[adress]'    => 'New Addr',
            'client[email]'     => 'updated@example.com',
            'client[taxNumber]' => 'TAX-999',
        ]);

        $this->http->submit($form);
        $this->assertResponseRedirects('/client');
        $this->http->followRedirect();

        // reload and check
        $refreshed = $this->clients->find($client->getId());
        $this->assertSame('Updated',              $refreshed->getName());
        $this->assertSame('UpdatedCo',            $refreshed->getCompany());
        $this->assertSame('New Addr',             $refreshed->getAdress());
        $this->assertSame('updated@example.com',  $refreshed->getEmail());
        $this->assertSame('TAX-999',              $refreshed->getTaxNumber());
    }
}
