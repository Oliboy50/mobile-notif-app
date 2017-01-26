<?php

namespace AppBundle\Controller;

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
     *     "data",
     *     name="push_data",
     * )
     * @Method({"POST"})
     */
    public function indexAction(Request $request)
    {
        if (!$request->headers->get('Content-Type') === 'application/json') {
            return new JsonResponse(['error' => 'The "Content-Type" header must be "application/json".']);
        }

        $body = json_decode($request->getContent(), JSON_OBJECT_AS_ARRAY);
        if (empty($body['tokens'])) {
            return new JsonResponse(['error' => 'The "tokens" key is required and must contain at least 1 device token.']);
        }
        if (empty($body['data'])) {
            return new JsonResponse(['error' => 'The "data" key cannot be empty.']);
        }

        return new JsonResponse(['success' => true]);
    }
}
