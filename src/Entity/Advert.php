<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdvertRepository")
 */
class Advert
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Item", inversedBy="adverts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $itemOwned;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Item", inversedBy="adverts")
     */
    private $itemWanted;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AdvertStatus")
     */
    private $advertStatus;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AdvertType")
     */
    private $advertType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="adverts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Offer", mappedBy="advert")
     */
    private $offers;

    public function __construct()
    {
        $this->offers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemOwned(): ?Item
    {
        return $this->itemOwned;
    }

    public function setItemOwned(?Item $itemOwned): self
    {
        $this->itemOwned = $itemOwned;

        return $this;
    }

    public function getItemWanted(): ?Item
    {
        return $this->itemWanted;
    }

    public function setItemWanted(?Item $itemWanted): self
    {
        $this->itemWanted = $itemWanted;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getAdvertStatus(): ?AdvertStatus
    {
        return $this->advertStatus;
    }

    public function setAdvertStatus(?AdvertStatus $advertStatus): self
    {
        $this->advertStatus = $advertStatus;

        return $this;
    }

    public function getAdvertType(): ?AdvertType
    {
        return $this->advertType;
    }

    public function setAdvertType(?AdvertType $advertType): self
    {
        $this->advertType = $advertType;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return Collection|Offer[]
     */
    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function addOffer(Offer $offer): self
    {
        if (!$this->offers->contains($offer)) {
            $this->offers[] = $offer;
            $offer->setAdvert($this);
        }

        return $this;
    }

    public function removeOffer(Offer $offer): self
    {
        if ($this->offers->contains($offer)) {
            $this->offers->removeElement($offer);
            // set the owning side to null (unless already changed)
            if ($offer->getAdvert() === $this) {
                $offer->setAdvert(null);
            }
        }

        return $this;
    }
}