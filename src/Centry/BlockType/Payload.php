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
        return [
            "core_area_layout",
            "core_page_type_composer_control_output",
            "core_scrapbook_display",
            "core_stack_display",
            "core_conversation",
            "autonav",
            "content",
            "date_navigation",
            "external_form",
            "file",
            "page_attribute_display",
            "form",
            "page_title",
            "feature",
            "topic_list",
            "social_links",
            "testimonial",
            "share_this_page",
            "google_map",
            "html",
            "horizontal_rule",
            "image",
            "faq",
            "next_previous",
            "page_list",
            "rss_displayer",
            "search",
            "image_slider",
            "survey",
            "switch_language",
            "tags",
            "video",
            "youtube",
            "express_form",
            "express_entry_list",
            "express_entry_detail",
            "desktop_site_activity",
            "desktop_app_status",
            "desktop_featured_theme",
            "desktop_featured_addon",
            "desktop_newsflow_latest",
            "desktop_latest_form",
            "desktop_waiting_for_me",
        ];
    }
}
