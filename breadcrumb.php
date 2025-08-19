<?php
function generateBreadcrumb()
{
    $path = $_SERVER['REQUEST_URI']; // Get current URL path
    $parts = explode("/", trim($path, "/")); // Split URL into parts
    $breadcrumb = '<ul id="breadcrumb">';

    $breadcrumb .= '<li><a href="/homepage/homepage.html">Home</a></li>'; // Home link
    $link = "";

    foreach ($parts as $part) {
        $link .= "/" . $part;
        $name = ucwords(str_replace("-", " ", $part)); // Convert URL to readable text
        $breadcrumb .= '<li><a href="' . $link . '">' . $name . '</a></li>';
    }

    $breadcrumb .= '</ul>';
    return $breadcrumb;
}
