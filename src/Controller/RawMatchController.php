<?php

namespace App\Controller;

use App\Command\RegenerateCommand;
use App\Repository\CalculatedMatchRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class RawMatchController extends AbstractController
{
    #[Route('/raw/match', name: 'raw_match')]
    public function index(
        Request $request,
        KernelInterface $kernel,
        CalculatedMatchRepository $calculatedMatchRepository
    ): Response {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get("file");

        file_put_contents(RegenerateCommand::$unparsedFilesDir . '/' . filter_var($uploadedFile->getClientOriginalName(), FILTER_SANITIZE_STRING), $uploadedFile->getContent());

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'referee:regenerate',
            'parseHbrs' => 'true',
        ]);
        // You can use NullOutput() if you don't need the output
        $application->run($input, (new NullOutput()));

        return $this->json($calculatedMatchRepository->getLastMatchId());
    }
}
