<?php

function ociSdkAutoload($dir) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            ociSdkAutoload($path);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) == 'php') {
            require_once $path;
        }
    }
}

ociSdkAutoload(__DIR__ . '/src/Oci/Common');
ociSdkAutoload(__DIR__ . '/src/Oci/ObjectStorage');
