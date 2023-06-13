<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\PostBlog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PostStateProcessor implements ProcessorInterface
{
    public function __construct( private EntityManagerInterface $entityManager, private HttpClientInterface $client,private SerializerInterface $serializer )
    {
    }

    public function process(mixed $data, Operation $operation,array $uriVariables = [], array $context = [])
    {
        $this->entityManager->persist($data);
        $this->entityManager->flush($data);

        $response = $this->client->request(
            'GET',
            'http://127.0.0.1:8001/api/users/2'
        );
        $user = json_decode($response->getContent(),true);

        $jsonData = $this->serializer->serialize($data, 'json');

        $emailData = json_decode($jsonData, true);
        $emailData["email"] = $user['email'];
        $emailData["name"] = $user['name'];

        $emailResponse = $this->client->request(
            'POST',
            'http://127.0.0.1:8003/send-email',[
                'json'=>$emailData
            ]
        );

        return $data;
    }
}