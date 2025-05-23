<?php

namespace App\Repository;

use App\Entity\Preference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour l'entité Preference
 * 
 * Ce repository fournit des méthodes pour interagir avec la table des préférences
 * et gérer les opérations de base de données liées aux préférences utilisateur
 */
class PreferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Preference::class);
    }

    /**
     * Sauvegarde une préférence en base de données
     * 
     * @param Preference $entity L'entité Preference à sauvegarder
     * @param bool $flush Détermine si les changements doivent être persistés immédiatement
     */
    public function save(Preference $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime une préférence de la base de données
     * 
     * @param Preference $entity L'entité Preference à supprimer
     * @param bool $flush Détermine si les changements doivent être persistés immédiatement
     */
    public function remove(Preference $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Recherche les préférences par thème
     * 
     * @param string $theme Le thème à rechercher
     * @return Preference[] Liste des préférences correspondant au thème
     */
    public function findByTheme(string $theme): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.theme = :theme')
            ->setParameter('theme', $theme)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche les préférences par langue
     * 
     * @param string $langue La langue à rechercher
     * @return Preference[] Liste des préférences correspondant à la langue
     */
    public function findByLangue(string $langue): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.langue = :langue')
            ->setParameter('langue', $langue)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 