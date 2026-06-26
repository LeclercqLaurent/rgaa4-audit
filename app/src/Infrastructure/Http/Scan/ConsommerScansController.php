<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Scan;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\EventListener\StopWorkerOnTimeLimitListener;
use Symfony\Component\Messenger\RoutableMessageBus;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Worker;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Traite à la demande les messages en attente sur le transport « async »
 * (scans, génération de constats), puis s'arrête dès que la file est vide.
 *
 * Pratique en dev pour déclencher le traitement sans worker permanent. Borné
 * par une limite de temps pour ne pas bloquer indéfiniment la requête.
 */
final readonly class ConsommerScansController
{
    public function __construct(
        #[Autowire(service: 'messenger.transport.async')]
        private ReceiverInterface $receiver,
        #[Autowire(service: 'messenger.routable_message_bus')]
        private RoutableMessageBus $bus,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    #[Route('/api/scans/consommer', name: 'api_scans_consommer', methods: ['POST'])]
    public function __invoke(): JsonResponse
    {
        $traites = 0;
        $this->dispatcher->addListener(WorkerMessageHandledEvent::class, static function () use (&$traites): void {
            ++$traites;
        });
        $this->dispatcher->addListener(WorkerRunningEvent::class, static function (WorkerRunningEvent $event): void {
            if ($event->isWorkerIdle()) {
                $event->getWorker()->stop();
            }
        });
        $this->dispatcher->addSubscriber(new StopWorkerOnTimeLimitListener(120));

        $worker = new Worker(['async' => $this->receiver], $this->bus, $this->dispatcher);
        $worker->run(['sleep' => 0]);

        return new JsonResponse(['traites' => $traites]);
    }
}
