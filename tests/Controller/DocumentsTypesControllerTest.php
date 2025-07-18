<?php

namespace App\Tests\Controller;

use App\Entity\TypeDocument;
use App\Repository\TypeDocumentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class DocumentsTypesControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $users;
    private TypeDocumentRepository $types;
    private object $testUser;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container    = self::getContainer();
        $this->users  = $container->get(UserRepository::class);
        $this->types  = $container->get(TypeDocumentRepository::class);

        // Log in as an existing user
        $this->testUser = $this->users->findOneByEmail('admin@admin.com');
        $this->client->loginUser($this->testUser);
    }

    public function testIndexPageListsTypes(): void
    {
        $crawler = $this->client->request('GET', '/documents/types');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table'); // assuming you render a table of types
    }

    public function testAddTypeIncreasesDatabaseCount(): void
    {
        $before = count($this->types->findAll());

        // GET the “add” form
        $crawler = $this->client->request('GET', '/document/type/add');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="documenttype"]');

        // Submit the form
        $form = $crawler->filter('form[name="documenttype"]')->form([
            'documenttype[name]'  => 'Custom Invoice',
            'documenttype[label]' => 'INV',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/documents/types');
        $this->client->followRedirect();

        $after = count($this->types->findAll());
        $this->assertSame($before + 1, $after);

        // Verify the saved type
        $saved = $this->types->findOneBy(['name' => 'Custom Invoice']);
        $this->assertInstanceOf(TypeDocument::class, $saved);
        $this->assertSame('INV', $saved->getLabel());
        $this->assertNotNull($saved->getCreatedAt());
    }

   public function testAddTypeShowsValidationErrors(): void
{
    // Submit empty form and expect it to re-render the form
    $crawler = $this->client->request('GET', '/document/type/add');
    $form    = $crawler->filter('form[name="documenttype"]')->form([
        'documenttype[name]'  => '',
        'documenttype[label]' => '',
    ]);

    $this->client->submit($form);
    $this->assertResponseStatusCodeSame(200);
    // The form should still be displayed for corrections
    $this->assertSelectorExists('form[name="documenttype"]');
}

}
