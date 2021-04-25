<?php

namespace App\Controller;

use App\Entity\CalculatedMatch;
use App\Form\CalculatedMatchType;
use App\Repository\CalculatedMatchRepository;
use App\Service\MatchCalculatorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        $data = $matchCalculator->getDataFromFile("HBReplay-2020-04-07-14h08m.hbr2.bin.json");
        $result = $matchCalculator->process($data);

        return new Response();
    }


}
