<?php

/**
 * SafeSignal AI - Password Hash Generator
 * Run this ONCE to get the correct hash for Admin@123
 * Then update the seed.sql file with that hash.
 * 
 * Access: http://localhost/SafeSignal/generate_hash.php
 * DELETE THIS FILE AFTER USE!
 */

$password = 'Admin@123';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

header('Content-Type: text/plain');
echo "Password: {$password}\n";
echo "Hash: {$hash}\n\n";
echo "Use this SQL to update users:\n";
echo "UPDATE users SET password_hash = '{$hash}' WHERE id IN (1,2,3);\n";
