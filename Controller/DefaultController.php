<?php

namespace Customer\Bundle\StripeBundle\Controller;

use Customer\Bundle\StripeBundle\Event\StripeWebhookEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Monolog\Logger;

class DefaultController extends ContainerAware
{
    public function indexAction(Request $request)
    {
        $user_id = $request->query->get('user_id');
        $content = json_decode($request->getContent(), true);

        $event = is_array($content) && isset($content['type']) ? $content['type'] : 'unknown';

        $this->container->get('event_dispatcher')->dispatch(
            'customer_stripe.generic',
            new StripeWebhookEvent($event, $content,$user_id)
        );

        $this->container->get('event_dispatcher')->dispatch(
            'customer_stripe.'. $event,
            new StripeWebhookEvent($event, $content,$user_id)
        );

        return new Response('ok', 200);

    }
}
