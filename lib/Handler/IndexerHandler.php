<?php

namespace Phpactor\Extension\LanguageServerWorkspaceQuery\Handler;

use Amp\Deferred;
use Amp\Promise;
use Amp\Success;
use Generator;
use LanguageServerProtocol\CompletionItem;
use LanguageServerProtocol\CompletionList;
use LanguageServerProtocol\CompletionOptions;
use LanguageServerProtocol\MessageType;
use LanguageServerProtocol\Position;
use LanguageServerProtocol\Range;
use LanguageServerProtocol\ServerCapabilities;
use LanguageServerProtocol\SignatureHelpOptions;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\TextEdit;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Core\TypedCompletorRegistry;
use Phpactor\Extension\LanguageServerCompletion\Util\PhpactorToLspCompletionType;
use Phpactor\Extension\LanguageServer\Helper\OffsetHelper;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\ServiceProvider;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Phpactor\LanguageServer\Core\Session\Workspace;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorkspaceQuery\Model\Indexer;

class IndexerHandler implements ServiceProvider
{
    /**
     * @var Indexer
     */
    private $indexer;

    public function __construct(
        Indexer $indexer
    ) {
        $this->indexer = $indexer;
    }

    public function methods(): array
    {
        return [
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function services(): array
    {
        return [
            'indexerService' => 'indexerService'
        ];
    }

    public function indexerService(MessageTransmitter $transmitter): Promise
    {
        return \Amp\call(function () use ($transmitter) {
            $transmitter->transmit(new NotificationMessage('window/logMessage', [
                'type' => MessageType::INFO,
                'message' => 'Indexer started',
            ]));
            $job = $this->indexer->getJob();

            foreach ($job->generator() as $file) {
                $transmitter->transmit(new NotificationMessage('window/logMessage', [
                    'type' => MessageType::INFO,
                    'message' => sprintf('Indexing "%s"', $file)
                ]));
                yield new Success();
            }

            return new Success();
        });
    }
}
