<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * @ORM\Entity(repositoryClass="App\Repository\OfferRepository")
 *
 */
class Offer
{
	use SoftDeleteableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Advert", inversedBy="offers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $advert;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="offers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;


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
     * @ORM\ManyToOne(targetEntity="App\Entity\GamePlatform")
     * @ORM\JoinColumn(name="game_platform_id", referencedColumnName="id", nullable=true)
     */
    private $gamePlatform;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\OfferStatus")
     * @ORM\JoinColumn(nullable=false)
     */
    private $offerStatus;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdvert(): ?Advert
    {
        return $this->advert;
    }

    public function setAdvert(?Advert $advert): self
    {
        $this->advert = $advert;

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


    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
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

    public function getGamePlatform(): ?GamePlatform
    {
        return $this->gamePlatform;
    }

    public function setGamePlatform(?GamePlatform $gamePlatform): self
    {
        $this->gamePlatform = $gamePlatform;

        return $this;
    }

    public function getOfferStatus(): ?OfferStatus
    {
        return $this->offerStatus;
    }

    public function setOfferStatus(?OfferStatus $offerStatus): self
    {
        $this->offerStatus = $offerStatus;

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validateDates(ExecutionContextInterface $context, $payload)
    {
        if($this->startDate > $this->endDate){
            $context->buildViolation('La date de début doit être inferieure à la date de fin')
                ->atPath('startDate')
                ->addViolation();
        }
    }





}
