<?php

namespace Customer\Bundle\StripeBundle\Event;

interface StripeWebhookEventInterface
{
    public function getEventName();
    public function getResponse();
    public function getUserId();
}