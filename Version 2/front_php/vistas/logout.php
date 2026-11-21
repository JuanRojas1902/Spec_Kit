<?php
declare(strict_types=1);
session_start();
session_unset();
session_destroy();

// Redirige al login principal
header('Location: index.php?vista=login');
exit;