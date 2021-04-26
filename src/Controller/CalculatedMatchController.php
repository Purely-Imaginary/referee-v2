<?php

namespace App\Controller;

use App\Entity\CalculatedMatch;
use App\Form\CalculatedMatchType;
use App\Repository\CalculatedMatchRepository;
use App\Service\MatchCalculatorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/calculatedMatch')]
class CalculatedMatchController extends AbstractController
{
    #[Route('/', name: 'calculated_match_index', methods: ['GET'])]
    public function index(CalculatedMatchRepository $calculatedMatchRepository): Response
    {
        return $this->render('calculated_match/index.html.twig', [
            'calculated_matches' => $calculatedMatchRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'calculated_match_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MatchCalculatorService $matchCalculator): Response
    {
        $files = scandir("/var/www/files/replayData/processed");
        array_shift($files);
        array_shift($files);
        foreach ($files as $file) {
            $matchCalculator->process($matchCalculator->getDataFromFile($file));
        }

        return new Response();
    }

    #[Route('/getLastMatches', name: 'calculated_match_index_last', methods: ['GET'])]
    public function getLastMatches(
        CalculatedMatchRepository $calculatedMatchRepository,
        SerializerInterface $serializer,
        NormalizerInterface $normalizer
    ): JsonResponse
    {
        $lastMatches = $calculatedMatchRepository->getLastMatches(1);
        $json = $serializer->serialize($lastMatches, 'json', ['groups' => ['lastMatches']]);

        $encoder = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);

        $serializer = new Serializer([$normalizer], [$encoder]);
        $json2 = $serializer->serialize($lastMatches, 'json');

        return $this->json($lastMatches);
    }
}
