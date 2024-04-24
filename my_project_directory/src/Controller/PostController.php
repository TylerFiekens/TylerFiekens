<?php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PostRepository;
use App\Requests\PostDto;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class PostController extends AbstractController
{
    public function __construct(
        private readonly PostRepository $postRepo,
        private readonly EntityManagerInterface $entityManager
    ) {  }

    #[Route('/v1/posts', name: 'posts.index', methods: ['GET', 'HEAD', 'OPTION'])]
    public function index(): JsonResponse
    {
        $posts = $this->postRepo->findAll();
        return $this->json([
            'data' => array_map(fn(Post $post) => [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'body' => $post->getBody(),
            ], $posts),
        ]);
    }

    #[Route('/v1/posts/{id}', name: 'posts.show', methods: ['GET', 'HEAD', 'OPTION'])]
    public function show(string $id = ''): JsonResponse
    {
        if (! $post = $this->postRepo->findOneBy(['id' => $id])) {
            return $this->json([], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'data' => [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'body' => $post->getBody(),
            ],
        ]);
    }

    #[Route('/v1/posts', name: 'posts.store', methods: ['POST'])]
    public function store(#[MapRequestPayload] PostDto $postDto): JsonResponse
    {
        $post = (new Post())
            ->setTitle($postDto->title)
            ->setBody($postDto->body);

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $this->json([
            'data' => [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'body' => $post->getBody(),
            ],
        ]);
    }

    #[Route('/v1/posts/{id}', name: 'posts.update', methods: ['PUT'])]
    public function update(#[MapRequestPayload] PostDto $postDto, string $id = ''): JsonResponse
    {
        if (! $post = $this->postRepo->findOneBy(['id' => $id])) {
            return $this->json([], Response::HTTP_NOT_FOUND);
        }

        $post = $post->setTitle($postDto->title)->setBody($postDto->body);

        $this->entityManager->flush();

        return $this->json([
            'data' => [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'body' => $post->getBody(),
            ],
        ]);
    }

    #[Route('/v1/posts/{id}', name: 'posts.delete', methods: ['DELETE'])]
    public function delete(string $id = ''): JsonResponse
    {
        if (! $post = $this->postRepo->findOneBy(['id' => $id])) {
            return $this->json([], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();

        return $this->json([]);
    }
}
