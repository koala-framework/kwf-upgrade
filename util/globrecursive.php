<?php
function glob_recursive($pattern, $flags = 0) {
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        if (dirname($dir) == './kwf-lib' || $dir == './kwf-lib') continue;
        if (dirname($dir) == './vkwf-lib' || $dir == './vkwf-lib') continue;
        if (dirname($dir) == './library' || $dir == './library') continue;
        if (dirname($dir) == './vendor' || $dir == './vendor') continue;
        $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
    }
    return $files;
}
