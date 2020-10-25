<?php


namespace App\Security;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TokenAuthenticator extends \Symfony\Component\Security\Guard\AbstractGuardAuthenticator {

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function start(Request $request, AuthenticationException $authException = null) {
        $data = [
            // you might translate this message
            'message' => 'Authentication Required'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supports(Request $request) {
        return 'authentication_token' !== $request->attributes->get('_route');
    }

    public function getCredentials(Request $request) {
        $authorisationHeader = $request->headers->get('Authorization');
        preg_match('/API-X-TOKEN (.*)/', $authorisationHeader, $matches);

        return [
            'token' => $matches[1] ?? null,
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider) {
        $apiToken = $credentials['token'];

        if (null === $apiToken) {
            return null;
        }

        // if a User object, checkCredentials() is called
        return $this->em->getRepository(User::class)->findOneBy(['apiToken' => $apiToken]);
    }

    public function checkCredentials($credentials, UserInterface $user) {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey) {
        return null;
    }

    public function supportsRememberMe() {
        return false;
    }
}