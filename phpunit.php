<?php

use Sami\Sami;
use Sami\RemoteRepository\GitHubRemoteRepository;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

const REPO           = 'sebastianbergmann/phpunit';
const REPO_DIR       = __DIR__ . '/phpunit';
const SRC_DIR        = REPO_DIR . '/src';
const CACHE_DIR      = __DIR__ . '/cache';
const BUILD_DIR      = __DIR__ . '/gh-pages';
const TEMPLATE_DIR   = __DIR__ . '/templates';


// Documentation is generated for PHPUnit source files.
$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in(SRC_DIR);


// Both old and current stable versions are targeted.
$versions = GitVersionCollection::create(REPO_DIR)
    ->add('4.8', '4.8 (old stable)')
    ->add('5.7', '5.7 (current stable)');


// Generate main index file redirecting to current stable version.
$loader = new Twig_Loader_Filesystem(TEMPLATE_DIR);
$twig = new Twig_Environment($loader);

$stableVersion = $versions->getVersions()[$versions->count() - 1];

$renderedHtml = $twig->render('index.html.twig', [
    'directory' => $stableVersion->getName()
]);


// Write it to build directory.
$filesystem = new FileSystem();

$filesystem->dumpFile(BUILD_DIR . '/index.html', $renderedHtml);


// Return Sami configuration
return new Sami($iterator, array(
    'title'               => 'PHPUnit API - The PHP Unit Testing framework.',
    'remote_repository'   => new GitHubRemoteRepository(REPO, REPO_DIR),
    'versions'            => $versions,
    'cache_dir'           => CACHE_DIR . '/%version%',
    'build_dir'           => BUILD_DIR . '/%version%',
    'simulate_namespaces' => true
));
