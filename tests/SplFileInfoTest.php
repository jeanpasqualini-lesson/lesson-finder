<?php
namespace tests;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Finder\SplFileInfo;

class SplFileInfoTest extends \PHPUnit_Framework_TestCase
{
    private $splFileInfo;
    private $vfs;

    public function setUp()
    {
        $this->vfs = vfsStream::setup();
        vfsStream::newFile('test.txt')->setContent('hello world !')->at($this->vfs);

        $this->splFileInfo = new SplFileInfo('vfs://root/test.txt', 'relativepath', 'relativepathname');
    }

    public function testGetRelativePath()
    {
        $this->assertEquals('relativepath', $this->splFileInfo->getRelativePath());
    }

    public function testGetRelativePathname()
    {
        $this->assertEquals('relativepathname', $this->splFileInfo->getRelativePathname());
    }

    public function testGetContents()
    {
        $this->assertEquals('hello world !', $this->splFileInfo->getContents());
    }
}