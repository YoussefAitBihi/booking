<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\BookingRepository;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BookingRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Booking
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $booker;

    /**
     * @ORM\ManyToOne(targetEntity=Ad::class, inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ad;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\GreaterThan(
     *  "today",
     *  message="La date d'arrivée doit faire au moins la date d'aujourd'hui"
     * )
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\GreaterThan(
     *  propertyPath="startDate",
     *  message="La date de départ doit logiquement être supérieur à la date d'arrivée"
     * )
     */
    private $endDate;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Permet de calculer la durée de la réservation
     *
     * @return integer
     */
    public function getDuration(): int
    {
        if ($this->startDate && $this->endDate) {
            return $this->endDate->diff($this->startDate)->days;
        }
    }

    /**
     * Sert à remplir automatiquement la date de création et le montant
     * 
     * @ORM\PrePersist
     * @ORM\PreUpdate
     *
     * @return void
     */
    public function setCreatedAtAndAmountValue(): void
    {        
        if (!$this->createdAt) {
            $this->createdAt = new \DateTime();
        }

        if (!$this->amount) {
            $this->amount = $this->getDuration() * $this->ad->getPrice();
        }
    }

    /**
     * Return true when ad is bookable and false when ad is not
     *
     * @return boolean
     */
    public function isBookableAd(): bool
    {
        $selectedDays = $this->getSelectedDays();

        // Les dates qui sont occupées
        $busyDays = $this->ad->getBusyDays();

        // Les dates sélectionées par l'utilisateur

        // Fonction anonyme qui sert à transformer un tableau d'objet DateTime en string
        $datetimeToString = function($dayTime) {
            return $dayTime->format('Y-m-d');
        };
        
        // Transoformation les deux tableaux dateTime sous la forme de string
        $busyDaysToString = array_map($datetimeToString, $busyDays);

        $selectedDaysToString = array_map($datetimeToString, $selectedDays);

        foreach($selectedDaysToString as $day) {
            if (array_search($day, $busyDaysToString) !== false) return false;
        }

        return true;
    }

    /**
     * Permet connaitre les dates sélectionnées par l'utilisateur
     *
     * @return array
     */
    public function getSelectedDays(): array
    {
        $timestampDate = range(
            $this->startDate->getTimestamp(),
            $this->endDate->getTimestamp(),
            24 * 3600
        );

        // Tranfrom $timestampDate to datetime object
        $days = array_map(function($dayTimeStamp) {
            return new \DateTime(date('Y-m-d', $dayTimeStamp));
        }, $timestampDate);

        return $days;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooker(): ?User
    {
        return $this->booker;
    }

    public function setBooker(?User $booker): self
    {
        $this->booker = $booker;

        return $this;
    }

    public function getAd(): ?Ad
    {
        return $this->ad;
    }

    public function setAd(?Ad $ad): self
    {
        $this->ad = $ad;

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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
