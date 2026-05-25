<?php
/**
 * Front controller fallback for shared hostings where DocumentRoot points to project root.
 * Prefer pointing DocumentRoot to /public when possible.
 */
require __DIR__ . '/public/index.php';
