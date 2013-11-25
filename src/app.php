<?php

require_once __DIR__.'/../vendor/autoload.php'; 

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Michelf\Markdown;
use Symfony\Component\Yaml\Yaml;

$app = new Silex\Application(); 
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
    'asset.directory' => __DIR__.'/../styles',
));

$twig = $app['twig'];
$twig->addExtension(new \Entea\Twig\Extension\AssetExtension($app));


$app->error(function (\Exception $e, $code) {
    return $e->getMessage();
});

$app->get('/', function() use($app) {
    $template = $app['twig']->render('index.html.twig', 
        array( 
            "data" => listLastupdate()
        )
    );
    return $template;
});

$app->get('/logs/{id}', function($id) use($app) {
    return "TODO";
});



function listLastupdate() {
    //echo getcwd() . "\n";
    //$fileList = glob("../blogs/*.md");
    $fileList = glob("blogs/*.md");
    array_multisort(
      array_map( 'filemtime', $fileList ),
      SORT_NUMERIC,
      SORT_DESC,
      $fileList
    );

    $resultList = array();

    foreach ($fileList as $filename) {
        $fp = fopen($filename, "r");
        $content = fread($fp, filesize($filename));
        fclose($fp);

        $contentSplit = explode('---', $content);
        $headers = Yaml::parse($contentSplit[0]);

        if( $headers['published'] == 'true' ) {
            $result = new stdClass;
            $result->title = $headers['title'];
            $result->content = Markdown::defaultTransform($contentSplit[1]);;
            $resultList[$filename] = $result;
        }
    }
    return $resultList;
}


return $app;
?>