<?php
require_once __DIR__ . '/../app/autoload.php';
$repo = new App\Repositories\CommunityRepository();
$groups = $repo->getAllGroups(false);
echo json_encode($groups, JSON_PRETTY_PRINT);
