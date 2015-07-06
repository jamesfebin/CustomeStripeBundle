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
        $user_id = $event->getUserId();

        switch($response['type'])
        {
            case "customer.subscription.deleted":
                $this->onCustomerSubscriptionDeleted($response,$user_id);
                break;
            case "invoice.payment_succeeded":
                $this->onInovicePaymentSucceeded($response,$user_id);
                break;
        }
        $this->logger->addInfo("Stripe webhook received. ID: {$response['id']}, Type: {$response['type']}");
    }

    public  function onCustomerSubscriptionDeleted($response,$user_id)
    {
        $customer_id = $response['data']['object']['customer'];
        $id = $response['data']['object']['id'];
        $created = $response['created'];
        $status = $response['data']['object']['status'];
        $canceled_at = $response['data']['object']['canceled_at'];
        $data = array("user_id"=>$user_id, "customer_id"=>$customer_id, "id"=>$id, "created"=>$created, "status"=>$status, "canceled_at"=>$canceled_at);
    }

    public function onInovicePaymentSucceeded($response,$user_id)
    {
        $customer_id = $response['data']['object']['customer'];
        $id = $response['id'];
        $date = $response['data']['object']['date'];
        $end = $response['data']['object']['lines']['data'][0]['period']['end'];
        $name = $response['data']['object']['lines']['data'][0]['plan']['name'];
        $interval = $response['data']['object']['lines']['data'][0]['plan']['interval'];
        $total = $response['data']['object']['total'];
        $data = array("customer_id"=>$customer_id, "user_id"=>$user_id, "id"=>$id, "date"=>$date, "end"=>$end, "name"=>$name, "interval"=>$interval, "total"=>$total);
    }
}