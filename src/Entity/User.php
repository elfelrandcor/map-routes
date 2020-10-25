<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ApiResource(
 *     attributes={
 *     "normalization_context"={"groups"={"read"}},
 *     "denormalization_context"={"groups"={"write"}}
 * },
 *     mercure=true,
 *     itemOperations={
 *          "get"={
 *             "path"="/users/{id}",
 *             "swagger_context"={
 *                 "tags"={"User"}
 *             }
 *          },
 *          "put"={
 *              "security"="is_granted('ROLE_ADMIN') or object.id == user.id"
 *          }
 *     },
 *     collectionOperations={
 *         "post"={
 *             "path"="/users",
 *             "method"="POST",
 *             "security"="is_granted('ROLE_ADMIN')",
 *             "validation_groups"={"Default", "create"},
 *             "swagger_context"={
 *                 "tags"={"Authentication"},
 *                 "summary"={"User registration"}
 *             }
 *         },
 *         "get"={
 *             "method"="GET",
 *             "swagger_context"={
 *                 "tags"={"User"}
 *             }
 *          }
 *     },
 * )
 */
class User implements UserInterface {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email()
     * @Groups({"read", "write"})
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Groups({"admin:write", "read"})
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @var string Plain password
     * @Groups("write")
     * @SerializedName("password")
     * @Assert\NotBlank(groups={"create"})
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     * @Groups({"admin:read"})
     */
    private $apiToken;

    /**
     * @ORM\OneToMany(targetEntity=Route::class, mappedBy="user", orphanRemoval=true)
     * @Groups({"read", "write"})
     */
    private $routes;

    public function __construct() {
        $this->routes = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string {
        return (string)$this->email;
    }

    public function getEmail(): string {
        return (string)$this->email;
    }

    public function setEmail(string $email): self {
        $this->email = $email;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string {
        return (string)$this->password;
    }

    public function setPassword(string $password): self {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt() {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials() {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getApiToken(): ?string {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): void {
        $this->apiToken = $apiToken ?? md5(uniqid(mt_rand(), true));
    }

    /**
     * @ORM\PrePersist
     */
    public function createToken(): void {
        if (!$this->apiToken) {
            $this->apiToken = md5(uniqid(mt_rand(), true));
        }
    }

    /**
     * @return Collection|Route[]
     */
    public function getRoutes(): Collection {
        return $this->routes;
    }

    public function addRoute(Route $route): self {
        if (!$this->routes->contains($route)) {
            $this->routes[] = $route;
            $route->setUser($this);
        }

        return $this;
    }

    public function removeRoute(Route $route): self {
        if ($this->routes->contains($route)) {
            $this->routes->removeElement($route);
            // set the owning side to null (unless already changed)
            if ($route->getUser() === $this) {
                $route->setUser(null);
            }
        }

        return $this;
    }

    public function getPlainPassword(): ?string {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self {
        $this->plainPassword = $plainPassword;

        return $this;
    }
}
