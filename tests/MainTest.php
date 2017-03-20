<?php

namespace tests;

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
}