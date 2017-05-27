<?php
namespace AppBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class Scope
 * @package AppBundle\Service
 */
class Scope
{
    /**
     *
     */
    const URI_TYPE_GUEST = 'guest';

    /**
     *
     */
    const URI_TYPE_CUSTOMER = 'loggedin';

    /**
     *
     */
    const URI_TYPE_GLOBAL = 'global';

    /**
     * @var TokenInterface
     */
    private $token;

    /**
     * @var
     */
    private $shopUrl;

    /**
     * @var Session
     */
    private $session;

    /**
     * Scope constructor.
     *
     * @param $shopUrl
     * @param $adminUser
     * @param $adminPassword
     * @param Session $session
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct($shopUrl, Session $session, TokenStorageInterface $tokenStorage)
    {
        $this->token = $tokenStorage->getToken();
        $this->shopUrl = $shopUrl;
        $this->session = $session;
        if ($this->session->has('cart_id') === false && $this->isGuest()) {
            $this->createGuestCart();
        }
    }

    /**
     * Helper MEthod to create GuestCart if not exist.
     */
    private function createGuestCart()
    {
        $request = new Request(
            'POST',
            $this->shopUrl . 'rest/V1/guest-carts',
            ["Content-Type" => "application/json"]
        );
        $response = (new Client())->send($request);
        $cartId = \GuzzleHttp\json_decode($response->getBody()->getContents());
        $this->session->set('cart_id', $cartId);
    }

    /**
     * @param $uris
     */
    public function prepareUri($uris)
    {
        if (array_key_exists(self::URI_TYPE_GLOBAL, $uris)) {
            $uri = $uris[self::URI_TYPE_GLOBAL];
        }  elseif ($this->isGuest()) {
            $uri = $uris[self::URI_TYPE_GUEST];
            $uri = str_replace(':cartId', $this->getCartId(), $uri);
        } else {
            $uri = $uris[self::URI_TYPE_CUSTOMER];
        }

        return $this->shopUrl . 'rest/' . $uri;
    }

    /**
     * @param $headers
     * @param bool $adminToken
     * @return array
     */
    public function extendHeaders($headers, $adminToken = false)
    {
        if (!$this->isGuest() && !$adminToken) {
            $headers['Authorization'] = 'Bearer ' . $this->token->getAttribute('bearerToken');
        } elseif ($adminToken) {
            $headers['Authorization'] = 'Bearer ' . $adminToken;
        }
        return $headers;
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return $this->token instanceof AnonymousToken;
    }

    /**
     * @return mixed
     */
    private function getCartId()
    {
        return $this->session->get('cart_id');
    }

    /**
     * Prepare Payload for Guest AddToCart Request.
     *
     * @param $payload
     * @return array
     */
    public function preparePayload($payload)
    {
        $data = $payload['cart_item'];
        $data['quote_id'] = $this->getCartId();
        unset($payload);
        $payload = ['cartItem' => $data];

        return \GuzzleHttp\json_encode($payload);
    }
}
