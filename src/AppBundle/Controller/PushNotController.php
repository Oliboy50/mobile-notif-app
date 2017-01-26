<?php

namespace AppBundle\Controller;

use LinkValue\MobileNotif\Model\ApnsMessage;
use LinkValue\MobileNotifBundle\Client\ApnsClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/")
 */
class PushNotController extends Controller
{
    /**
     * @Route(
     *     "apns",
     *     name="push_to_apns",
     * )
     * @Method({"POST"})
     */
    public function pushToApnsAction(Request $request)
    {
        // content-type JSON
        if (!$request->headers->get('Content-Type') === 'application/json') {
            return new JsonResponse(['error' => 'The "Content-Type" header must be "application/json".']);
        }
        $body = json_decode($request->getContent(), JSON_OBJECT_AS_ARRAY);

        // tokens
        if (empty($body['tokens'])) {
            return new JsonResponse(['error' => 'The "tokens" key is required and must contain at least 1 device token.']);
        }
        $message = (new ApnsMessage())
            ->setTokens($body['tokens'])
        ;

        // alert
        if (empty($body['alert']) || is_int($body['alert']) || is_bool($body['alert'])) {
            return new JsonResponse(['error' => 'The "alert" key is required and could be either a string or an object.']);
        }
        if (is_string($body['alert'])) {
            $message->setSimpleAlert($body['alert']);
        } else {
            if (isset($body['alert']['title'])) {
                $message->setAlertTitle($body['alert']['title']);
            }
            if (isset($body['alert']['body'])) {
                $message->setAlertBody($body['alert']['body']);
            }
            if (isset($body['alert']['title-loc-key'])) {
                $message->setAlertTitleLocKey($body['alert']['title-loc-key']);
            }
            if (isset($body['alert']['title-loc-args'])) {
                $message->setAlertTitleLocArgs($body['alert']['title-loc-args']);
            }
            if (isset($body['alert']['action-loc-key'])) {
                $message->setAlertActionLocKey($body['alert']['action-loc-key']);
            }
            if (isset($body['alert']['loc-key'])) {
                $message->setAlertLocKey($body['alert']['loc-key']);
            }
            if (isset($body['alert']['loc-args'])) {
                $message->setAlertLocArgs($body['alert']['loc-args']);
            }
            if (isset($body['alert']['launch-image'])) {
                $message->setAlertLaunchImage($body['alert']['launch-image']);
            }
            if (isset($body['alert']['action'])) {
                $message->setAction($body['alert']['action']);
            }
        }

        // badge
        if (isset($body['badge'])) {
            $message->setBadge($body['badge']);
        }

        // sound
        if (isset($body['sound'])) {
            $message->setSound($body['sound']);
        }

        // content-available
        if (isset($body['content-available'])) {
            $message->setContentAvailable($body['content-available']);
        }

        // category
        if (isset($body['category'])) {
            $message->setCategory($body['category']);
        }

        // data
        if (isset($body['data'])) {
            $message->setData($body['data']);
        }

        // push message to apns clients
        $apnsClients = $this->container->get('link_value_mobile_notif.clients')->getApnsClients();
        $apnsClients->forAll(function ($key, ApnsClient $client) use ($message) {
            $client->push($message);

            return true;
        });

        // return info
        return new JsonResponse(['sent_push_number' => count($body['tokens']) * count($apnsClients)]);
    }
}
