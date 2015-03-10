<?php
//own script so it can be used in other repositories that contain update scripts

$directory = new RecursiveDirectoryIterator('.');
$filter = new RecursiveCallbackFilterIterator($directory, function ($current, $key, $iterator) {
    if ($current->getFilename()[0] === '.') {
        return false;
    }
    if ($current->isDir()) {
        // Only recurse into intended subdirectories.
        return $current->getFilename() != 'vendor';
    } else {
        // Only return files in folders named 'Update'
        return $current->getPathInfo()->getFileName() == 'Update';
    }
});
$iterator = new RecursiveIteratorIterator($filter);
$datePrefix = date('Ymd');
foreach ($iterator as $file) {
    $path = $file->getPathName();
    if (!$file->isFile()) continue;
    $ext = $file->getExtension();
    if ($ext != 'sql' && $ext != 'php') continue;
    $u = $file->getBasename('.'.$ext);
    if (!is_numeric($u)) continue;
    $uPadded = str_pad($u, 5, '0', STR_PAD_LEFT);
    $newFile = $file->getPath().'/'.$datePrefix.'Legacy'.$uPadded.'.'.$ext;
    rename($file->getPathName(), $newFile);
    if ($ext == 'php') {
        $c = file_get_contents($newFile);
        $newClassName = "Update_".$datePrefix.'Legacy'.$uPadded;
        $c = preg_replace("#class ([A-Za-z_]*)Update_$u#", "class $1$newClassName", $c);
        file_put_contents($newFile, $c);
    }
}
