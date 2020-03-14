<?php

namespace Phpactor\Extension\LanguageServerWorkspaceQuery;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\LanguageServerCompletion\Handler\SignatureHelpHandler;
use Phpactor\Extension\LanguageServerWorkspaceQuery\Handler\IndexerHandler;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\LanguageServerCompletion\Handler\CompletionHandler;
use Phpactor\MapResolver\Resolver;
use Phpactor\WorkspaceQuery\Model\Indexer;

class LanguageServerWorkspaceQueryExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(IndexerHandler::class, function (Container $container) {
            return new IndexerHandler($container->get(Indexer::class));
        }, [
            LanguageServerExtension::TAG_SESSION_HANDLER => []
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
