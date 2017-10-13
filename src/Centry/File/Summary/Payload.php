<?php

namespace A3020\Centry\File\Summary;

use A3020\Centry\Payload\PayloadAbstract;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\File\File;

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
        $files = $this->db->fetchAll('
            SELECT fID, fvFilename, fvSize 
            FROM FileVersions
            GROUP BY fvID, fID
            ORDER BY fvSize DESC
            LIMIT 0, 10
        ');

        return $this->normalizeFiles($files);
    }

    private function getBiggestImages()
    {
        $files = $this->db->fetchAll('
            SELECT fID, fvFilename, fvSize 
            FROM FileVersions
            GROUP BY fvID, fID
            ORDER BY fvSize DESC
            LIMIT 0, 10
        ');

        return $this->normalizeFiles($files);
    }

    private function normalizeFiles($files)
    {
        return array_map(function($file) {
            $fileObject = File::getByID($file['fID']);
            if (!$fileObject) {
                return;
            }

            return [
                'file_id' => $file['fID'],
                'file_name' => $file['fvFilename'],
                'file_size' => $file['fvSize'],
                'file_url' => $fileObject->getVersion()->getDownloadURL(),
            ];
        }, $files);
    }
}
