#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';

$file = is_file('vkwf_branch') ? 'vkwf_branch' : 'kwf_branch';
if (!file_exists($file)) die("Execute this script in app root.\n");
if (trim(file_get_contents($file)) != '3.3') die("This script will update from 3.3, update to 3.3 manually first.\n");

file_put_contents($file, "3.4\n");
echo "Changed $file to 3.4\n";

function checkGallery($files) {
    foreach ($files as $f) {
        $content = file_get_contents($f);
        if (strpos($content, 'Kwc_List_Gallery_Component')) {
            echo ("$f: Gallery changed the image enlarge tag from LinkTag to EnlargeTag. Please make sure that EnlargeTag is ok or change the component to Kwc_List_ImagesLinked_Component\n");
        }
    }
}

function checkBaseProperties($files) {
    foreach ($files as $f) {
        $content = file_get_contents($f);
        if (strpos($content, 'hasDomain')) {
            echo "\033[45mPlease change setting hasDomain to BaseProperties\033[00m\n";
        }
        if (strpos($content, 'hasLanguage')) {
            echo "\033[45mPlease change setting hasLanguage to BaseProperties\033[00m\n";
        }
        if (strpos($content, 'hasMoneyFormat')) {
            echo "\033[45mPlease change setting hasMoneyFormat to BaseProperties\033[00m\n";
        }
    }
}

function updateStatisticsConfig()
{
    $content = file_get_contents('config.ini');
    $original = $content;
    $content = str_replace('statistic.', 'statistics.', $content);
    $content = str_replace('moneyFormat', 'money.format', $content);
    $content = str_replace('moneyDecimals', 'money.decimals', $content);
    $content = str_replace('moneyDecimalSeparator', 'money.decimalSeparator', $content);
    $content = str_replace('moneyThousandSeparator', 'money.thousandSeparator', $content);
    $content = preg_replace('/kwc\.domains\.([a-z]*)\.piwikId/', 'kwc.domains.$1.statistics.piwikId', $content);
    $content = preg_replace('/kwc\.domains\.([a-z]*)\.piwikDomain/', 'kwc.domains.$1.statistics.piwikDomain', $content);
    $content = preg_replace('/kwc\.domains\.([a-z]*)\.twynCustomerId/', 'kwc.domains.$1.statistics.twynCustomerId', $content);
    $content = preg_replace('/kwc\.domains\.([a-z]*)\.analyticsCode/', 'kwc.domains.$1.statistics.analyticsCode', $content);
    $content = preg_replace('/kwc\.domains\.([a-z]*)\.ignoreAnalyticsCode/', 'kwc.domains.$1.statistics.ignoreAnalyticsCode', $content);
    $content = preg_replace('/kwc\.domains\.([a-z]*)\.ignorePiwikCode/', 'kwc.domains.$1.statistics.ignorePiwikCode', $content);
    $content = str_replace('piwikId', 'piwik.id', $content);
    $content = str_replace('piwikDomain', 'piwik.domain', $content);
    $content = str_replace('ignorePiwikCode', 'piwik.ignore', $content);
    $content = str_replace('twynCustomerId', 'twin.customerId', $content);
    $content = str_replace('analyticsCode', 'analytics.code', $content);
    $content = str_replace('ignoreAnalyticsCode', 'analytics.ignore', $content);
    if ($original != $content) {
        file_put_contents('config.ini', $content);
        echo "Updated statistics config\n";
    }
}

function updateConfig()
{
    $content = file_get_contents('config.ini');
    $original = $content;
    $content = str_replace('userModel = ', 'user.model = ', $content);
    $content = str_replace('kwc.pageTypes.', 'kwc.pageCategories.', $content);
    if ($original != $content) {
        file_put_contents('config.ini', $content);
        echo "Updated config\n";
    }
}

function replaceFiles($files, $from, $to) {
    foreach ($files as $f) {
        $content = file_get_contents($f);
        if (strpos($content, $from)) {
            file_put_contents($f, str_replace($from, $to, $content));
            echo "Change $f: $from -> $to\n";
        }
    }
}

