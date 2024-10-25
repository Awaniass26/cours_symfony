<?php

// src/Controller/ClientController.php

namespace App\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ClientController extends AbstractController
{
    #[Route('/client-Liste', name: 'app_client')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les clients depuis la base de données
        $clientsQuery = $entityManager->getRepository(Client::class)->createQueryBuilder('c');

        // Récupérer les paramètres de filtrage de la requête
        $surname = $request->query->get('surname');
        $telephone = $request->query->get('telephone');

        // Appliquer les filtres si les valeurs sont fournies
        if ($surname) {
            $clientsQuery->andWhere('c.surname LIKE :surname')
                          ->setParameter('surname', '%' . $surname . '%');
        }
        if ($telephone) {
            $clientsQuery->andWhere('c.telephone LIKE :telephone')
                          ->setParameter('telephone', '%' . $telephone . '%');
        }

        $totalClientsQuery = clone $clientsQuery; // Cloner la requête pour compter sans les limites
        $totalClients = count($totalClientsQuery->getQuery()->getResult());

        // Pagination
        $page = $request->query->getInt('page', 1); // Page actuelle, par défaut 1
        $limit = 8; // Nombre de clients par page
        $offset = ($page - 1) * $limit; // Calculer l'offset

        // Récupérer les clients filtrés avec pagination
        $clients = $clientsQuery->setFirstResult($offset) // Début de la pagination
                                ->setMaxResults($limit) // Limite
                                ->getQuery()
                                ->getResult();

        // Compter le total de clients pour la pagination
        $totalPages = ceil($totalClients / $limit); // Nombre total de pages

        return $this->render('client/index.html.twig', [
            'clients' => $clients,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'surname' => $surname, // Assure-toi que ces variables sont également passées
            'telephone' => $telephone,
        ]);
    }



    #[Route('/client/create', name: 'app_client_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $errors = [];

        if ($request->isMethod('POST')) {
            $surname = $request->request->get('surname');
            $telephone = $request->request->get('telephone');
            $adresse = $request->request->get('adresse');

            // Vérifier si un client avec ce surname ou telephone existe déjà
            $existingClientSurname = $entityManager->getRepository(Client::class)
                ->findOneBy(['surname' => $surname]);

            $existingClientTelephone = $entityManager->getRepository(Client::class)
                ->findOneBy(['telephone' => $telephone]);

            if ($existingClientSurname) {
                $errors['surname'] = 'Ce Surname existe déjà.';
            }

            if ($existingClientTelephone) {
                $errors['telephone'] = 'Ce Téléphone existe déjà.';
            }

            // Si aucune erreur, on peut créer le client
            if (!$errors) {
                $client = new Client();
                $client->setSurname($surname);
                $client->setTelephone($telephone);
                $client->setAdresse($adresse);

                $entityManager->persist($client);
                $entityManager->flush();

                return $this->redirectToRoute('app_client');
            }
        }

        // Renvoyer le formulaire avec les erreurs
        return $this->render('client/create.html.twig', [
            'errors' => $errors
        ]);
    }


    

}
