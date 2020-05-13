<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Form\UploadType;
use App\Message\ProcessCsvCommand;
use App\Repository\UploadRepository;
use App\Repository\InvoiceRepository;
use App\Service\UploadService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends AbstractController
{
    /** @var InvoiceRepository */
    private $invoiceRepository;

    /** @var UploadRepository */
    private $uploadRepository;

    /** @var MessageBus */
    private $messageBus;

    /** @var UploadService */
    private $uploadService;

    /**
     * Constructor
     * 
     * @param InvoiceRepository $invoiceRepository
     * @param UploadRepository $uploadRepository,
     * @param MessageBusInterface $messageBus
     * @param UploadService $uploadService
     */
    public function __construct(
        InvoiceRepository $invoiceRepository,
        UploadRepository $uploadRepository,
        MessageBusInterface $messageBus,
        UploadService $uploadService
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->uploadRepository = $uploadRepository;
        $this->messageBus = $messageBus;
        $this->uploadService = $uploadService;
    }

    /**
     * @Route("/", name="default")
     */
    public function index()
    {
        return $this->redirectToRoute('invoices');
    }

    /**
     * @Route("/invoices", name="invoices")
     */
    public function invoices()
    {
        $invoices = $this->invoiceRepository->findBy([], ['createdAt' => 'desc']);

        return $this->render('default/invoices.html.twig', [
            'invoices' => $invoices
        ]);
    }

    /**
     * @Route("/uploads", name="uploads")
     */
    public function uploads()
    {
        $uploads = $this->uploadRepository->findBy([], ['createdAt' => 'desc']);

        return $this->render('default/uploads.html.twig', [
            'uploads' => $uploads
        ]);
    }

    /**
     * @Route("/upload-file", name="upload_file")
     */
    public function uploadFile(Request $request)
    {
        $upload = new Upload();
        $form = $this->createForm(UploadType::class, $upload);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileName = $this->uploadService->upload($form->get('file')->getData());

            $upload
                ->setFileName($fileName)
                ->setStatus(Upload::STATUS_PENDING);

            $this->invoiceRepository->save($upload);

            $this->messageBus->dispatch(new ProcessCsvCommand($upload->getId()));

            return $this->redirectToRoute('uploads');
        }

        return $this->render('default/uploadFile.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
