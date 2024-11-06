<?php

namespace App\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class ClientController extends AbstractController
{
    #[Route('/client-Liste', name: 'app_client')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $clientsQuery = $entityManager->getRepository(Client::class)->createQueryBuilder('c');
    
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
    
        $page = $request->query->getInt('page', 1);
        $limit = 8;
        $offset = ($page - 1) * $limit;
    
        $clients = $clientsQuery->setFirstResult($offset)
                                ->setMaxResults($limit)
                                ->getQuery()
                                ->getResult();
    
        $totalPages = ceil($totalClients / $limit);
    
        return $this->render('client/index.html.twig', [
            'clients' => $clients,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'surname' => $surname,
            'telephone' => $telephone,
        ]);
    }
    



    #[Route('/client/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $errors = [];
    
        if ($request->isMethod('POST')) {
            $surname = $request->request->get('surname');
            $telephone = $request->request->get('telephone');
            $adresse = $request->request->get('adresse');
    
            $existingClientSurname = $entityManager->getRepository(Client::class)->findOneBy(['surname' => $surname]);
            $existingClientTelephone = $entityManager->getRepository(Client::class)->findOneBy(['telephone' => $telephone]);
    
            if ($existingClientSurname) {
                $errors['surname'] = 'Ce Surname existe déjà.';
            }
            if ($existingClientTelephone) {
                $errors['telephone'] = 'Ce Téléphone existe déjà.';
            }
    
            if (!$errors) {
                $client = new Client();
                $client->setSurname($surname);
                $client->setTelephone($telephone);
                $client->setAdresse($adresse);
    
                $validationErrors = $validator->validate($client);
                if (count($validationErrors) > 0) {
                    foreach ($validationErrors as $error) {
                        $field = $error->getPropertyPath();
                        $errors[$field] = $error->getMessage();
                    }
                }
    
                if (!$errors) {
                    $entityManager->persist($client);
                    $entityManager->flush();
                    return $this->redirectToRoute('app_client');
                }
            }
        }
    
        return $this->render('client/create.html.twig', [
            'errors' => $errors
        ]);
    }
    

}
