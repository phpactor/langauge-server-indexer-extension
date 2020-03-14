<?php

namespace Phpactor\Extension\LanguageServerWorkspaceQuery\Watcher\Watcher;

use Amp\Process\Process;
use Amp\Promise;
use Generator;
use Phpactor\Extension\LanguageServerWorkspaceQuery\Watcher\FileModification;
use Phpactor\Extension\LanguageServerWorkspaceQuery\Watcher\Watcher;

class InotifyWatcher implements Watcher
{
    private $processStarted = false;

    /**
     * @var Process
     */
    private $process;

    /**
     * @var string
     */
    private $buffer;

    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * {@inheritDoc}
     */
    public function wait(): Promise
    {
        return \Amp\call(function () {
            if (!$this->processStarted) {
                $this->process = yield $this->startProcess();
                $this->processStarted = true;
            }

            return \Amp\call(function () {
                $buffer = '';
                $stdout = $this->process->getStdout();
                while (null !== $chunk = yield $stdout->read()) {
                    foreach (str_split($chunk) as $char) {
                        $this->buffer .= $char;

                        if ($char !== "\n") {
                            continue;
                        }

                        $read = $this->buffer;
                        $this->buffer = '';

                        return FileModification::fromCsvString($read);
                    }
                }

                $exitCode = yield $this->process->join();
            });
        });
    }

    private function startProcess(): Promise
    {
        return \Amp\call(function () {
            $process = new Process([
                'inotifywait',
                $this->path,
                '-r',
                '-emodify,create',
                '--monitor',
                '--csv',
            ]);

            $pid = yield $process->start();

            if (!$process->isRunning()) {
                throw new RuntimeException(sprintf(
                    'Could not start process'
                ));
            }

            return $process;
        });
    }
}
