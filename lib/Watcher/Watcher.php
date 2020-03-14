<?php

namespace Phpactor\Extension\LanguageServerWorkspaceQuery\Watcher;

use Amp\Promise;

interface Watcher
{
    /**
     * Return a promise with a FileModification object.
     */
    public function wait(): Promise;
}
