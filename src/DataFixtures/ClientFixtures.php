<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Dette;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ClientFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $client = new Client();
            $client->setSurname("surname" . $i);
            $client->setAdresse("adresse" . $i);
            $client->setTelephone("77100101" . $i);

            if ($i % 2 == 0) {
                $user = new User();
                $user->setLogin('login' . $i);
                $user->setPassword('password');
                $user->setNom('nom' . $i);       // Assurez-vous d'attribuer un 'nom'
                $user->setPreNom('prenom' . $i);  // Assurez-vous d'attribuer un 'prenom'
                $client->setCompte($user);

                for ($j = 1; $j <= 10; $j++) {
                    $dette = new Dette();
                    $dette->setDateAt(new \DateTimeImmutable());
                    $dette->setMontant(1000 * $j * $i);
                    $dette->setMontantVerse($j % 2 == 0 ? 1000 * $j * $i - 1000 : 1000 * $j * $i);
                    $client->addDette($dette);
                }
            }
            $manager->persist($client);
        }

        $manager->flush();
    }
}
