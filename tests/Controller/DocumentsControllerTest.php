<?php

namespace App\Tests\Controller;

use App\Entity\Company;
use App\Entity\Document;
use App\Entity\TypeDocument;
use App\Repository\ClientRepository;
use App\Repository\CompanyRepository;
use App\Repository\DocumentRepository;
use App\Repository\TypeDocumentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DocumentsControllerTest extends WebTestCase
{
    private KernelBrowser          $client;
    private UserRepository         $users;
    private ClientRepository       $clients;
    private CompanyRepository      $companies;
    private TypeDocumentRepository $types;
    private DocumentRepository     $docs;
    private EntityManagerInterface $em;
    private object                 $testUser;

    protected function setUp(): void
    {
        $this->client    = static::createClient();
        $c               = self::getContainer();
        $this->users     = $c->get(UserRepository::class);
        $this->clients   = $c->get(ClientRepository::class);
        $this->companies = $c->get(CompanyRepository::class);
        $this->types     = $c->get(TypeDocumentRepository::class);
        $this->docs      = $c->get(DocumentRepository::class);
        $this->em        = $c->get(EntityManagerInterface::class);

        $this->testUser = $this->users->findOneByEmail('admin@admin.com');
        $this->client->loginUser($this->testUser);
    }

    public function testIndexListsDocuments(): void
    {
        $crawler = $this->client->request('GET', '/documents');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    public function testByTypeRedirectsOnInvalidSlug(): void
    {
        $this->client->request('GET', '/document/does-not-exist');
        $this->assertResponseRedirects('/documents');
    }

    public function testByTypeFiltersCorrectly(): void
    {
        $type = (new TypeDocument())
            ->setName('Foo')
            ->setLabel('FOO')
            ->setCreatedAt(new \DateTime())
            ->setSlug('foo');
        $this->em->persist($type);

        $doc = (new Document())
            ->setUser($this->testUser)
            ->setClient($this->clients->findOneBy(['user' => $this->testUser]))
            ->setType($type)
            ->setCreatedAt(new \DateTime())
            ->setStatus(false)
            ->setDocumentNumber('DOC-XYZ')
            ->setSlug('doc-xyz')
            ->setTotal(0.0);
        $this->em->persist($doc);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/document/'.$type->getSlug());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('table', 'DOC-XYZ');
    }

    public function testPreviewPageRenders(): void
    {
        $company = new Company();
        $company->setName('TestCo')
                ->setSlug('testco')
                ->setCurrency('USD')
                ->setUser($this->testUser);
        $this->em->persist($company);
        $this->em->flush();

        $type   = $this->types->findAll()[0];
        $client = $this->clients->findOneBy(['user' => $this->testUser]);
        $doc    = (new Document())
            ->setUser($this->testUser)
            ->setClient($client)
            ->setType($type)
            ->setCreatedAt(new \DateTime())
            ->setStatus(false)
            ->setDocumentNumber('PREVIEW123')
            ->setSlug('preview123')
            ->setTotal(0.0);
        $this->em->persist($doc);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/document/preview/'.$doc->getSlug());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $type->getLabel());
    }

    public function testValidateSetsStatusTrue(): void
    {
        $type   = $this->types->findAll()[0];
        $client = $this->clients->findOneBy(['user' => $this->testUser]);
        $doc    = (new Document())
            ->setUser($this->testUser)
            ->setClient($client)
            ->setType($type)
            ->setCreatedAt(new \DateTime())
            ->setStatus(false)
            ->setDocumentNumber('VAL123')
            ->setSlug('val123')
            ->setTotal(0.0);
        $this->em->persist($doc);
        $this->em->flush();

        $this->client->request('GET', '/document/validate/'.$doc->getSlug());
        $this->assertResponseRedirects('/documents');
        $reloaded = $this->docs->find($doc->getId());
        $this->assertTrue($reloaded->isStatus());
    }

    public function testPdfRedirectsWhenMissingDocOrCompany(): void
    {
        $this->client->request('GET', '/documents/unknown/pdf');
        $this->assertResponseRedirects('/documents');
    }

    public function testAddRedirectsWhenNoCompany(): void
    {
        foreach ($this->companies->findBy(['user'=>$this->testUser]) as $c) {
            $this->em->remove($c);
        }
        $this->em->flush();

        $this->client->request('GET', '/documents/add');
        $this->assertResponseRedirects('/company/add');
    }

    public function testCreateJsonVariousErrorsAndSuccess(): void
    {
        $this->client->request('POST', '/documents/create', [], [], ['CONTENT_TYPE'=>'application/json'], json_encode([]));
        $this->assertResponseStatusCodeSame(400);

        $payload = ['client_id'=> 0, 'document_number'=>'D1','document_type'=>0, 'items'=>[['description'=>'','qty'=>'a','unit_price'=>'b']]];
        $this->client->request('POST','/documents/create',[],[],['CONTENT_TYPE'=>'application/json'], json_encode($payload));
        $this->assertResponseStatusCodeSame(400);

        $clientEntity = $this->clients->findOneBy(['user'=>$this->testUser]);
        $typeEntity   = $this->types->findAll()[0];
        $payload = [
            'client_id'       => $clientEntity->getId(),
            'document_number' => 'JS-'.uniqid(),
            'document_type'   => $typeEntity->getId(),
            'items'           => [['description'=>'Item 1','qty'=>1,'unit_price'=>10]],
        ];
        $this->client->request('POST', '/documents/create', $payload);
        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }
}
