<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\User;
use App\Entity\Document;
use App\Entity\Client;

class UserTest extends TestCase
{
    public function testEmailAndIdentifier(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame('test@example.com', $user->getUserIdentifier());
    }

    public function testDefaultRoleIsAlwaysRoleUser(): void
    {
        $user = new User();

        $roles = $user->getRoles();
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testSetRolesAddsAndKeepsRoleUser(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testNameSetterAndGetter(): void
    {
        $user = new User();
        $user->setName('Alice');

        $this->assertSame('Alice', $user->getName());
    }

    public function testAddAndRemoveDocument(): void
    {
        $user     = new User();
        $document = new Document();

        $this->assertCount(0, $user->getDocuments());

        $user->addDocument($document);
        $this->assertCount(1, $user->getDocuments());
        $this->assertSame($user, $document->getUser());

        $user->removeDocument($document);
        $this->assertCount(0, $user->getDocuments());
        $this->assertNull($document->getUser());
    }

    public function testAddAndRemoveClient(): void
    {
        $user   = new User();
        $client = new Client();

        $this->assertCount(0, $user->getClients());

        $user->addClient($client);
        $this->assertCount(1, $user->getClients());
        $this->assertSame($user, $client->getUser());

        $user->removeClient($client);
        $this->assertCount(0, $user->getClients());
        $this->assertNull($client->getUser());
    }
}
