<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

auth_logout();
auth_redirect('login.php');

