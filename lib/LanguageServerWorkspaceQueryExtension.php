<?php

namespace Phpactor\Extension\LanguageServerWorkspaceQuery;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\LanguageServerCompletion\Handler\SignatureHelpHandler;
use Phpactor\Extension\LanguageServerWorkspaceQuery\Handler\IndexerHandler;
use Phpactor\Extension\LanguageServerWorkspaceQuery\Watcher\Watcher;
use Phpactor\Extension\LanguageServerWorkspaceQuery\Watcher\Watcher\InotifyWatcher;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\LanguageServerCompletion\Handler\CompletionHandler;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\FilePathResolverExtension\FilePathResolverExtension;
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\MapResolver\Resolver;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorkspaceQuery\Model\Indexer;

class LanguageServerWorkspaceQueryExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(IndexerHandler::class, function (Container $container) {
            return new IndexerHandler(
                $container->get(Indexer::class),
                $container->get(Watcher::class)
            );
        }, [
            LanguageServerExtension::TAG_SESSION_HANDLER => []
        ]);

        $container->register(Watcher::class, function (Container $container) {
            $resolver = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER);
            assert($resolver instanceof PathResolver);
            return new InotifyWatcher(
                TextDocumentUri::fromString($resolver->resolve('%project_root%'))->path(),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
