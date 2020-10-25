<?php


namespace App\DataPersister;


use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserDataPersister implements DataPersisterInterface {

    protected $em;
    private   $passwordEncoder;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder) {
        $this->em              = $em;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports($data): bool {
        return $data instanceof User;
    }

    /**
     * @param User $data
     *
     * @return object|void
     */
    public function persist($data) {
        if ($plain = $data->getPlainPassword()) {
            $data->setPassword(
                $this->passwordEncoder->encodePassword($data, $plain)
            );
            $data->eraseCredentials();
        }

        $this->em->persist($data);
        $this->em->flush();
    }

    public function remove($data) {
        $this->em->remove($data);
        $this->em->flush();
    }
}