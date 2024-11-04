<?php

namespace App\DataFixtures;
use App\Entity\Paiement;
use App\Entity\Dette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PaiementFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        {
            $dettes = $manager->getRepository(Dette::class)->findAll();
    
            foreach ($dettes as $dette) {
                // Créer un paiement pour chaque dette
                $paiement = new Paiement();
                $paiement->setMontant($dette->getMontant() * 0.5); 
                $paiement->setDateAt(new \DateTimeImmutable(sprintf('-%d days', rand(1, 30)))); 
                $paiement->setDette($dette); 
    
                $manager->persist($paiement);
            }
    
            $manager->flush(); 
        }
    }
}
