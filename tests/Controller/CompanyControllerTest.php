<?php

namespace App\Tests\Controller;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CompanyControllerTest extends WebTestCase
{
    private KernelBrowser      $client;
    private UserRepository     $users;
    private CompanyRepository  $companies;
    private object             $testUser;

    protected function setUp(): void
    {
        $this->client    = static::createClient();
        $container       = self::getContainer();
        $this->users     = $container->get(UserRepository::class);
        $this->companies = $container->get(CompanyRepository::class);

        // Use the admin fixture
        $this->testUser = $this->users->findOneByEmail('admin@admin.com');
        $this->client->loginUser($this->testUser);
    }

    public function testIndexRedirectsWhenNoCompany(): void
    {
        // Ensure no company exists for this user
        foreach ($this->companies->findBy(['user' => $this->testUser]) as $c) {
            $em = self::getContainer()->get('doctrine')->getManager();
            $em->remove($c);
            $em->flush();
        }

        $this->client->request('GET', '/company');
        $this->assertResponseRedirects('/company/add');
    }

    public function testAddCompanyIncreasesDatabaseCount(): void
    {
        $before = count($this->companies->findBy(['user' => $this->testUser]));

        // GET the add form
        $crawler = $this->client->request('GET', '/company/add');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name=company]');

        // Submit with only the fields actually in the form
        $form = $crawler->filter('form[name="company"]')->form([
            'company[name]'     => 'TestCo',
            'company[currency]' => 'USD',
            'company[adress]'   => '123 Main St',
            'company[phone]'    => '+123456789',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/company');
        $this->client->followRedirect();

        $after = count($this->companies->findBy(['user' => $this->testUser]));
        $this->assertSame($before + 1, $after);

        // Verify the record exists
        $saved = $this->companies->findOneBy([
            'user' => $this->testUser,
            'name' => 'TestCo'
        ]);
        $this->assertInstanceOf(Company::class, $saved);
        $this->assertSame('USD', $saved->getCurrency());
    }

    public function testUpdateNonexistentCompanyRedirects(): void
    {
        $this->client->request('GET', '/company/update/non-existent-slug');
        $this->assertResponseRedirects('/board'); // as per your controller’s redirect
    }

    public function testUpdateExistingCompanyPersistsChanges(): void
    {
        // Create a company fixture
        $company = (new Company())
            ->setName('OrigName')
            ->setCurrency('EUR')
            ->setAdress('Orig Addr')
            ->setPhone('000')
            ->setUser($this->testUser);

        $em = self::getContainer()->get('doctrine')->getManager();
        $em->persist($company);
        $em->flush();

        // GET update form
        $crawler = $this->client->request('GET', '/company/update/'.$company->getSlug());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name=company]');

        // Submit only the existing fields
        $form = $crawler->filter('form[name="company"]')->form([
            'company[name]'     => 'UpdatedCo',
            'company[currency]' => 'GBP',
            'company[adress]'   => 'New Addr',
            'company[phone]'    => '+987654321',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/company');
        $this->client->followRedirect();

        // Reload and assert
        $refreshed = $this->companies->find($company->getId());
        $this->assertSame('UpdatedCo',    $refreshed->getName());
        $this->assertSame('GBP',          $refreshed->getCurrency());
        $this->assertSame('New Addr',     $refreshed->getAdress());
        $this->assertSame('+987654321',   $refreshed->getPhone());
    }
}
