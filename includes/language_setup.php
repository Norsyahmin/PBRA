<?php
// This file contains the shared language setup logic and get_text function.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Make sure your database connection is available if needed by get_text or other parts.
// However, get_text itself doesn't need it.
// We'll assume mypbra_connect.php is included where this file is included.

// Language Definitions
$supported_languages = [
    'en' => ['name' => 'English'],
    'ms' => ['name' => 'Bahasa Melayu']
];
$default_language = 'en';

// Determine Current Language
if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $supported_languages)) {
    $_SESSION['language'] = $_GET['lang'];
} elseif (!isset($_SESSION['language']) || !array_key_exists($_SESSION['language'], $supported_languages)) {
    $_SESSION['language'] = $default_language;
}
$current_language = $_SESSION['language'];

// Include Language File
$lang_file = __DIR__ . '/../languages/' . $current_language . '.php';
if (file_exists($lang_file)) {
    include $lang_file;
} else {
    // Fallback to English if the specific language file doesn't exist
    include __DIR__ . '/../languages/en.php';
}

// Language Text Retrieval Function
if (!function_exists('get_text')) { // Prevent redeclaration if somehow included twice
    function get_text($key, $default = '')
    {
        global $current_language;
        global $$current_language; // This assumes your language files define variables like $en, $ms
        return isset($$current_language[$key]) ? $$current_language[$key] : $default;
    }
}
