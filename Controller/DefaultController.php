<?php

namespace Customer\Bundle\StripeBundle\Controller;

use Customer\Bundle\StripeBundle\Event\StripeWebhookEvent;
use Stripe\Customer;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Stripe\Stripe;
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
            new StripeWebhookEvent($event, $content, $user_id)
        );

        $this->container->get('event_dispatcher')->dispatch(
            'customer_stripe.' . $event,
            new StripeWebhookEvent($event, $content, $user_id)
        );

        return new Response('ok', 200);

    }

    public function subscriptionsAction(Request $request)
    {
        try {
            $customer_id = $request->query->get('customer_id');
            $limit = $request->query->get('limit') ? $request->query->get('limit') : 10;
            $starting_after = $request->query->get('starting_after') ? $request->query->get('starting_after') : -1;
            $ending_before = $request->query->get('ending_before') ? $request->query->get('ending_before') : -1;

            $request_array = array("limit" => $limit);
            if ($starting_after != -1) {
                $request_array["starting_after"] = $starting_after;
            }
            if ($ending_before != -1) {
                $request_array["ending_before"] = $ending_before;
            }

            \Stripe\Stripe::setApiKey("Input User API KEY HERE");
            $response = \Stripe\Customer::retrieve($customer_id)->subscriptions->all($request_array);
            $subscriptions = array();
            $i = 0;
            foreach ($response['data'] as $subscription) {
                $subscription_item = array(
                    "id" => $subscription['id'],
                    "created" => $subscription['plan']['created'],
                    "status" => $subscription['status']
                );
                $subscriptions[$i] = $subscription_item;
                $i++;
            }
            $subscriptions = json_encode($subscriptions);

        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            $error = array("error" => "Connection Error");

            return new Response(json_encode($error), 422);
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            $error = array("error" => "Invalid API Key");

            return new Response(json_encode($error), 422);
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            $error = array("error" => "Invalid Request");

            return new Response(json_encode($error), 422);
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            $error = array("error" => $e);

            return new Response(json_encode($error), 422);
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            $error = array("error" => $e);

            return new Response(json_encode($error), 422);
        }

        return new Response($subscriptions, 200);
    }

    public function invoicesAction(Request $request)
    {
        try {
            $customer_id = $request->query->get('customer_id');
            $limit = $request->query->get('limit') ? $request->query->get('limit') : 10;
            $starting_after = $request->query->get('starting_after') ? $request->query->get('starting_after') : -1;
            $ending_before = $request->query->get('ending_before') ? $request->query->get('ending_before') : -1;
            $date = $request->query->get('date') ? $request->query->get('date') : -1;

            $request_array = array("customer"=>$customer_id, "limit"=>$limit);
            if ($starting_after != -1) {
                $request_array["starting_after"] = $starting_after;
            }
            if ($ending_before != -1) {
                $request_array["ending_before"] = $ending_before;
            }
            if ($date != -1) {
                $date_params = array("gt"=>$date);
                $request_array["date"] = $date_params;
            }

            \Stripe\Stripe::setApiKey("sk_test_DxL4C2nUSAVBILG7FtnY4mYs");
            $response = \Stripe\Invoice::all($request_array);
            $invoices = array();
            $i = 0;
            foreach ($response['data'] as $invoice) {
                $invoice_item = array(
                    "date" => $invoice['date'],
                    "end" => $invoice['lines']['data'][0]['period']['end'],
                    "name" => $invoice['lines']['data'][0]['plan']['name'],
                    "id" => $invoice['id'],
                    "interval" => $invoice['lines']['data'][0]['plan']['interval'],
                    "total" => $invoice['total']
                );
                $invoices[$i] = $invoice_item;
                $i++;
            }
            $invoices = json_encode($invoices);

        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            $error = array("error" => "Connection Error");

            return new Response(json_encode($error), 422);
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            $error = array("error" => "Invalid API Key");

            return new Response(json_encode($error), 422);
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            $error = array("error" => "Invalid Request","array"=> $request_array);

            return new Response(json_encode($error), 422);
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            $error = array("error" => $e);

            return new Response(json_encode($error), 422);
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            $error = array("error" => $e);

            return new Response(json_encode($error), 422);
        }

        return new Response($invoices, 200);
    }

}
