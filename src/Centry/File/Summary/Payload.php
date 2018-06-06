<?php

namespace A3020\Centry\File\Summary;

use A3020\Centry\Payload\PayloadAbstract;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\File\File;
use Concrete\Core\File\FileList;

final class Payload extends PayloadAbstract
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function jsonSerialize()
    {
        return [
            'total_number' => $this->getTotalNumberOfFiles(),
            'total_bytes' => $this->getTotalBytes(),
            'biggest_files' => $this->getBiggestFiles(),
            'biggest_images' => $this->getBiggestImages(),
        ];
    }

    private function getTotalNumberOfFiles()
    {
        return (int) $this->db->fetchColumn('
            SELECT COUNT(1) 
            FROM Files
        ');
    }

    private function getTotalBytes()
    {
        return (int) $this->db->fetchColumn('
            SELECT SUM(fvSize) 
            FROM FileVersions
        ');
    }

    private function getBiggestFiles()
    {
        $fl = new FileList();
        $fl->sortBy('fvSize', 'desc');
        $fl->getQueryObject()->setMaxResults(10);

        return $this->normalizeFiles($fl->executeGetResults());
    }

    private function getBiggestImages()
    {
        $fl = new FileList();
        $fl->filterByType(\Concrete\Core\File\Type\Type::T_IMAGE);
        $fl->sortBy('fvSize', 'desc');
        $fl->getQueryObject()->setMaxResults(10);

        return $this->normalizeFiles($fl->executeGetResults());
    }

    private function normalizeFiles($files)
    {
        return array_map(function($file) {
            $fileObject = File::getByID($file['fID']);
            if (!$fileObject) {
                return;
            }

            return [
                'file_id' => (int) $fileObject->getFileID(),
                'file_name' => (string) $fileObject->getVersion()->getFileName(),
                'file_size' => (int) $fileObject->getVersion()->getFullSize(),
                'file_url' => (string) $fileObject->getVersion()->getDownloadURL(),
            ];
        }, $files);
    }
}