function updateIncludeCode()
{
    $files = glob_recursive('Master.tpl');
    $removeBoxes = array('title', 'metatags', 'opengraph', 'piwik', 'analytics', 'assets', 'rssFeeds');
    foreach ($files as $f) {
        $c = file_get_contents($f);
        if (!strpos($c, 'this->includeCode')) {
            $c = str_replace("<head>\n", "<head>\n        <?=\$this->includeCode('header')?>\n", $c);
            $c = str_replace("</body>", "    <?=\$this->includeCode('footer')?>\n    </body>", $c);
            foreach ($removeBoxes as $b) {
                $c = preg_replace("#^\s*<\?=\\\$this->component\(\\\$this->boxes\['$b'\]\);\?> *\n#im", "", $c);
            }
            $c = preg_replace("#^\s*<\?=\\\$this->debugData\(\);\?> *\n#im", "", $c);
            $c = preg_replace("#^\s*<link rel=\"shortcut icon\" href=\"/assets/web/images/favicon\.ico\" /> *\n#im", "", $c);
            $c = preg_replace("#^\s*<\?=\\\$this->statisticCode\(\);\?> *\n#im", "", $c);
            $c = preg_replace("#^\s*<\?=\\\$this->assets\(\'Frontend\'\);\?> *\n#im", "", $c);
            file_put_contents($f, $c);
            echo "Updated $f to use new includeCode helper\n";
        }
    }
}

function updateMasterCssClass()
{
    $files = glob_recursive('Master.tpl');
    foreach ($files as $f) {
        $c = file_get_contents($f);
        $c = str_replace('<body class="frontend', '<body class="', $c);
        $c = str_replace('<body class="', '<body class="<?=$this->cssClass?>', $c);
        $c = str_replace('<body>', '<body class="<?=$this->cssClass?>">', $c);
        file_put_contents($f, $c);
        echo "Updated $f to use new cssClass\n";
    }
}

function moveCssFiles()
{
    if (file_exists('css/master.css') && file_exists('css/web.css')) {
        $c = file_get_contents('css/master.css')."\n\n".file_get_contents('css/web.css');
        file_put_contents("css/web.css", $c);
        unlink('css/master.css');
        echo "moved css/master.css contents into css/web.css\n";
    }
    if (file_exists('css/web.css') && file_exists('components/Root/Component.php')) {
        rename('css/web.css', 'components/Root/Web.css');
        echo "moved css/web.css to components/Root/Web.css\n";
        file_put_contents('components/Root/Master.scss', '/* move styling relevant for Master.tpl from Web.css in here*/');
        echo "created components/Root/Master.scss\n";
    }
    if (file_exists('css/web.scss') && file_exists('components/Root/Component.php')) {
        rename('css/web.scss', 'components/Root/Web.scss');
        echo "moved css/web.scss to components/Root/Web.scss\n";
    }
    if (file_exists('css/web.printcss') && file_exists('components/Root/Component.php')) {
        rename('css/web.printcss', 'components/Root/Web.printcss');
        echo "moved css/web.printcss to components/Root/Web.printcss\n";
    }
    if (file_exists('dependencies.ini')) {
        $c = file_get_contents('dependencies.ini');
        $c = str_replace("Frontend.files[] = web/css/master.css\n", '', $c);
        $c = str_replace("Frontend.files[] = web/css/web.css\n", '', $c);
        $c = str_replace("Frontend.files[] = web/css/web.scss\n", '', $c);
        $c = str_replace("Frontend.files[] = web/css/web.printcss\n", '', $c);
        file_put_contents('dependencies.ini', $c);
        echo "updated dependencies.ini\n";
    }
}

function updateAclTrl()
{
    $c = file_get_contents('app/Acl.php');
    $c = preg_replace('#(trl[cp]?(Kwf)?)\\(#', '\1Static(', $c);
    file_put_contents('app/Acl.php', $c);
    echo "updated app/Acl.php to use trlStatic\n";
}



function updateComponents()
{
    $info = _updateComponentsDir('components');
    $ret = "\n";
    if ($info['templates']) {
        $count = count($info['templates']);
        $ret .= "   following $count Templates have been adapted to new ifHasContent:\n";
        foreach ($info['templates'] as $file) {
            $ret .= "        $file\n";
        }
    }
    if ($info['componentName']) {
        $count = count($info['componentName']);
        $ret .= "   following $count Components have been adapted to new static componentName:\n";
        foreach ($info['componentName'] as $file) {
            $ret .= "        $file\n";
        }
    }
    if ($info['getCacheVars']) {
        $count = count($info['getCacheVars']);
        $ret .= "   Following $count Components have getCacheVars overridden and need to be adapted to getCacheMeta:\n";
        foreach ($info['getCacheVars'] as $file) {
            $ret .= "        $file\n";
        }
    }
    if ($info['getStaticCacheVars']) {
        $count = count($info['getStaticCacheVars']);
        $ret .= "   Following $count Components have getStaticCacheVars overridden and need to be adapted to getStaticCacheMeta:\n";
        foreach ($info['getStaticCacheVars'] as $file) {
            $ret .= "        $file\n";
        }
    }
    return $ret;
        /* zum Testen
        $string = '
<?=$this->ifHasContent($this->item);?>
<?=$this->ifHasContent();?>

<?php echo $this->ifHasContent ( $this->item ) ; ?>
<?php echo $this->ifHasContent ( ) ; ?>

<?=$this->ifHasNoContent($this->item) ;?>
<?=$this->ifHasNoContent() ;?>
        ';
        d($this->_replaceHasContent($string));
        */
}

