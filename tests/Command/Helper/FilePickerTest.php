<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;
use Trismegiste\SnippetGenerator\Command\Helper\FilePicker;

class FilePickerTest extends TestCase {

    protected $sut;

    protected function setUp(): void {
        $this->sut = new FilePicker();
    }

    protected function tearDown(): void {
        unset($this->sut);
    }

    public function testNoFile() {
        $this->expectException(RuntimeException::class);
        $this->sut->pickFile($this->createStub(InputInterface::class), $this->createStub(OutputInterface::class), __DIR__, 'X');
    }

    public function testOneFile() {
        $found = $this->sut->pickFile($this->createStub(InputInterface::class), $this->createStub(OutputInterface::class), __DIR__, 'FilePicker*');
        $this->assertInstanceOf(SplFileInfo::class, $found);
        $this->assertStringEndsWith('FilePickerTest.php', (string) $found);
    }

    public function testManyFile() {
        $questionHelper = $this->createMock(QuestionHelper::class);
        $questionHelper->expects($this->once())
                ->method('ask')
                ->willReturnCallback(function(...$param) {
                    return $param[2]->getChoices()[0];   // [2] because the Question is #2 parameter and we take the first choice in the list returned by the Finder
                });
        $this->sut->setHelperSet(new HelperSet(['question' => $questionHelper]));

        $found = $this->sut->pickFile($this->createStub(InputInterface::class), $this->createStub(OutputInterface::class), __DIR__ . '/..', '*.php');

        $this->assertInstanceOf(SplFileInfo::class, $found);
        $this->assertStringEndsWith('FilePickerTest.php', (string) $found);
    }

}
