<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Repository\RouteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(mercure=true)
 * @ORM\Entity(repositoryClass=RouteRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Route
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     */
    private $score;

    /**
     * @ApiSubresource()
     * @ORM\OneToOne(targetEntity=Article::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $description;

    /**
     * @ApiSubresource()
     * @ORM\OneToMany(targetEntity=Point::class, mappedBy="route", orphanRemoval=true)
     */
    private $points;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="routes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function __construct()
    {
        $this->points = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getDescription(): Article
    {
        return $this->description;
    }

    public function setDescription(Article $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCover(): ?Media {
        return $this->getDescription()->getMedia()->first();
    }

    /**
     * @return Collection|Point[]
     */
    public function getPoints(): Collection
    {
        return $this->points;
    }

    public function addPoint(Point $point): self
    {
        if (!$this->points->contains($point)) {
            $this->points[] = $point;
            $point->setRoute($this);
        }

        return $this;
    }

    public function removePoint(Point $point): self
    {
        if ($this->points->contains($point)) {
            $this->points->removeElement($point);
            // set the owning side to null (unless already changed)
            if ($point->getRoute() === $this) {
                $point->setRoute(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
