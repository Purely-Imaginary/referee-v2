<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthCheckController extends AbstractController
{
    public function __construct() {
    }

    #[Route('/hc', name: 'healthCheck')]
    public function index(): Response
    {

        return $this->json("OK");
    }

}
