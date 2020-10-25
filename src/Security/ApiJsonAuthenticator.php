<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class ApiJsonAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface {
    use TargetPathTrait;

    private $token;
    private $entityManager;
    private $passwordEncoder;
    private $urlGenerator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->entityManager   = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->urlGenerator    = $urlGenerator;
    }

    public function supports(Request $request) {
        return 'authentication_token' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request) {
        if ($request->getContentType() !== 'json') {
            throw new BadRequestHttpException();
        }

        $data = json_decode($request->getContent(), true);

        $credentials = [
            'email'    => $data['email'],
            'password' => $data['password'],
        ];

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider) {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user) {
        if ($this->passwordEncoder->isPasswordValid($user, $credentials['password'])) {
            $this->token = $user->getApiToken();
            return true;
        }

        return false;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string {
        return $credentials['password'];
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        return new JsonResponse('failed');
    }


    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
        return new JsonResponse([
            'token' => $this->token,
            'user'  => [
                'username' => $token->getUsername(),
                'roles'    => $token->getRoleNames(),
            ]
        ]);
    }

    protected function getLoginUrl() {
        return $this->urlGenerator->generate('authentication_token');
    }
}
