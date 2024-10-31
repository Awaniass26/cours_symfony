<?php


namespace App\Controller;

use App\Entity\User;
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
        // Récupérer tous les clients depuis la base de données avec les filtres appliqués
        $clientsQuery = $entityManager->getRepository(Client::class)->createQueryBuilder('c');
    
        // Filtrage
        $surname = $request->query->get('surname');
        $telephone = $request->query->get('telephone');
    
        if ($surname) {
            $clientsQuery->andWhere('c.surname LIKE :surname')
                         ->setParameter('surname', '%' . $surname . '%');
        }
        if ($telephone) {
            $clientsQuery->andWhere('c.telephone LIKE :telephone')
                         ->setParameter('telephone', '%' . $telephone . '%');
        }
    
        $totalClientsQuery = clone $clientsQuery;
        $totalClients = count($totalClientsQuery->getQuery()->getResult());
    
        // Pagination
        $page = $request->query->getInt('page', 1);
        $limit = 8;
        $offset = ($page - 1) * $limit;
    
        // Récupérer les clients filtrés avec pagination
        $clients = $clientsQuery->setFirstResult($offset)
                                ->setMaxResults($limit)
                                ->getQuery()
                                ->getResult();
    
        // Calcul du nombre total de pages
        $totalPages = ceil($totalClients / $limit);
    
        return $this->render('client/index.html.twig', [
            'clients' => $clients,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'surname' => $surname,
            'telephone' => $telephone,
        ]);
    }
    



    #[Route('/client/create', name: 'app_client_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
{
    $errors = [];

    if ($request->isMethod('POST')) {
        // Champs Client
        $surname = $request->request->get('surname');
        $telephone = $request->request->get('telephone');
        $adresse = $request->request->get('adresse');

        // Champs User
        $nom = $request->request->get('nom');
        $prenom = $request->request->get('prenom');
        $login = $request->request->get('login');
        $password = $request->request->get('password');

        // Vérifier si un client avec ce surname ou telephone existe déjà
        $existingClientSurname = $entityManager->getRepository(Client::class)->findOneBy(['surname' => $surname]);
        $existingClientTelephone = $entityManager->getRepository(Client::class)->findOneBy(['telephone' => $telephone]);

        if ($existingClientSurname) {
            $errors['surname'] = 'Ce Surname existe déjà.';
        }
        if ($existingClientTelephone) {
            $errors['telephone'] = 'Ce Téléphone existe déjà.';
        }

        if (!$errors) {
            // Créer un objet User (compte)
            $user = new User();
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setLogin($login);
            $user->setPassword($password);

            // Créer un objet Client et lier le compte (User)
            $client = new Client();
            $client->setSurname($surname);
            $client->setTelephone($telephone);
            $client->setAdresse($adresse);
            $client->setCompte($user); // Lier le compte au client
            $user->setClient($client); // Lier le client au compte (bidirectionnel)

            // Sauvegarder dans la base de données
            $entityManager->persist($user);
            $entityManager->persist($client);
            $entityManager->flush();

            return $this->redirectToRoute('app_client');
        }
    }

    return $this->render('client/create.html.twig', [
        'errors' => $errors
    ]);
}

    

}
