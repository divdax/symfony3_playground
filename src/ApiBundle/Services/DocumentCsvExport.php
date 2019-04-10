<?php

namespace ApiBundle\Services;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

class DocumentCsvExport
{
    protected $documents = [];
    
    protected $csv;
    
    public function setDocuments(array $documents)
    {
        $this->documents = $documents;
    }

    public function saveCsv($path = null)
    {
        $this->generateCsv();
        
        return file_put_contents($path, $this->csv);
    }

    public function generateCsv()
    {
        $content = $this->csvContent();
        
        $serializer = new Serializer([], [new CsvEncoder()]);

        $this->csv = $serializer->encode($content, 'csv');

        return $this->csv;
    }
    
    private function csvContent()
    {
        $data = [];
        
        foreach($this->documents as $document) {
            $data[] = [
                'Typ' => $document->type,
                'Dokument-Nr.' => $document->number,
                'Datum' => $document->document_date,
                'Netto' => $document->amount_net,
                'Brutto' => $document->amount,
            ];
        }

        return $data;
    }
}