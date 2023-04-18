<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthorController extends AbstractController
{
    #[Route('/api/authors', name: 'author', methods: ['GET'])]
    public function getAllAuthor(AuthorRepository $authorRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllAuthor-" . $page . "-" . $limit;

        $authorList = $cache->get($idCache, function (ItemInterface $item) use ($authorRepository, $page, $limit) {
            echo ("L'element n'est pas encore en cache !\n");
            $item->tag("auhtorsCache");
            $item->expiresAfter(60);
            return $authorRepository->findAllWithPagination($page, $limit);
        });

        $content = SerializationContext::create()->setGroups(["getBooks"]);
        $jsonBookList = $serializer->serialize($authorList, 'json', $content);

        return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/authors/{id}', name: 'detailAuthor', methods: ['GET'])]
    public function getDetailBook(Author $author, SerializerInterface $serializer): JsonResponse
    {
        $content = SerializationContext::create()->setGroups(["getBooks"]);
        $jsonAuthor = $serializer->serialize($author, 'json', $content);
        return new JsonResponse($jsonAuthor, Response::HTTP_OK, [], true);
    }

    #[Route('/api/authors/{id}', name: 'detailAuthor', methods: ['DELETE'])]
    public function deleteUser(Author $author, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($author);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/authors', name: "createAuthor", methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'avez pas les droits suffisant pour crée un livre.")]
    public function createBook(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $author = $serializer->deserialize($request->getContent(), Book::class, 'json');

        $em->persist($author);
        $em->flush();

        $content = SerializationContext::create()->setGroups(["getBooks"]);
        $jsonBook = $serializer->serialize($author, 'json', $content);

        $location = $urlGenerator->generate('detailAuthor', ['id' => $author->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonBook, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/authors/{id}', name: "updateAuthor", methods: ['PUT'])]
    public function updateBook(Request $request, SerializerInterface $serializer, Author $currentAuthor, EntityManagerInterface $em, AuthorRepository $authorRepository, ValidatorInterface $validator): JsonResponse
    {
        $newAuthor = $serializer->deserialize($request->getContent(), Book::class, 'json');

        $currentAuthor->setLastname($newAuthor->getTitle());
        $currentAuthor->setFirstname($newAuthor->getCoverText());

        //On verifie les erreurs
        $errors = $validator->validate($currentAuthor);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($currentAuthor);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
