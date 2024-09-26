<?php

namespace App\Controller;

use App\Entity\ParticipantSortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class ParticipantSortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParticipantSortie::class);
    }

    // Méthode pour trouver les participants d'une sortie
    public function findBySortie(int $sortieId)
    {
        return $this->createQueryBuilder('ps')
            ->andWhere('ps.sortie = :sortieId')
            ->setParameter('sortieId', $sortieId)
            ->getQuery()
            ->getResult();
    }
}