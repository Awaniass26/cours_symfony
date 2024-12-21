<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Dette;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClientFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $encoder) {
      
    }
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
                $passwordHasher=$this->encoder->hashPassword($user,'password');
                $user->setPassword($passwordHasher);
                $user->setNom('nom' . $i);      
                $user->setPreNom('prenom' . $i);
                $random=rand(0,1);
                $roles=$random==1?['ROLE_BOUTIQUIER','ROLE_CLIENT']:['ROLE_CLIENT'];
                $user->setRoles($roles);
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
