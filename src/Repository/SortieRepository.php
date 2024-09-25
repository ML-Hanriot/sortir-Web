<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findByFilters(array $filters)
    {
        $qb = $this->createQueryBuilder('s');

        if ($filters['campus']) {
            $qb->andWhere('s.campus = :campus')
                ->setParameter('campus', $filters['campus']);
        }

        if ($filters['nom']) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $filters['nom'] . '%');
        }

        if ($filters['date_debut']) {
            $qb->andWhere('s.dateHeureDebut >= :date_debut')
                ->setParameter('date_debut', $filters['date_debut']);
        }

        if ($filters['date_fin']) {
            $qb->andWhere('s.dateHeureDebut <= :date_fin')
                ->setParameter('date_fin', $filters['date_fin']);
        }

        if ($filters['organisateur']) {
            $qb->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $filters['organisateur']);
        }

        if ($filters['inscrit']) {
            // Filtrer les sorties auxquelles l'utilisateur est inscrit
            $qb->innerJoin('s.participants', 'p') // Joindre les participants
            ->andWhere('p = :inscrit') // Vérifier l'utilisateur parmi les participants
            ->setParameter('inscrit', $filters['inscrit']);
        }

        if ($filters['pasinscrit']) {
            // Filtrer les sorties auxquelles l'utilisateur n'est pas inscrit
            $qb->leftJoin('s.participants', 'p')
                ->andWhere('p IS NULL OR p != :pasinscrit')
                ->setParameter('pasinscrit', $filters['pasinscrit']);
        }


        if ($filters['passer']) {
            $qb->andWhere('s.dateHeureDebut < :now')
                ->setParameter('now', new \DateTime());
        }

        return $qb->getQuery()->getResult();
    }
}