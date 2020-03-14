<?php

namespace Phpactor\Extension\LanguageServerWorkspaceQuery\Tests\Unit\Watcher\Watcher;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerWorkspaceQuery\Watcher\FileModification;
use Phpactor\Extension\LanguageServerWorkspaceQuery\Watcher\Watcher\InotifyWatcher;
use Phpactor\TestUtils\Workspace;

class InotifyWatcherTest extends TestCase
{
    /**
     * @var Workspace
     */
    private $workspace;

    protected function setUp(): void
    {
        $this->workspace = new Workspace(realpath(__DIR__ . '/../Workspace'));
        $this->workspace->reset();
    }

    public function testWatch()
    {
        $watcher = new InotifyWatcher($this->workspace->path());
        $promise = \Amp\call(function () use ($watcher) {
            return yield $watcher->wait();
        });

        $this->workspace->put('foo', 'bar');

        $result = \Amp\Promise\wait($promise);
        $this->assertEquals(
            new FileModification($this->workspace->path() . '/', 'CREATE', 'foo'),
            $result
        );
    }
}
