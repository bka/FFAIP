<?php
namespace AppBundle\Security\Authenticator;

use AppBundle\Security\Token\MagentoToken;
use AppBundle\Security\Token\MagentoTokenFactory;
use AppBundle\Security\User\Customer;
use AppBundle\Service\CustomerData;
use AppBundle\Service\CustomerLogin;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;

/**
 * Class Api
 * @package AppBundle\Security\Authenticator
 */
class Api implements SimpleFormAuthenticatorInterface
{
    /**
     * @var MagentoTokenFactory
     */
    private $tokenFactory;

    /**
     * Api constructor.
     * @param MagentoTokenFactory $tokenFactory
     */
    public function __construct(MagentoTokenFactory $tokenFactory)
    {
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * Create AuthenticationToken for valid User.
     *
     * @param TokenInterface $token
     * @param UserProviderInterface $userProvider
     * @param                       $providerKey
     * @return MagentoToken
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if ($token->getUsername() !== null && $token->getCredentials() !== null) {

            return $this->tokenFactory->authenticateToken($token, $providerKey);
        }

        return new AnonymousToken('', 'anon.');
    }

    /**
     * Validate if given Token is supported.
     *
     * @param TokenInterface $token
     * @param                $providerKey
     *
     * @return bool
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof MagentoToken
        && $token->getProviderKey() == $providerKey;
    }

    /**
     * Create Token from given providerKey.
     *
     * @param Request $request
     * @param         $username
     * @param         $password
     * @param         $providerKey
     *
     * @return UsernamePasswordToken
     */
    public function createToken(Request $request, $username, $password, $providerKey)
    {
        return $this->tokenFactory->createToken($username, $password, $providerKey);
    }
}