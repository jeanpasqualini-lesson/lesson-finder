<?php
namespace tests;

use org\bovigo\vfs\content\LargeFileContent;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Finder\Finder;
use org\bovigo\vfs\vfsStreamDirectory;

class MainTest extends \PHPUnit_Framework_TestCase
{
    /** @var Finder */
    private $finder;

    /** @var vfsStreamDirectory */
    private $vfs;

    public function setUp()
    {
        $this->vfs = vfsStream::setup();
        $this->finder = new Finder();
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(Finder::class, $this->finder);
    }

    public function testContents()
    {
        vfsStream::newFile('mario.txt')
            ->withContent('youpi !')
            ->at($this->vfs);


        $this->finder->in($this->vfs->url());
        $this->finder->files();

        $files = iterator_to_array($this->finder);

        $this->assertEquals('youpi !', current($files)->getContents());
    }

    public function testIn()
    {
        vfsStream::newFile('mario.txt')->at($this->vfs);

        $this->finder->in($this->vfs->url());
        $this->finder->files();

        $files = iterator_to_array($this->finder);
        $files = array_map('strval', $files);

        $this->assertEquals(['vfs://root/mario.txt'], array_values($files));
    }

    public function testInAndIn()
    {
        $folderA = vfsStream::newDirectory('folderA')->at($this->vfs);
        $folderB = vfsStream::newDirectory('folderB')->at($this->vfs);

        vfsStream::newFile('mario.txt')->at($folderA);
        vfsStream::newFile('luigi.txt')->at($folderB);

        $this->finder->in($folderA->url());
        $this->finder->in($folderB->url());
        $this->finder->files();

        $files = iterator_to_array($this->finder);
        $files = array_map('strval', $files);

        $this->assertEquals(['vfs://root/folderA/mario.txt', 'vfs://root/folderB/luigi.txt'], array_values($files));
    }

