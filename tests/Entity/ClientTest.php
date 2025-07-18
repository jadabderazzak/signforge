<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Client;
use App\Entity\User;

class ClientTest extends TestCase
{
    public function testInitialState(): void
    {
        $client = new Client();

        $this->assertNull($client->getId());
        $this->assertNull($client->getName());
        $this->assertNull($client->getCompany());
        $this->assertNull($client->getAdress());
        $this->assertNull($client->getEmail());
        $this->assertNull($client->getTaxNumber());
        $this->assertNull($client->getSlug());
        $this->assertNull($client->getUser());
    }

    public function testSetNameAndCompany(): void
    {
        $client = new Client();
        $client->setName('John Doe')
               ->setCompany('Acme Inc.');

        $this->assertSame('John Doe', $client->getName());
        $this->assertSame('Acme Inc.', $client->getCompany());
    }

    public function testSetAdressAndEmailAndTaxNumber(): void
    {
        $client = new Client();
        $client->setAdress('123 Main St')
               ->setEmail('client@example.com')
               ->setTaxNumber('TAX-123456');

        $this->assertSame('123 Main St', $client->getAdress());
        $this->assertSame('client@example.com', $client->getEmail());
        $this->assertSame('TAX-123456', $client->getTaxNumber());
    }

    public function testSetSlug(): void
    {
        $client = new Client();
        $client->setSlug('acme-inc');

        $this->assertSame('acme-inc', $client->getSlug());
    }

    public function testSetUser(): void
    {
        $user = new User();
        $client = new Client();
        $client->setUser($user);

        $this->assertSame($user, $client->getUser());
    }
}
