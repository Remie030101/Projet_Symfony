<?php

namespace App\Controller\Api;

use App\Entity\Role;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Contrôleur API pour la gestion des rôles
 * 
 * Ce contrôleur gère toutes les opérations CRUD sur les rôles
 * via une API REST
 */
#[Route('/api')]
class RoleController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RoleRepository $roleRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * Récupère la liste des rôles
     * 
     * @return JsonResponse La réponse JSON contenant la liste des rôles
     */
    #[Route('/roles', name: 'api_roles_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $roles = $this->roleRepository->findAll();
        $data = $this->serializer->serialize($roles, 'json', ['groups' => 'role:read']);
        return new JsonResponse(json_decode($data));
    }

    /**
     * Récupère un rôle spécifique
     * 
     * @param Role $role L'entité rôle à récupérer
     * @return JsonResponse La réponse JSON contenant les détails du rôle
     */
    #[Route('/roles/{id}', name: 'api_roles_show', methods: ['GET'])]
    public function show(Role $role): JsonResponse
    {
        $data = $this->serializer->serialize($role, 'json', ['groups' => 'role:read']);
        return new JsonResponse(json_decode($data));
    }

    /**
     * Crée un nouveau rôle
     * 
     * @param Request $request La requête HTTP contenant les données du rôle
     * @return JsonResponse La réponse JSON avec le rôle créé ou les erreurs
     * 
     * Body JSON requis:
     * {
     *     "nom": "string (ex: ROLE_USER)",
     *     "description": "string (optionnel)"
     * }
     */
    #[Route('/roles', name: 'api_roles_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid JSON data'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $role = new Role();
            $role->setNom($data['nom'] ?? '');
            $role->setDescription($data['description'] ?? null);
            
            $errors = $this->validator->validate($role);
            if (count($errors) > 0) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $this->entityManager->persist($role);
            $this->entityManager->flush();
            
            $data = $this->serializer->serialize($role, 'json', ['groups' => 'role:read']);
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Role created successfully',
                'data' => json_decode($data)
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'An error occurred while creating the role',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Met à jour un rôle existant
     * 
     * @param Role $role L'entité rôle à mettre à jour
     * @param Request $request La requête HTTP contenant les nouvelles données
     * @return JsonResponse La réponse JSON avec le rôle mis à jour ou les erreurs
     */
    #[Route('/roles/{id}', name: 'api_roles_update', methods: ['PUT'])]
    public function update(Role $role, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (isset($data['nom'])) {
                $role->setNom($data['nom']);
            }
            if (isset($data['description'])) {
                $role->setDescription($data['description']);
            }
            
            $errors = $this->validator->validate($role);
            if (count($errors) > 0) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $this->entityManager->flush();
            
            $data = $this->serializer->serialize($role, 'json', ['groups' => 'role:read']);
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Role updated successfully',
                'data' => json_decode($data)
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'An error occurred while updating the role',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Supprime un rôle
     * 
     * @param Role $role L'entité rôle à supprimer
     * @return JsonResponse La réponse JSON indiquant le succès de la suppression
     */
    #[Route('/roles/{id}', name: 'api_roles_delete', methods: ['DELETE'])]
    public function delete(Role $role): JsonResponse
    {
        try {
            $this->entityManager->remove($role);
            $this->entityManager->flush();
            
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Role deleted successfully'
            ], Response::HTTP_NO_CONTENT);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'An error occurred while deleting the role',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 