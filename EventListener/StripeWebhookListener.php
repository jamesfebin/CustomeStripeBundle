<?php

namespace Customer\Bundle\StripeBundle\EventListener;

use Customer\Bundle\StripeBundle\Event\StripeWebhookEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Monolog\Logger;

class StripeWebhookListener implements EventSubscriberInterface
{

    protected $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'customer_stripe.generic' => 'onGenericWebhookEvent'
        );
    }

    public function onGenericWebhookEvent(StripeWebhookEventInterface $event)
    {
        $response = $event->getResponse();
        $this->logger->addInfo("Stripe webhook received. ID: {$response['id']}, Type: {$response['type']}");
    }
}