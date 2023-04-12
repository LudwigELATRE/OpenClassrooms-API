<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookController extends AbstractController
{
    #[Route('/api/books', name: 'book', methods: ['GET'])]
    public function getBookList(BookRepository $bookRepository, SerializerInterface $serializer): JsonResponse
    {
        $bookList = $bookRepository->findAll();
        $jsonBookList = $serializer->serialize($bookList, 'json');
        return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
    }
}
