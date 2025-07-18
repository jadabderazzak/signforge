<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Company;
use App\Entity\Client;
use App\Entity\TypeDocument;
use App\Entity\Document;
use App\Entity\DocumentItem;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

/**
 * Loads initial demo data into the database.
 * Includes: 1 admin user, 1 company, 5 clients, 6 document types, 3 documents with 3 items each.
 * Automatically links all entities with proper relations and calculates document totals.
 */
class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Create an admin user
        $user = new User();
        $user->setEmail('admin@admin.com');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setName($faker->name);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'admin'));
        $manager->persist($user);

        // Create a main company associated with the user
        $company = new Company();
        $company->setName($faker->company);
        $company->setAdress($faker->address);
        $company->setPhone($faker->phoneNumber);
        $company->setTaxIdentification(strtoupper($faker->bothify('TX######')));
        $company->setRegistrationNumber(strtoupper($faker->bothify('RC######')));
        $company->setBankDetails($faker->text(150));
        $company->setLogo(null); // Set to 'logo.png' if needed
        $company->setUser($user);
        $company->setFooter($faker->sentence());
        $company->setCurrency($faker->randomElement(['$', '£', '€']));
        $manager->persist($company);

       

        // Create 5 demo clients
        $clients = [];
        for ($i = 0; $i < 5; $i++) {
            $client = new Client();
            $client->setName($faker->name);
            $client->setEmail($faker->email);
            $client->setAdress($faker->address);
            $client->setCompany($faker->company);
            $client->setUser($user);
            $client->setTaxNumber((string) $faker->randomNumber(8, true));

            $manager->persist($client);
            $clients[] = $client;
        }

        // Create common document types
        $types = [];
        foreach (['Invoice', 'Quote', 'Delivery Note', 'Purchase Order', 'Credit Note', 'Proforma Invoice'] as $label) {
            $type = new TypeDocument();
            $type->setLabel($label);
            $type->setName($label);
            $type->setCreatedAt(new DateTime());
            $manager->persist($type);
            $types[] = $type;
        }

        // Create 3 documents, each with 3 items
        for ($i = 0; $i < 3; $i++) {
            $document = new Document();
            $document->setClient($clients[array_rand($clients)]);
            $document->setUser($user);
            $document->setStatus(false);
            $document->setType($types[array_rand($types)]);
            $document->setDocumentNumber($this->generateRandomDocumentNumber());
            $document->setCreatedAt(new DateTime());

            $totalDocument = 0;

            // Add 3 document items and calculate line total (with discount & tax)
            for ($j = 0; $j < 3; $j++) {
                $item = new DocumentItem();
                $item->setDocument($document);
                $item->setTitle($faker->sentence(3));
                $item->setQuantity(mt_rand(1, 5));
                $item->setUnitPrice(mt_rand(10, 200));
                $item->setDiscount(mt_rand(0, 20)); // 0% to 20%
                $item->setTaxe(20);// 20%

                // Auto-calculate line total
                $item->calculateTotal();

                $totalDocument += $item->getTotal();

                $manager->persist($item);
            }

            // Set total document amount
            $document->setTotal(round($totalDocument, 2));

            $manager->persist($document);
            $manager->flush(); // flush inside loop to ensure relations are saved early
        }
    }

    /**
     * Generates a random document number in formats like:
     * - INV-2025/0001
     * - QT0002/2025
     */
    private function generateRandomDocumentNumber(): string
    {
        $faker = Factory::create();

        $types = ['INV', 'QT', 'PO', 'DL']; // Invoice, Quote, Purchase Order, Delivery
        $type = $faker->randomElement($types);
        $year = date('Y');
        $number = str_pad($faker->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);

        $format1 = "$type-$year/$number";     // ex: INV-2025/0001
        $format2 = "$type$number/$year";      // ex: QT0002/2025

        return $faker->randomElement([$format1, $format2]);
    }
}
