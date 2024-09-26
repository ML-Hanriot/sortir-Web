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
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.participants', 'p') // Utilisez leftJoin pour toutes les conditions de participant
            ->join('s.etat', 'e')
            ->join('s.organisateur', 'o')
            ->select('s,e,p,o');

        // Vérification et application des filtres
        if (isset($filters['campus']) && $filters['campus']) {
            $qb->andWhere('s.campus = :campus')
                ->setParameter('campus', $filters['campus']);
        }

        if (isset($filters['nom']) && $filters['nom']) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $filters['nom'] . '%');
        }

        if (isset($filters['date_debut']) && $filters['date_debut']) {
            $qb->andWhere('s.dateHeureDebut >= :date_debut')
                ->setParameter('date_debut', new \DateTime($filters['date_debut']));
        }

        if (isset($filters['date_fin']) && $filters['date_fin']) {
            $qb->andWhere('s.dateHeureDebut <= :date_fin')
                ->setParameter('date_fin', new \DateTime($filters['date_fin']));
        }

        if (isset($filters['organisateur']) && $filters['organisateur']) {
            $qb->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $filters['organisateur']);
        }

        // Filtrer les sorties auxquelles l'utilisateur est inscrit
        if (isset($filters['inscrit']) && $filters['inscrit']) {
            $qb->andWhere('p = :inscrit')
                ->setParameter('inscrit', $filters['inscrit']);
        }

        // Filtrer les sorties auxquelles l'utilisateur n'est pas inscrit
        if (isset($filters['pasinscrit']) && $filters['pasinscrit']) {
            $qb->andWhere('p IS NULL OR p != :pasinscrit')
                ->setParameter('pasinscrit', $filters['pasinscrit']);
        }

        // Vérifier si la sortie est déjà passée
        if (isset($filters['passer']) && $filters['passer']) {
            $qb->andWhere('s.dateHeureDebut < :now')
                ->setParameter('now', new \DateTime());
        }

        return $qb->getQuery()->getResult();
    }
}