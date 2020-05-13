<?php

namespace App\MessageHandler;

use App\Entity\Upload;
use App\Entity\Invoice;
use Psr\Log\LoggerInterface;
use App\Message\ProcessCsvCommand;
use App\Repository\UploadRepository;
use App\Repository\InvoiceRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProcessCsvCommandHandler implements MessageHandlerInterface
{
    /** @var JobRepository */
    private $invoiceRepository;

    /** @var UploadRepository */
    private $uploadRepository;

    /** @var LoggerInterface */
    private $logger;

    /** @var ParameterBagInterface */
    private $params;

    /**
     * Constructor
     * 
     * @param InvoiceRepository $invoiceRepository
     * @param LoggerInterface $messageLogger
     */
    public function __construct(
        InvoiceRepository $invoiceRepository,
        LoggerInterface $messageLogger,
        UploadRepository $uploadRepository,
        ParameterBagInterface $params
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->logger = $messageLogger;
        $this->uploadRepository = $uploadRepository;
        $this->params = $params;
    }

    /**
     * Invoke
     * 
     * @param ProcessCsvCommand $processCsvCommand
     */
    public function __invoke(ProcessCsvCommand $processCsvCommand)
    {
        $upload = $this->uploadRepository->find($processCsvCommand->getUploadId());
        if (!$upload) {
            return;
        }

        try {
            $file = fopen($this->params->get('kernel.project_dir') . '/public/upload/' . $upload->getFilename(), 'r');
            $errors = [];

            while ($line = fgets($file)) {
                $this->logger->info($line);
                $parts = explode(',', $line);

                if (count($parts) != 3) {
                    $errors[] = 'Incorrect line format: ' . $line;
                    $this->logger->error('Incorrect line format: ' . $line);
                    continue;
                }

                $dueOn = \DateTime::createFromFormat('Y-m-d', trim($parts[2]));

                if (!$dueOn) {
                    $errors[] = 'Incorrect date format: ' . $line;
                    $this->logger->error('Incorrect date format: ' . $line);
                    continue;
                }

                if (!(double)$parts[1]) {
                    $errors[] = 'Incorrect amount: ' . $line;
                    $this->logger->error('Incorrect amount: ' . $line);
                    continue;
                }

                $date = (new \DateTime())->add(new \DateInterval('P30D'));
                $coefficient = $date <= $dueOn ? 0.5 : 0.3;
                $sellingPrice = (double) $parts[1] * $coefficient;

                $invoice = new Invoice();
                $invoice
                    ->setInvoiceId($parts[0])
                    ->setAmount($parts[1])
                    ->setDueOn($dueOn)
                    ->setSellingPrice($sellingPrice);
                
                $this->invoiceRepository->persist($invoice);
            }

            $this->invoiceRepository->flush();

            $upload
                ->setStatus(Upload::STATUS_PROCESSED)
                ->setErrors($errors);

            $this->uploadRepository->save($upload);
        } catch (\Exception $e) {
            $this->logger->error($e);

            if (isset($upload)) {
                $upload->setStatus(Upload::STATUS_FAILED);
                $this->uploadRepository->save($upload);
            }
        } finally {
            if (isset($file)) {
                fclose($file);
            }
        }
    }   
}