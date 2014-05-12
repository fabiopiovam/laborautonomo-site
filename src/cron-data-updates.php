<?php
require __DIR__.'/api.class.php';
$app = require __DIR__.'/bootstrap.php';

$repositories = new ApiClient\Repositories($app);

echo "updating pages ...\r\n";
$repositories->update_project_pages();
echo "ok\r\n\r\n";
echo "updating releases ...\r\n"; 
$repositories->update_releases();
echo "ok\r\n\r\n";
