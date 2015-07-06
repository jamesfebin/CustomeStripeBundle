<?php

namespace Customer\Bundle\StripeBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class StripeWebhookEvent extends Event implements StripeWebhookEventInterface
{
    /**
     * @see https://stripe.com/docs/api#event_types
     *
     * @var string
     */
    protected $event_name;
    /** @var string */
    protected $response;
    /**
     * @param $event_name string Stripe Event name
     * @param $response string Stripe response
     */
    public function __construct($event_name, $response)
    {
        $this->event_name = $event_name;
        $this->response   = $response;
    }
    /**
     * @return string
     */
    public function getEventName()
    {
        return $this->event_name;
    }
    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }
}