function _updateComponentsDir($dir)
{
    $ret = array(
        'templates' => array(),
        'getCacheVars' => array(),
        'getStaticCacheVars' => array(),
        'componentName' => array()
    );
    $count = 0;
    foreach (new DirectoryIterator($dir) as $i) {
        if (substr($i, 0, 1) == '.') continue;
        $path = $dir . '/' . $i;
        if (substr($path, -4) == '.tpl') {
            $original = file_get_contents($path);
            $new = _updateComponentsReplaceHasContent($original);
            file_put_contents($path, $new);
            if ($original != $new) $ret['templates'][] = $path;
        }


        if ($i == 'Component.php') {
            $string = file_get_contents($path);
            if (strpos($string, 'getCacheVars') !== false) {
                $ret['getCacheVars'][] = $path;
            }
            if (strpos($string, 'getStaticCacheVars') !== false) {
                $ret['getStaticCacheVars'][] = $path;
            }
            $string2 = _updateComponentsSettings($string);
            if ($string2 != $string) {
                $ret['componentName'][] = $path;
                file_put_contents($path, $string2);
            }

        }

        if (!is_dir($path)) continue;
        $ret2 = _updateComponentsDir($path);
        foreach ($ret as $key => $r) {
            $ret[$key] = array_merge($r, $ret2[$key]);
        }
    }
    return $ret;
}

function _updateComponentsReplaceHasContent($string)
{
    $pattern = '/(=|echo)\s*\$this->ifHasContent\s*\(\s*(\S+)\s*\)\s*;?/i';
    $replacement = 'if (\$this->hasContent($2)) {';
    $string = preg_replace($pattern, $replacement, $string);
    $pattern = '/(=|echo)\s*\$this->ifHasNoContent\s*\(\s*(\S+)\s*\)\s*;?/i';
    $replacement = 'if (!\$this->hasContent($2)) {';
    $string = preg_replace($pattern, $replacement, $string);
    $pattern = '/(=|echo)\s*\$this->ifHas(No)?Content\s*\(\s*\)\s*;?/i';
    $replacement = '}';
    $string = preg_replace($pattern, $replacement, $string);

    return $string;
}


function _updateComponentsSettings($string)
{
    if (preg_match("#getSettings\(.*?\)\s*{\n(.*?)return \\\$ret;\s+}#ims", $string, $m)) {
        $s = $m[1];
        if (preg_match("#(componentName['\"]\]\s*= )(.*);#", $s, $m2)) {
            $replaced = $m2[2];
            $replaced = preg_replace('#trl(Kwf)?\\(#', 'trl\1Static(', $replaced);
            $s = str_replace($m2[0], $m2[1].$replaced.';', $s);
        }
        if (preg_match("#(componentName['\"]\s*=> )(.*),#", $s, $m2)) {
            $replaced = $m2[2];
            $replaced = preg_replace('#trl(Kwf)?\\(#', 'trl\1Static(', $replaced);
            $s = str_replace($m2[0], $m2[1].$replaced.',', $s);
        }
        $string = str_replace($m[1], $s, $string);
    }
    return $string;
}


$files = glob_recursive('Component.php');
$files = array_merge($files, glob_recursive('config.ini'));
replaceFiles($files, 'Kwc_Composite_Images_Component', 'Kwc_List_Images_Component');
replaceFiles($files, 'Kwc_Composite_LinksImages_Component', 'Kwc_List_ImagesLinked_Component');
replaceFiles($files, 'Kwc_Composite_Downloads_Component', 'Kwc_List_Downloads_Component');
replaceFiles($files, 'Kwc_Composite_ImagesEnlarge_Component', 'Kwc_List_Gallery_Component');
replaceFiles($files, 'Kwc_Composite_Links_Component', 'Kwc_List_Links_Component');
replaceFiles($files, 'Kwc_Box_Analytics_Component', 'Kwc_Statistics_Analytics_Component');
replaceFiles($files, 'Kwc_Root_DomainRoot_Domain_Analytics_Component', 'Kwc_Statistics_Analytics_Component');
replaceFiles($files, 'Kwc_Root_DomainRoot_Domain_AdsenseAnalytics_Component', 'Kwc_Statistics_Adsense_Component');
checkGallery($files);
checkBaseProperties($files);
updateStatisticsConfig();
updateIncludeCode();
updateMasterCssClass();
moveCssFiles();
updateAclTrl();
updateConfig();
updateComponents();
