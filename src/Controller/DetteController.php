<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Dette;
use App\Entity\Paiement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class DetteController extends AbstractController
{
    private $paginator;

    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    #[Route('/create-dette', name: 'create_dette')]
    public function create(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
{
    $dette = new Dette();
    $clients = $entityManager->getRepository(Client::class)->findAll();

    if ($request->isMethod('POST')) {
        $montant = $request->request->get('montant');
        $montantVerse = $request->request->get('montantVerse');
        $clientId = $request->request->get('client');
        $dateAt = $request->request->get('dateAt');

        $dette->setMontant((float)$montant);
        $dette->setMontantVerse((float)$montantVerse);
        $dette->setDateAt(new \DateTimeImmutable($dateAt));
        $client = $entityManager->getRepository(Client::class)->find($clientId);
        $dette->setClient($client);

        $errors = $validator->validate($dette);

        if (count($errors) > 0) {
            return $this->render('dette/create.html.twig', [
                'clients' => $clients,
                'errors' => $errors,
            ]);
        }

        $entityManager->persist($dette);
        $entityManager->flush();

        return $this->redirectToRoute('dette_list');
    }

    return $this->render('dette/create.html.twig', [
        'clients' => $clients,
    ]);
}

    #[Route('/dette/list', name: 'dette_list')]
    public function list(Request $request, EntityManagerInterface $entityManager): Response
    {
        $debtsQuery = $entityManager->getRepository(Dette::class)->createQueryBuilder('d');

        $clientId = $request->query->get('clientId');
        if ($clientId) {
            $debtsQuery->andWhere('d.client = :clientId')
                ->setParameter('clientId', $clientId);
        }

        $dateAt = $request->query->get('dateAt');
        if ($dateAt) {
            $debtsQuery->andWhere('d.dateAt >= :dateAt')
                ->setParameter('dateAt', new \DateTimeImmutable($dateAt));
        }

        $status = $request->query->get('status');
        if ($status === 'solde') {
            $debtsQuery->andWhere('d.montant = d.montantVerse');
        } elseif ($status === 'non_solde') {
            $debtsQuery->andWhere('d.montant > d.montantVerse');
        }

        $dettes = $this->paginator->paginate(
            $debtsQuery->getQuery(),
            $request->query->getInt('page', 1),
            8
        );

        return $this->render('dette/list.html.twig', [
            'dettes' => $dettes,
            'currentPage' => $dettes->getCurrentPageNumber(),
            'totalPages' => ceil($dettes->getTotalItemCount() / 8),
            'status' => $status,
            'dateAt' => $dateAt,
            'clientId' => $clientId,
            'clients' => $entityManager->getRepository(Client::class)->findAll(),
        ]);
    }


    #[Route('/client/{clientId}/dettes', name: 'client_dettes')]
    public function listByClient(int $clientId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $client = $entityManager->getRepository(Client::class)->find($clientId);
        if (!$client) {
            throw $this->createNotFoundException('Client non trouvé.');
        }

        $debtsQuery = $entityManager->getRepository(Dette::class)->createQueryBuilder('d')
            ->where('d.client = :clientId')
            ->setParameter('clientId', $clientId);

        $dateAt = $request->query->get('dateAt');
        if ($dateAt) {
            $debtsQuery->andWhere('d.dateAt >= :dateAt')
                ->setParameter('dateAt', new \DateTimeImmutable($dateAt));
        }

        $status = $request->query->get('status');
        if ($status === 'solde') {
            $debtsQuery->andWhere('d.montant = d.montantVerse');
        } elseif ($status === 'non_solde') {
            $debtsQuery->andWhere('d.montant > d.montantVerse');
        }

        $dettes = $this->paginator->paginate(
            $debtsQuery->getQuery(),
            $request->query->getInt('page', 1),
            8
        );

        return $this->render('dette/clientdette.html.twig', [
            'dettes' => $dettes,
            'client' => $client,
            'currentPage' => $dettes->getCurrentPageNumber(),
            'totalPages' => ceil($dettes->getTotalItemCount() / 8),
            'dateAt' => $dateAt,
            'status' => $status,
        ]);
    }


    #[Route('/dette/{detteId}/paiement/create', name: 'create_paiement')]
    public function createPayment(int $detteId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $dette = $entityManager->getRepository(Dette::class)->find($detteId);
        if (!$dette) {
            throw $this->createNotFoundException('Dette non trouvée.');
        }

        if ($request->isMethod('POST')) {
            $montant = $request->request->get('montant');
            $dateAt = $request->request->get('dateAt');

            if ($montant) {
                $paiement = new Paiement();
                $paiement->setMontant((float)$montant);
                $paiement->setDateAt(new \DateTimeImmutable($dateAt)); 
                $paiement->setDette($dette);

                $dette->setMontantVerse($dette->getMontantVerse() + $montant);

                $entityManager->persist($paiement);
                $entityManager->flush();

                $this->addFlash('success', 'Paiement enregistré avec succès.');
                return $this->redirectToRoute('dette_list');
            } else {
                $this->addFlash('error', 'Le montant est requis.');
            }
        }

        return $this->render('paiement/index.html.twig', [
            'dette' => $dette,
        ]);
    }


    #[Route('/dette/{id}', name: 'dette_details')]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $dette = $entityManager->getRepository(Dette::class)->find($id);

        if (!$dette) {
            throw $this->createNotFoundException('La dette demandée n\'existe pas.');
        }

        return $this->render('dette/details.html.twig', [
            'dette' => $dette,
            'paiements' => $dette->getPaiements(),
        ]);
    }
}
