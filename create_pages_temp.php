<?php
require_once dirname(__FILE__) . '/wp-load.php';
require_once dirname(__FILE__) . '/wp-content/plugins/lopas/includes/class-page-creator.php';

LOPAS_Page_Creator::create_pages();
echo "Pages created successfully.\n";
