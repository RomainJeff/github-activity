<?php
require_once __DIR__ ."/vendor/autoload.php";

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Cache\Adapter\Filesystem\FilesystemCachePool;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);

$filesystemAdapter = new Local(__DIR__.'/');
$filesystem        = new Filesystem($filesystemAdapter);
$pool = new FilesystemCachePool($filesystem);

$builder = new Github\HttpClient\Builder();
$builder->addHeaderValue('Accept', sprintf('application/vnd.github.cloak-preview+json'));
$githubAPI = new \Github\Client($builder);
$githubAPI->addCache($pool);

$githubAPI->authenticate(
    getenv('GH_TOKEN'), 
    getenv('GH_TOKEN'), 
    Github\Client::AUTH_HTTP_TOKEN
);

$hours = [
    "00" => 0,
    "01" => 0,
    "02" => 0,
    "03" => 0,
    "04" => 0,
    "05" => 0,
    "06" => 0,
    "07" => 0,
    "08" => 0,
    "09" => 0,
    "10" => 0,
    "11" => 0,
    "12" => 0,
    "13" => 0,
    "14" => 0,
    "15" => 0,
    "16" => 0,
    "17" => 0,
    "18" => 0,
    "19" => 0,
    "20" => 0,
    "21" => 0,
    "22" => 0,
    "23" => 0,    
];

$searchAPI = $githubAPI->api('search');
$paginator  = new Github\ResultPager($githubAPI);
$parameters = array('author:'. getenv('GH_USER') .' >='. getenv('GH_DATE'), 'committer-date', 'desc');

$commits    = array_reduce(
    $paginator->fetchAll($searchAPI, 'commits', $parameters),
    function ($items, $commit) {
        $date = new DateTime($commit['commit']['author']['date']);
        dump($date);
        $formattedDate = $date->format('H');
        $items[$formattedDate] += 1;
        return $items;
    },
    $hours
);

$totalCommits = array_reduce($commits, function ($total, $commits) {
    return $total + $commits;
}, 0);

echo $twig->render('index.html.twig', [
    'commits' => $commits,
    'totalCommits' => $totalCommits,
    ''
]);
