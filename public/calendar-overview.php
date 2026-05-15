<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

header('Location: ' . app_url('calendar.php?view=list'), true, 302);
exit;
