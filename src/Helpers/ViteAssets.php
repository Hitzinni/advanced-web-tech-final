<?php

// Simple Vite Asset Helper for PHP
// In development, loads assets from the Vite dev server.
// In production, loads assets from the manifest file.

const VITE_HOST = 'http://localhost:5173'; // Default Vite dev server

function vite_asset(string $entry): string
{
    $manifestPath = __DIR__ . '/../../public/dist/manifest.json';
    $isDev = file_exists(__DIR__ . '/../../public/hot'); // Check for Vite's 'hot' file

    if ($isDev) {
        return VITE_HOST . '/' . $entry;
    }

    if (!file_exists($manifestPath)) {
        error_log("Manifest file not found: {$manifestPath}");
        return ''; // Or throw an exception
    }

    $manifest = json_decode(file_get_contents($manifestPath), true);

    if (!isset($manifest[$entry])) {
        error_log("Entry not found in manifest: {$entry}");
        return ''; // Or throw an exception
    }

    // Construct the base path correctly
    // Assumes 'public/dist' is accessible relative to the web root
    $basePath = '/dist/'; 

    $tags = '';

    // Include CSS files if they exist for the entry
    if (!empty($manifest[$entry]['css'])) {
        foreach ($manifest[$entry]['css'] as $cssFile) {
            $tags .= sprintf('<link rel="stylesheet" href="%s">', $basePath . $cssFile);
        }
    }

    // Include the main JS file
    $tags .= sprintf('<script type="module" src="%s"></script>', $basePath . $manifest[$entry]['file']);

    return $tags;
}

function vite_react_refresh_runtime(): string
{
    $isDev = file_exists(__DIR__ . '/../../public/hot'); // Check for Vite's 'hot' file

    if ($isDev) {
        return '<script type="module"> import RefreshRuntime from "' . VITE_HOST . '/@react-refresh"; RefreshRuntime.injectIntoGlobalHook(window); window.$RefreshReg$ = () => {}; window.$RefreshSig$ = () => (type) => type; window.__vite_plugin_react_preamble_installed__ = true; </script>';
    }
    return '';
} 