    public function testInWithBadFolderPath()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'The "/unknow" directory does not exist.');
        $this->finder->in('/unknow');
    }

    public function provideDepth()
    {
        // Expected directories names in depth

        yield ['< 3', [
            'vfs://root/first',
            'vfs://root/first/two',
            'vfs://root/first/two/three',
        ]];

        yield ['>= 3', [
            'vfs://root/first/two/three/foor',
            'vfs://root/first/two/three/foor/five',
        ]];
    }

    /**
     * @dataProvider provideDepth
     * @param $depth
     * @param $expected
     */
    public function testDepth($depth, $expected)
    {
        vfsStream::newDirectory('first/two/three/foor/five')->at($this->vfs);

        $this->finder->in($this->vfs->url());
        $this->finder->directories();
        $this->finder->depth($depth);

        $directories = array_keys(iterator_to_array($this->finder));

        $this->assertEquals($expected, $directories);
    }

    public function testDate()
    {
        vfsStream::newFile('1970.txt')->lastModified(strtotime('1970-06-06'))->at($this->vfs);
        vfsStream::newFile('2016.txt')->lastModified(strtotime('2016-06-06'))->at($this->vfs);
        vfsStream::newFile('1991.txt')->lastModified(strtotime('1991-06-06'))->at($this->vfs);

        $this->finder->in($this->vfs->url());
        $this->finder->date('> 1991-01-01');
        $this->finder->date('< 1991-12-31');

        $files = array_keys(iterator_to_array($this->finder));

        $this->assertEquals(['vfs://root/1991.txt'], $files);
    }

    public function provideName()
    {
        // Glob (auto convered in regex)
        yield ['*.php', [
            'vfs://root/index.php',
            'vfs://root/mainController.php',
        ]];

        // Glob (auto converted in regex)
        yield ['*Controller.php', [
            'vfs://root/mainController.php',
        ]];

        // Pattern
        yield ['/[a-z]Controller.php/', [
            'vfs://root/mainController.php',
        ]];

        // Simple string
        yield ['luc.txt', [
            'vfs://root/luc.txt',
        ]];
    }

    /**
     * @dataProvider provideName
     * @param $name
     * @param $expected
     */
    public function testName($name, $expected)
    {
        vfsStream::newFile('index.php')->at($this->vfs);
        vfsStream::newFile('mainController.php')->at($this->vfs);
        vfsStream::newFile('luc.txt')->at($this->vfs);

        $this->finder->in($this->vfs->url());
        $this->finder->name($name);

        $files = array_keys(iterator_to_array($this->finder));

        $this->assertEquals($expected, $files);
    }

    public function provideNotName()
    {
        // Glob (auto convered in regex)
        yield ['*.php', [
            'vfs://root/luc.txt',
        ]];

        // Glob (auto converted in regex)
        yield ['*Controller.php', [
            'vfs://root/index.php',
            'vfs://root/luc.txt',
        ]];

        // Pattern
        yield ['/[a-z]Controller.php/', [
            'vfs://root/index.php',
            'vfs://root/luc.txt',
        ]];

        // Simple string
        yield ['luc.txt', [
            'vfs://root/index.php',
            'vfs://root/mainController.php',
        ]];
    }

    /**
     * @dataProvider provideNotName
     * @param $name
     * @param $expected
     */
    public function testNotName($name, $expected)
    {
        vfsStream::newFile('index.php')->at($this->vfs);
        vfsStream::newFile('mainController.php')->at($this->vfs);
        vfsStream::newFile('luc.txt')->at($this->vfs);

        $this->finder->in($this->vfs->url());
        $this->finder->notName($name);

        $files = array_keys(iterator_to_array($this->finder));

        $this->assertEquals($expected, $files);
    }

    public function provideContains()
    {
        yield ['/ [0-9]{4} /', [
            'vfs://root/fichier1.txt'
        ]];

        yield ['20000', [
            'vfs://root/fichier2.txt'
        ]];
    }

    /**
     * @dataProvider provideContains
     * @param $contains
     * @param $expect
     */
    public function testContains($contains, $expect)
    {
        vfsStream::newFile('fichier1.txt')->withContent('Copyright 2017 Licence MIT')->at($this->vfs);
        vfsStream::newFile('fichier2.txt')->withContent('Copyright 20000 Licence MIT')->at($this->vfs);

        $this->finder->in($this->vfs->url());
        $this->finder->contains($contains);

        $files = array_keys(iterator_to_array($this->finder));

        $this->assertEquals($expect, $files);
    }

    public function provideNotContains()
    {
        yield ['/ [0-9]{4} /', [
            'vfs://root/fichier2.txt'
        ]];

        yield ['20000', [
            'vfs://root/fichier1.txt'
        ]];
    }

    /**
     * @dataProvider provideNotContains
     * @param $contains
     * @param $expect
     */
    public function testNotContains($contains, $expect)
    {
        vfsStream::newFile('fichier1.txt')->withContent('Copyright 2017 Licence MIT')->at($this->vfs);
        vfsStream::newFile('fichier2.txt')->withContent('Copyright 20000 Licence MIT')->at($this->vfs);

        $this->finder->in($this->vfs->url());
        $this->finder->notContains($contains);

        $files = array_keys(iterator_to_array($this->finder));

        $this->assertEquals($expect, $files);
    }

    public function providePath()
    {
        // Only pattern or simple string

        yield ['folderA', [
            'vfs://root/folderA',
            'vfs://root/folderA/file.txt'
        ]];

        yield ['file', [
            'vfs://root/folderA/file.txt',
            'vfs://root/folderB/file.txt'
        ]];
    }

    /**
     * @dataProvider providePath
     * @param $path
     * @param $expected
     */
    public function testPath($path, $expected)
    {
        $this->finder->in($this->vfs->url());
        $this->finder->path($path);

        $folderA = vfsStream::newDirectory('folderA')->at($this->vfs);
        $folderB = vfsStream::newDirectory('folderB')->at($this->vfs);
        vfsStream::newFile('file.txt')->at($folderA);
        vfsStream::newFile('file.txt')->at($folderB);

        $elements = array_keys(iterator_to_array($this->finder));

        $this->assertEquals($expected, $elements);
    }


    public function provideNotPath()
    {
        // Only pattern or simple string

        yield ['folderA', [
            'vfs://root/folderB',
            'vfs://root/folderB/file.txt'
        ]];

        yield ['file', [
            'vfs://root/folderA',
            'vfs://root/folderB'
        ]];
    }

    /**
     * @dataProvider provideNotPath
     * @param $path
     * @param $expected
     */
    public function testNotPath($path, $expected)
    {
        $this->finder->in($this->vfs->url());
        $this->finder->notPath($path);

        $folderA = vfsStream::newDirectory('folderA')->at($this->vfs);
        $folderB = vfsStream::newDirectory('folderB')->at($this->vfs);
        vfsStream::newFile('file.txt')->at($folderA);
        vfsStream::newFile('file.txt')->at($folderB);

        $elements = array_keys(iterator_to_array($this->finder));

        $this->assertEquals($expected, $elements);
    }

    public function provideSize()
    {
        // Quand matcheur si plusieur alors and
        yield ['> 1M', [
            'vfs://root/2M.txt',
            'vfs://root/2G.txt',
            'vfs://root/1000G.txt',
        ]];

        yield [['> 1M', '< 1000G'], [
            'vfs://root/2M.txt',
            'vfs://root/2G.txt',
        ]];
    }

    private function applyMatcher($matcher, $filters)
    {
        $filters = (array) $filters;

        array_walk($filters, function($filter) use ($matcher)
        {
           $this->finder->{$matcher}($filter);
        });
    }

    /**
     * @dataProvider provideSize
     * @param $size
     * @param $expected
     */
    public function testSize($size, $expected)
    {
        $this->finder->in($this->vfs->url());

        vfsStream::newFile('500K.txt')->withContent(LargeFileContent::withKilobytes(500))->at($this->vfs);
        vfsStream::newFile('2M.txt')->withContent(LargeFileContent::withMegabytes(2))->at($this->vfs);
        vfsStream::newFile('2G.txt')->withContent(LargeFileContent::withGigabytes(2))->at($this->vfs);
        vfsStream::newFile('1000G.txt')->withContent(LargeFileContent::withGigabytes(1000))->at($this->vfs);

        $this->applyMatcher('size', $size);

        $files = array_keys(iterator_to_array($this->finder));

        $this->assertEquals($expected, $files);
    }
}