<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Document;
use App\Entity\DocumentItem;
use App\Entity\TypeDocument;
use App\Entity\User;
use App\Entity\Client;

class DocumentTest extends TestCase
{
    public function testInitialState(): void
    {
        $doc = new Document();

        $this->assertNull($doc->getId());
        $this->assertNull($doc->getType());
        $this->assertNull($doc->getCreatedAt());
        $this->assertNull($doc->isStatus());
        $this->assertNull($doc->getUser());
        $this->assertNull($doc->getPdfPath());
        $this->assertNull($doc->getSlug());
        $this->assertNull($doc->getDocumentNumber());
        $this->assertNull($doc->getClient());
        $this->assertNull($doc->getTotal());
        $this->assertCount(0, $doc->getDocumentItems());
    }

    public function testSettersAndGetters(): void
    {
        $now     = new \DateTime('2025-07-18 12:00:00');
        $type    = new TypeDocument();
        $user    = new User();
        $client  = new Client();
        $doc     = new Document();

        $doc
            ->setType($type)
            ->setCreatedAt($now)
            ->setStatus(true)
            ->setUser($user)
            ->setPdfPath('/path/to/file.pdf')
            ->setSlug('doc-slug')
            ->setDocumentNumber('DOC-001')
            ->setClient($client)
            ->setTotal(123.45);

        $this->assertSame($type,    $doc->getType());
        $this->assertSame($now,     $doc->getCreatedAt());
        $this->assertTrue($doc->isStatus());
        $this->assertSame($user,    $doc->getUser());
        $this->assertSame('/path/to/file.pdf', $doc->getPdfPath());
        $this->assertSame('doc-slug',          $doc->getSlug());
        $this->assertSame('DOC-001',           $doc->getDocumentNumber());
        $this->assertSame($client,  $doc->getClient());
        $this->assertSame(123.45,   $doc->getTotal());
    }

    public function testAddAndRemoveDocumentItem(): void
    {
        $doc  = new Document();
        $item = new DocumentItem();

        $this->assertCount(0, $doc->getDocumentItems());

        $doc->addDocumentItem($item);
        $this->assertCount(1, $doc->getDocumentItems());
        $this->assertSame($doc, $item->getDocument());

        $doc->removeDocumentItem($item);
        $this->assertCount(0, $doc->getDocumentItems());
        $this->assertNull($item->getDocument());
    }
}
