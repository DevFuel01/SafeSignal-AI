<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

session_destroy();
jsonResponse(true, 'Logged out successfully.');
