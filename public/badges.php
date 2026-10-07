<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Helpers\Url;

header('Location: ' . baseUrl('certificates.php#badges'));
exit;
