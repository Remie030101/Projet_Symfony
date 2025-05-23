<?php

namespace App\Controller\Api;

use App\Entity\Preference;
use App\Repository\PreferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Contrôleur API pour la gestion des préférences utilisateur
 * 
 * Ce contrôleur gère toutes les opérations CRUD sur les préférences
 * des utilisateurs via une API REST
 */
#[Route('/api')]
class PreferenceController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PreferenceRepository $preferenceRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * Récupère la liste des préférences
     * 
     * @return JsonResponse La réponse JSON contenant la liste des préférences
     */
    #[Route('/preferences', name: 'api_preferences_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $preferences = $this->preferenceRepository->findAll();
        $data = $this->serializer->serialize($preferences, 'json', ['groups' => 'preference:read']);
        return new JsonResponse(json_decode($data));
    }

    /**
     * Récupère une préférence spécifique
     * 
     * @param Preference $preference L'entité préférence à récupérer
     * @return JsonResponse La réponse JSON contenant les détails de la préférence
     */
    #[Route('/preferences/{id}', name: 'api_preferences_show', methods: ['GET'])]
    public function show(Preference $preference): JsonResponse
    {
        $data = $this->serializer->serialize($preference, 'json', ['groups' => 'preference:read']);
        return new JsonResponse(json_decode($data));
    }

    /**
     * Crée une nouvelle préférence
     * 
     * @param Request $request La requête HTTP contenant les données de la préférence
     * @return JsonResponse La réponse JSON avec la préférence créée ou les erreurs
     * 
     * Body JSON requis:
     * {
     *     "langue": "string",
     *     "theme": "string",
     *     "notifications": boolean
     * }
     */
    #[Route('/preferences', name: 'api_preferences_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $preference = new Preference();
        $preference->setLangue($data['langue'] ?? 'fr');
        $preference->setTheme($data['theme'] ?? 'light');
        $preference->setNotifications($data['notifications'] ?? true);
        
        $errors = $this->validator->validate($preference);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        $this->entityManager->persist($preference);
        $this->entityManager->flush();
        
        $data = $this->serializer->serialize($preference, 'json', ['groups' => 'preference:read']);
        return new JsonResponse(json_decode($data), Response::HTTP_CREATED);
    }

    /**
     * Met à jour une préférence existante
     * 
     * @param Preference $preference L'entité préférence à mettre à jour
     * @param Request $request La requête HTTP contenant les nouvelles données
     * @return JsonResponse La réponse JSON avec la préférence mise à jour ou les erreurs
     */
    #[Route('/preferences/{id}', name: 'api_preferences_update', methods: ['PUT'])]
    public function update(Preference $preference, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['langue'])) {
            $preference->setLangue($data['langue']);
        }
        if (isset($data['theme'])) {
            $preference->setTheme($data['theme']);
        }
        if (isset($data['notifications'])) {
            $preference->setNotifications($data['notifications']);
        }
        
        $errors = $this->validator->validate($preference);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        $this->entityManager->flush();
        
        $data = $this->serializer->serialize($preference, 'json', ['groups' => 'preference:read']);
        return new JsonResponse(json_decode($data));
    }

    /**
     * Supprime une préférence
     * 
     * @param Preference $preference L'entité préférence à supprimer
     * @return JsonResponse La réponse JSON indiquant le succès de la suppression
     */
    #[Route('/preferences/{id}', name: 'api_preferences_delete', methods: ['DELETE'])]
    public function delete(Preference $preference): JsonResponse
    {
        $this->entityManager->remove($preference);
        $this->entityManager->flush();
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
} 