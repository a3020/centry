<?php

namespace A3020\Centry\Page\Summary;

use A3020\Centry\Payload\PayloadAbstract;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Page\Page;

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
            'total_pages' => $this->getTotalNumberOfPages(),
            'unapproved_pages' => $this->getUnapprovedPages(),
        ];
    }

    private function getTotalNumberOfPages()
    {
        return (int) $this->db->fetchColumn('
            SELECT COUNT(1) FROM Pages 
            WHERE cIsTemplate = 0 AND cIsActive = 1 AND cIsSystemPage = 0
        ');
    }

    private function getUnapprovedPages()
    {
        $ids = (array) $this->db->fetchColumn('
        SELECT cv.cID FROM (
            SELECT cv.cID, max(cv.cvID) AS cvID FROM CollectionVersions AS cv
            INNER JOIN Pages AS p ON p.cID = cv.cID
            WHERE p.cIsSystemPage = 0 AND p.cIsActive = 1 AND p.uID > 0
            GROUP BY cv.cID
        ) tmp
        INNER JOIN CollectionVersions AS cv ON cv.cID = tmp.cID AND cv.cvID = tmp.cvID 
        WHERE cv.cvIsApproved = 0
        ');

        $pages = [];
        foreach ($ids as $id ) {
            $page = Page::getByID($id);
            if (!$page || $page->isError()) {
                continue;
            }

            $pages[] = [
                'name' => $page->getCollectionName(),
                'url' => $page->getCollectionLink(true),
            ];
        }

        return $pages;
    }
}
