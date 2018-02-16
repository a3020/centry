<?php

namespace A3020\Centry\BlockType;

use A3020\Centry\Payload\PayloadAbstract;
use Concrete\Core\Database\Connection\Connection;

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
        return $this->getBlockTypes();
    }

    private function getBlockTypes()
    {
        $blockTypes = $this->db->fetchAll('
            SELECT * FROM BlockTypes
            ORDER BY btID
        ');

        $btHandles = $this->getDefaultBlockTypeHandles();
        $blockTypes = array_filter($blockTypes, function($blockType) use ($btHandles) {
            return !in_array($blockType['btHandle'], $btHandles);
        });

        $blockTypes = array_values($blockTypes);

        return array_map(function($blockType) {
            return array(
                'bt_id' => $blockType['btID'],
                'bt_handle' => $blockType['btHandle'],
                'bt_name' => $blockType['btName'],
                'bt_description' => $blockType['btDescription'],
            );
        }, $blockTypes);
    }

    private function getDefaultBlockTypeHandles()
    {
        // concrete/blocks
        $directory = DIR_BASE_CORE . DIRECTORY_SEPARATOR . DIRNAME_BLOCKS;

        return array_values(array_diff(scandir($directory), array('..', '.')));
    }
}
