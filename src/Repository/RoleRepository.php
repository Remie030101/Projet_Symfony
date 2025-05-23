<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour l'entité Role
 * 
 * Ce repository fournit des méthodes pour interagir avec la table des rôles
 * et gérer les opérations de base de données liées aux rôles
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    /**
     * Sauvegarde un rôle en base de données
     * 
     * @param Role $entity L'entité Role à sauvegarder
     * @param bool $flush Détermine si les changements doivent être persistés immédiatement
     */
    public function save(Role $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime un rôle de la base de données
     * 
     * @param Role $entity L'entité Role à supprimer
     * @param bool $flush Détermine si les changements doivent être persistés immédiatement
     */
    public function remove(Role $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Recherche un rôle par son nom
     * 
     * @param string $nom Le nom du rôle à rechercher
     * @return Role|null Le rôle trouvé ou null
     */
    public function findOneByNom(string $nom): ?Role
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.nom = :nom')
            ->setParameter('nom', $nom)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Recherche les rôles par préfixe
     * 
     * @param string $prefix Le préfixe à rechercher (ex: "ROLE_")
     * @return Role[] Liste des rôles correspondant au préfixe
     */
    public function findByPrefix(string $prefix): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.nom LIKE :prefix')
            ->setParameter('prefix', $prefix . '%')
            ->orderBy('r.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 