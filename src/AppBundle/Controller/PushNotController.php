<?php

namespace AppBundle\Controller;

use LinkValue\MobileNotif\Model\ApnsMessage;
use LinkValue\MobileNotif\Model\GcmMessage;
use LinkValue\MobileNotifBundle\Client\ApnsClient;
use LinkValue\MobileNotifBundle\Client\GcmClient;
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

    /**
     * @Route(
     *     "gcm",
     *     name="push_to_gcm",
     * )
     * @Method({"POST"})
     */
    public function pushToGcmAction(Request $request)
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
        $message = new GcmMessage();

        // GCM tokens are limited
        try {
            $message->setTokens($body['tokens']);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => 'The "tokens" key contains too many device tokens. You should push your notification multiple times with less device tokens.']);
        }

        if (empty($body['notification']) || !is_array($body['notification'])) {
            return new JsonResponse(['error' => 'The "notification" key is required and must be an object.']);
        }

        // notification
        if (isset($body['notification']['title'])) {
            $message->setNotificationTitle($body['notification']['title']);
        }
        if (isset($body['notification']['body'])) {
            $message->setNotificationBody($body['notification']['body']);
        }
        if (isset($body['notification']['icon'])) {
            $message->setNotificationIcon($body['notification']['icon']);
        }
        if (isset($body['notification']['sound'])) {
            $message->setNotificationSound($body['notification']['sound']);
        }
        if (isset($body['notification']['tag'])) {
            $message->setNotificationTag($body['notification']['tag']);
        }
        if (isset($body['notification']['badge'])) {
            $message->setNotificationBadge($body['notification']['badge']);
        }
        if (isset($body['notification']['color'])) {
            $message->setNotificationColor($body['notification']['color']);
        }
        if (isset($body['notification']['click_action'])) {
            $message->setNotificationClickAction($body['notification']['click_action']);
        }
        if (isset($body['notification']['body_loc_key'])) {
            $message->setNotificationBodyLocKey($body['notification']['body_loc_key']);
        }
        if (isset($body['notification']['body_loc_args'])) {
            $message->setNotificationBodyLocArgs($body['notification']['body_loc_args']);
        }
        if (isset($body['notification']['title_loc_key'])) {
            $message->setNotificationTitleLocKey($body['notification']['title_loc_key']);
        }
        if (isset($body['notification']['title_loc_args'])) {
            $message->setNotificationTitleLocArgs($body['notification']['title_loc_args']);
        }

        // extra parameters
        if (isset($body['collapse_key'])) {
            $message->setCollapseKey($body['collapse_key']);
        }
        if (isset($body['priority'])) {
            $message->setPriority($body['priority']);
        }
        if (isset($body['restricted_package_name'])) {
            $message->setRestrictedPackageName($body['restricted_package_name']);
        }
        if (isset($body['content_available'])) {
            $message->setContentAvailable($body['content_available']);
        }
        if (isset($body['delay_while_idle'])) {
            $message->setDelayWhileIdle($body['delay_while_idle']);
        }
        if (isset($body['dry_run'])) {
            $message->setDryRun($body['dry_run']);
        }
        if (isset($body['time_to_live'])) {
            $message->setTimeToLive($body['time_to_live']);
        }

        // data
        if (isset($body['data'])) {
            $message->setData($body['data']);
        }

        // push message to gcm clients
        $gcmClients = $this->container->get('link_value_mobile_notif.clients')->getGcmClients();
        $gcmClients->forAll(function ($key, GcmClient $client) use ($message) {
            $client->push($message);

            return true;
        });

        // return info
        return new JsonResponse(['sent_push_number' => count($body['tokens']) * count($gcmClients)]);
    }
}
