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

    public function findByFilters(array $filters, $user = null)
    {
        $qb = $this->createQueryBuilder('s');

        if (!empty($filters['campus'])) {
            $qb->andWhere('s.campus = :campus')
                ->setParameter('campus', $filters['campus']);
        }

        if (!empty($filters['nom'])) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $filters['nom'] . '%');
        }

        if (!empty($filters['date_debut'])) {
            $qb->andWhere('s.dateHeureDebut >= :date_debut')
                ->setParameter('date_debut', $filters['date_debut']);
        }

        if (!empty($filters['date_fin'])) {
            $qb->andWhere('s.dateHeureDebut <= :date_fin')
                ->setParameter('date_fin', $filters['date_fin']);
        }

        if (!empty($filters['organisateur']) && $user) {
            $qb->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }

        if (!empty($filters['inscrit']) && $user) {
            $qb->join('s.inscriptions', 'i')
                ->andWhere('i.participant = :user')
                ->setParameter('user', $user);
        }

        if (!empty($filters['pasinscrit']) && $user) {
            $qb->leftJoin('s.inscriptions', 'i')
                ->andWhere('i.participant != :user OR i.participant IS NULL')
                ->setParameter('user', $user);
        }

        if (!empty($filters['passer'])) {
            $qb->andWhere('s.dateHeureDebut < :now')
                ->setParameter('now', new \DateTime());
        }

        return $qb->getQuery()->getResult();
    }
}