<?php
if (!is_dir('symfony')) {
    echo "Added symfony folder\n";
    system("cp -r " . __DIR__ . "/symfony " . getcwd());
}

$c = file_get_contents('bootstrap.php');
if (!preg_match("#\/kwf\/symfony#", $c)) {
    $needle = "::setUp();";
    $pos = strpos($c, $needle) + strlen($needle);

    $c = insertStringOnPosition($c, file_get_contents(__DIR__ . '/bootstrap.tpl'), $pos);

    echo "Added symfony kernel in bootstrap.php\n";
    file_put_contents('bootstrap.php', $c);
}

$c = file_get_contents('config.ini');
$config = parse_ini_string($c, true);
if (!array_key_exists('symfony.kernelClass', $config['production'])) {
    $addConfig = "symfony.kernelClass = AppKernel\n";
    $c = str_replace("[production]\n", "[production]\n{$addConfig}", $c);

    echo "Added symfony.kernelClass in config.ini\n";
    file_put_contents('config.ini', $c);
}

if (!is_dir('cache/symfony')) {
    mkdir('cache/symfony');
    echo "Added symfony cache folder\n";
    file_put_contents('cache/symfony/.gitignore', "*\n!.gitignore\n");
    system("git add cache/symfony/.gitignore");
}

if (!is_dir('log/symfony')) {
    mkdir('log/symfony');
    echo "Added symfony log folder\n";
    file_put_contents('log/symfony/.gitignore', "*\n!.gitignore\n");
    system("git add log/symfony/.gitignore");
}

$c = json_decode(file_get_contents('composer.json'));
$originalC = clone $c;
if (!isset($c->autoload)) $c->autoload = json_decode("{}");
if (!isset($c->autoload->classmap)) $c->autoload->classmap = array();
if (!in_array('symfony/AppKernel.php', $c->autoload->classmap)) $c->autoload->classmap[] = 'symfony/AppKernel.php';

$packages = array(
    "symfony/symfony" => "^2.8",
    "symfony/monolog-bundle" => "^3.1",
    "sensio/framework-extra-bundle" => "^3.0",
    "friendsofsymfony/rest-bundle" => "^1.8",
    "nelmio/api-doc-bundle" => "^2.13"
);
foreach ($packages as $package => $version) {
    if (!isset($c->require->{$package})) {
        $c->require->{$package} = $version;
    }
}
if (json_encode($c) !== json_encode($originalC)) {
    echo "Added symfony packages and classmap in composer.json\n";
    file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));
}


function insertStringOnPosition($original, $new, $position)
{
    return substr($original, 0, $position) . $new . substr($original, $position);
}
