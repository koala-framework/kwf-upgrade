#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';
require __DIR__.'/util/deleteCacheFolder.php';

if (is_file('vkwf_branch') || is_file('kwf_branch')) {
    die("This script will update from 4.5, update to 4.5 first.\n");
}
if (!is_file('composer.json')) {
    die("composer.json not found.\n");
}

$changed = false;
$c = json_decode(file_get_contents('composer.json'));
foreach ($c->require as $packageName=>$packageVersion) {
    if (substr($packageVersion, 0, 4) == "4.5.") {
        $c->require->$packageName = '5.0.x-dev';
        $changed = true;
    }
}
$extraWebpackConfig = "";
if (isset($c->require->{'koala-framework/kwc-susy'})) {
    $c->require->{'koala-framework/kwc-susy'} = '1.2.x-dev';
}
if (isset($c->require->{'koala-framework/kwc-flickity'})) {
    $c->require->{'koala-framework/kwc-flickity'} = '2.1.x-dev';
}
if (isset($c->require->{'vivid-planet/poi-tealium'})) {
    $c->require->{'vivid-planet/poi-tealium'} = '1.1.x-dev';
}
if (isset($c->require->{'vivid-planet/poi-tools'})) {
    $c->require->{'vivid-planet/poi-tools'} = '2.1.x-dev';
}
if (isset($c->require->{'vivid-planet/poi-contact'})) {
    $c->require->{'vivid-planet/poi-contact'} = '1.1.x-dev';
}
if (isset($c->require->{'vivid-planet/partnernet'})) {
    $c->require->{'vivid-planet/partnernet'} = '2.2.x-dev';
}
if (isset($c->require->{'koala-framework/kwf-reactjs'})) {
    unset($c->require->{'koala-framework/kwf-reactjs'});
    $c->extra->{'require-npm'}->{'react'} = "^15.3.0";
    $c->extra->{'require-npm'}->{'react-dom'} = "^15.3.0";
    $c->extra->{'require-npm'}->{'babel-preset-react'} = "^6.11.1";
$extraWebpackConfig .= "
    module: {
        rules: [{
            test: /\.jsx$/,
            exclude: /node_modules/,
            loader: 'babel-loader',
            options: {
                presets: ['es2015', 'react']
            }
        }]
    },
    resolve: {
        extensions: ['.jsx']
    }";
}
if (!$changed) {
    die("This script will update from 4.5, update to 4.5 first.\n");
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
    $c = preg_replace('#(kwfTrl|t)\.trl(p?c?(Kwf)?)\(#', '__trl$2(', $c);
    $c = preg_replace("#var (kwfTrl|t) *= *require\('kwf/commonjs/trl'\);\n#", '', $c);
    $c = preg_replace("#^ *kwfTrl: *kwfTrl,? *\n#m", '', $c);
    $c = preg_replace("#^ *ret.kwfTrl *= *kwfTrl; *\n#m", '', $c);
    $c = preg_replace("#kwf-jquery-plugin/(.*)#", 'kwf-webpack/loader/jquery-plugin-loader!$1', $c);
    if (file_exists(substr($file, 0, -3).'.scss') && strpos($c, 'require(') !== false) {
        $c = "require('.".substr($file, strrpos($file, '/'), -3).'.scss'."');\n".$c;
    }

    if ($c != $origC) {
        file_put_contents($file, $c);
    }
}

$files = array_merge(
    glob_recursive('*.js'),
    glob_recursive('*.jsx')
);

foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = preg_replace('#([\'"])/(kwf|vkwf|admin|assets|api)#', 'KWF_BASE_URL+$1/$2', $c);
    if ($c != $origC) {
        file_put_contents($file, $c);
    }
}

$files = array_merge(
    glob_recursive('*.jsx')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    //TODO
    $c = str_replace('from \'kwf/', 'from \'kwf/commonjs/', $c);
    //$c = preg_replace('#(kwfTrl|t)\.trl(p?c?(Kwf)?)\(#', '__trl$2(', $c);
    //$c = preg_replace("#var (kwfTrl|t) *= *require\('kwf/commonjs/trl'\);\n#", '', $c);
    //$c = preg_replace("#^ *kwfTrl: *kwfTrl,? *\n#m", '', $c);
    //$c = preg_replace("#^ *ret.kwfTrl *= *kwfTrl; *\n#m", '', $c);
    $c = preg_replace("#kwf-jquery-plugin/(.*)#", 'kwf-webpack/loader/jquery-plugin-loader!$1', $c);
    if (file_exists(substr($file, 0, -4).'.scss') && strpos($c, 'import ') !== false) {
        $c = "import '.".substr($file, strrpos($file, '/'), -4).'.scss'."';\n".$c;
    }

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
    glob_recursive('*.css'),
    glob_recursive('*.scss')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;

    $c = preg_replace('#(url *\( ?[\'"]? ?)/assets/#', '$1~', $c);
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
    //or we could also support that in ini-loader (tough that is harder)
    $c = preg_replace_callback("#Admin.files\[\] = web/js/\*\n#", function($m) {
        $ret = '';
        foreach (glob_recursive('js/*.js') as $i) {
            $ret .= "Admin.files[] = web/$i\n";
        }
        return $ret;
    }, $c);

    $c = str_replace('vendor/vivid-planet/api-check-version', 'apiCheckVersion', $c);
    if ($c != $origC) {
        file_put_contents($file, $c);
    }
}

$webpackConfig = "'use strict';
const WebpackConfig = require('webpack-config');

module.exports = new WebpackConfig.Config().extend(
    'kwf-webpack/config/webpack.kwc.config.js'
).merge({
$extraWebpackConfig
});
";
file_put_contents('webpack.config.js', $webpackConfig);
system('git add webpack.config.js');

if (file_exists('dependencies.ini') && file_exists('themes/Theme/Component.php')) {
    $c = file_get_contents('dependencies.ini');
    var_dump($c);
    if (preg_match("/^Frontend\\.files\\[\\] = https:\\/\\/fast\\.fonts\\.net\\/cssapi\\/(.*)\\.css\n?/m", $c, $m)) {
        $projectId = $m[1];
        $c = str_replace($m[0], '', $c);
        file_put_contents('dependencies.ini', $c);


        $js = "var WebFont = require('webfontloader');\n".
            "WebFont.load({\n".
            "    monotype: {\n".
            "        projectId: '$projectId'\n".
            "   }\n".
            "});\n";
        if (!file_exists('themes/Theme/Web.js')) {
            $c = file_get_contents('themes/Theme/Component.php');
            $c = preg_replace("#web/themes/Theme/Master.js';\n( *)#", "$0\$ret['assets']['files'][] = 'web/themes/Theme/Web.js';\n$1", $c);
            file_put_contents('themes/Theme/Component.php', $c);
            file_put_contents('themes/Theme/Web.js', $js);
            system('git add themes/Theme/Web.js');
        } else {
            file_put_contents('themes/Theme/Web.js', $js, FILE_APPEND);
        }
    }
}

if (!is_dir('cache/webpack')) {
    mkdir('cache/webpack');
    file_put_contents('cache/webpack/.gitignore', "*\n!.gitignore\n");
    system("git add cache/webpack/.gitignore");
}

deleteCacheFolder('cache/assetdeps');
deleteCacheFolder('cache/commonjs');
deleteCacheFolder('cache/uglifyjs');

echo "\n";
echo "run now 'composer update' to update dependencies\n";

