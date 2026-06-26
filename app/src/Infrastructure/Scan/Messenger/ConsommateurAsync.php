<?php

declare(strict_types=1);

namespace App\Infrastructure\Scan\Messenger;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\EventListener\StopWorkerOnTimeLimitListener;
use Symfony\Component\Messenger\RoutableMessageBus;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Worker;

/**
 * Draine à la demande le transport « async » (scans puis génération de constats)
 * jusqu'à ce que la file soit vide, borné par une limite de temps. Partagé entre
 * le contrôleur HTTP de consommation et la commande CLI d'audit.
 */
final readonly class ConsommateurAsync
{
    public function __construct(
        #[Autowire(service: 'messenger.transport.async')]
        private ReceiverInterface $receiver,
        #[Autowire(service: 'messenger.routable_message_bus')]
        private RoutableMessageBus $bus,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @return int Nombre de messages traités
     */
    public function consommer(int $limiteSecondes = 120): int
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
        $this->dispatcher->addSubscriber(new StopWorkerOnTimeLimitListener($limiteSecondes));

        $worker = new Worker(['async' => $this->receiver], $this->bus, $this->dispatcher);
        $worker->run(['sleep' => 0]);

        return $traites;
    }
}
