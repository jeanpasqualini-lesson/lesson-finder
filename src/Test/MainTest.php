<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/13/15
 * Time: 4:49 AM
 */

namespace Test;

use Interfaces\TestInterface;
use Symfony\Component\Finder\Finder;

class MainTest implements TestInterface {
    public function runTest()
    {
        $this->printSeparator("in");

        $this->testIn();

        $this->printSeparator("inAndIn");

        $this->testInAndIn();

        $this->printSeparator("exclude");

        $this->testExclude();

        $this->printSeparator("ignoreCVS");

        $this->testIgnoreCVS();

        $this->printSeparator("ingoreDotFiles");

        $this->testIgnoreDotFiles();

        $this->printSeparator("filter");

        $this->testFilter();

        $this->printSeparator("directory");

        $this->testDirectory();

        $this->printSeparator("sort");

        $this->testSort();

        $this->printSeparator("other");

        $this->testOther();
    }

    private function printSeparator($title)
    {
        echo "=== $title ===\n";
    }

    private function getFinder()
    {
        return new Finder();
    }

    public function dumpFinder(Finder $finder)
    {
        foreach($finder as $file)
        {
            echo $file->getRealPath().PHP_EOL;
        }
    }

    public function testIn()
    {
        $finder = $this->getFinder();

        $finder->files()->in(ROOT_DIR);

        $this->dumpFinder($finder);
    }

    public function testInAndIn()
    {
        $finder = $this->getFinder();

        $finder->files()->in(__DIR__)->in(ROOT_DIR);

        $this->dumpFinder($finder);
    }

    public function testExclude()
    {
        $finder = $this->getFinder();

        $finder->in("/etc/php5")->exclude("/etc/php5/mods-available/");

        $this->dumpFinder($finder);
    }

    public function testIgnoreUnreadableDirs()
    {
        $finder = $this->getFinder();

        $finder->in("/")->ignoreUnreadableDirs();
    }

    public function testIgnoreCVS()
    {
        $finder = $this->getFinder();

        $finder->ignoreVCS(true);

        $this->dumpFinder($finder->in(ROOT_DIR));
    }

    public function testIgnoreDotFiles()
    {
        $finder = $this->getFinder();

        $finder->ignoreDotFiles(true);

        $this->dumpFinder($finder->in(ROOT_DIR));
    }

    public function testFilter()
    {
        $finder = $this->getFinder();

        $finder->name("Test*")->size("< 100K")->date("since 10 hour ago");

        $this->dumpFinder($finder->in(ROOT_DIR));
    }


    public function testDirectory()
    {
        $finder = $this->getFinder();

        $finder->directories();

        $this->dumpFinder($finder->in(ROOT_DIR));
    }

    public function testSort()
    {
        $finder = $this->getFinder();

        $finder->sortByName();

        /**
        $finder->sort(function(\SplFileInfo $a, \SplFileInfo $b)
        {
           return strcmp($a->getRealPath(), $b->getRealPath());
        });
         */

        $finder->files();

        $this->dumpFinder($finder->in(ROOT_DIR));
    }

    public function testOther()
    {
        $finder = $this->getFinder();

        $finder->in(ROOT_DIR.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."vendor");

        $finder->files()
            ->name("*.php")
            ->name('/\.php$/')
            ->notName("*.rb")
            ->contains("Test")
            ->path("Tests/")
            ->notPath("Iterator")
            ->depth(" > 3")
        ;

        $finder->filter(function(\SplFileInfo $file)
        {
            if(strpos($file, "Comparator") !== false)
            {
                return false;
            }
        });

        echo "first : ".end(iterator_to_array($finder))->getContents().PHP_EOL;

        $this->dumpFinder($finder);
    }
}