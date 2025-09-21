<?php

// Always set German (de_DE) as default on page load
$_SESSION['lang'] = 'de_DE';

// Language switcher logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lang'])) {
    $lang = $_POST['lang'];
    if (in_array($lang, ['en_US', 'de_DE'])) {
        $_SESSION['lang'] = $lang;
    }
}

// Load the appropriate language file
$lang_file = __DIR__ . '/languages/' . $_SESSION['lang'] . '.php';
if (file_exists($lang_file)) {
    include_once $lang_file;
} else {
    include_once __DIR__ . '/languages/de_DE.php'; // Fallback to German
}

// Language switcher HTML
function language_switcher() {
    $current = $_SESSION['lang'];
    echo '<form method="post" id="language-switcher" class="ml-5 lang-switcher">
            <select name="lang" onchange="this.form.submit()">
                <option value="de_DE" ' . ($current == 'de_DE' ? 'selected' : '') . '>Deutsch</option>
                <option value="en_US" ' . ($current == 'en_US' ? 'selected' : '') . '>English</option>
            </select>
          </form>';
}

// Translation function
function __t($key) {
    global $lang;
    return $lang[$key] ?? $key;
}
?>
