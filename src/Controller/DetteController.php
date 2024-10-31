<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Dette;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

class DetteController extends AbstractController
{
    private $paginator; // Declare a private variable for paginator

    public function __construct(PaginatorInterface $paginator) // Inject paginator through the constructor
    {
        $this->paginator = $paginator;
    }


    #[Route('/dette/{clientId}', name: 'app_dette')]
public function index($clientId, Request $request, EntityManagerInterface $entityManager): Response
{
    $client = $entityManager->getRepository(Client::class)->find($clientId);

    // Créez la requête de base pour récupérer les dettes
    $debtsQuery = $entityManager->getRepository(Dette::class)->createQueryBuilder('d')
        ->where('d.client = :client')
        ->setParameter('client', $client);

    // Appliquer le filtre de statut
    $status = $request->query->get('status');
    if ($status === 'solde') {
        $debtsQuery->andWhere('d.montant = d.montantVerse');
    } elseif ($status === 'non_solde') {
        $debtsQuery->andWhere('d.montant > d.montantVerse');
    }

    // Paginons les résultats
    $dettes = $this->paginator->paginate(
        $debtsQuery->getQuery(),
        $request->query->getInt('page', 1),
        8
    );

    // Calcul du montant total
    $totalAmount = array_sum(array_map(fn($dette) => $dette->getMontant(), $dettes->getItems()));

    $currentPage = $dettes->getCurrentPageNumber();
    $totalPages = ceil($dettes->getTotalItemCount() / 8);

    return $this->render('dette/index.html.twig', [
        'client' => $client,
        'dettes' => $dettes,
        'totalAmount' => $totalAmount,
        'currentPage' => $currentPage,
        'totalPages' => $totalPages,
        'status' => $status, // Passer le statut au template pour l'affichage
    ]);
}

    
}
