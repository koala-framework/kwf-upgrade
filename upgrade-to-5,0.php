#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';

if (is_file('vkwf_branch') || is_file('kwf_branch')) {
    die("This script will update from 4.4, update to 4.4 first.\n");
}
if (!is_file('composer.json')) {
    die("composer.json not found.\n");
}

$changed = false;
$c = json_decode(file_get_contents('composer.json'));
foreach ($c->require as $packageName=>$packageVersion) {
    if (substr($packageVersion, 0, 4) == "4.4.") {
        $c->require->$packageName = '5.0.x-dev';
        $changed = true;
    }
}
if (!$changed) {
    die("This script will update from 4.4, update to 4.4 first.\n");
}

file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));


$htaccess = file_get_contents('.htaccess');
$htaccess = str_replace("RewriteEngine on\n", "RewriteEngine on\n\nRewriteRule ^assets/build/(.*)$ build/assets/$1 [L]\n", $htaccess);
$htaccess = preg_replace("#^RewriteRule .* bootstrap\.php \[L\]#m", "RewriteCond %{REQUEST_URI} !/build/assets/\n$0", $htaccess);
file_put_contents('.htaccess', $htaccess);

$files = array_merge(
    glob_recursive('*.js')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('require(\'kwf/', 'require(\'kwf/commonjs/', $c);
    $c = preg_replace('#(kwfTrl|t).trl(p?c?(Kwf)?)\(#', '__trl$2(', $c);
    $c = preg_replace("#var (kwfTrl|t) *= *require\('kwf/commonjs/trl'\);\n#", '', $c);
    $c = preg_replace("#^ *kwfTrl: *kwfTrl,? *\n#m", '', $c);
    $c = preg_replace("#^ *ret.kwfTrl *= *kwfTrl; *\n#m", '', $c);
    $c = preg_replace("#kwf-jquery-plugin/(.*)#", 'kwf-webpack/loader/jquery-plugin-loader!$1', $c);

    if ($c != $origC) {
        file_put_contents($file, $c);
    }
}

$files = array_merge(
    glob_recursive('*.underscore.tpl')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = preg_replace('#kwfTrl.trl(p?c?(Kwf)?)\(#', '__trl$1(', $c);
    if ($c != $origC) {
        file_put_contents($file, $c);
    }
}

$files = array_merge(
    glob_recursive('*.php')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('require(\'kwf/', 'require(\'kwf/commonjs/', $c);
    $c = preg_replace_callback('#\$ret\[\'assets\'\]\[\'dep\'\]\[\] = \'FontFace(.*)\';#', function($m) {
        $font = strtolower($m[1]);
        if (!file_exists('vendor/bower_components/'.$font)) {
            $font = $font .'-fonts';
        }
        return '$ret[\'assets\'][\'files\'][] = \''.$font.'/fonts.css\';';
    }, $c);
    if ($c != $origC) {
        file_put_contents($file, $c);
    }
}

$files = array_merge(
    glob_recursive('dependencies.ini')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;

    //for now only web/js/* is converted
    //probably add more?
    //or we could also support that in isi-loader (tough that is harder)
    $c = preg_replace_callback("#Admin.files\[\] = web/js/\*\n#", function($m) {
        $ret = '';
        foreach (glob_recursive('js/*.js') as $i) {
            $ret .= "Admin.files[] = web/$i\n";
        }
        return $ret;
    }, $c);

    $c = str_replace('vendor/vivid-planet/api-check-version', 'api-check-version', $c);
    if ($c != $origC) {
        file_put_contents($file, $c);
    }
}


$webpackConfig = "'use strict';
const WebpackConfig = require('webpack-config');

module.exports = new WebpackConfig.Config().extend(
    'kwf-webpack/config/webpack.kwc.config.js'
).merge({

});";
file_put_contents('webpack.config.js', $webpackConfig);


echo "\n";
echo "run now 'composer update' to update dependencies\n";

