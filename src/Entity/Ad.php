<?php

namespace App\Entity;

use DateTime;
use App\Entity\Image;
use Cocur\Slugify\Slugify;
use App\Repository\AdRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass=AdRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *  fields={"title"},
 *  message="Ce titre est déjà utilisé dans une autre annonce, veuillez donc le modifier"
 * )
 */
class Ad
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *  min=10,
     *  max=100,
     *  minMessage="Le titre de votre annonce doit faire au moins {{ limit }} caractères",
     *  maxMessage="Le titre de votre annonce ne doit pas dépasser {{ limit }} caractères"
     * )
     */
    private string $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(
     *  min=300,
     *  minMessage="La description de votre annonce doit faire au moins {{ limit }} caractères" 
     * )
     */
    private string $description;

    /**
     * @ORM\Column(type="smallint")
     */
    private int $rooms;

    /**
     * @ORM\Column(type="float")
     */
    private int $price;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $thumbnail;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="datetime")
     */
    private $publishedAt;

    /**
     * @ORM\OneToMany(targetEntity=Image::class, mappedBy="ad", orphanRemoval=true, cascade={"persist"})
     */
    private Collection $images;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(
     *  min=100,
     *  max=200,
     *  minMessage="L'introduction de votre annonce doit faire au moins {{ limit }} caractères",
     *  maxMessage="L'introduction de votre annonce ne doit pas dépasser {{ limit }} caractères"
     * )
     */
    private string $introduction;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="ads")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\OneToMany(targetEntity=Booking::class, mappedBy="ad", orphanRemoval=true)
     */
    private $bookings;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="ad", orphanRemoval=true)
     */
    private $comments;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * Retourner true si l'utilisateur à déjà fait un commentaire
     *
     * @param User $user
     * @return Comment[]|bool $comment
     */
    public function isCommented(User $user)
    {
        foreach ($this->comments as $comment) {
            if ($comment->getAuthor() === $user) {
                return $comment;
                break;
            }       
        }

        return false;
    }

    /**
     * Get Rating Ad
     *
     * @return integer|null
     */
    public function getAvgRating()
    {
        if (count($this->comments) > 0) {
            $totalRating = array_reduce(
                $this->comments->toArray(),
                fn($total, Comment $comment) => $total += $comment->getRating()
            );

            return (int) ceil($totalRating / count($this->comments));
        }

    }

    /**
     * Set automaticly publishedAt value when creating or updating an Ad
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     *
     * @return void
     */
    public function setPublishedAtValue(): void
    {
        if (!$this->publishedAt) {
            $this->publishedAt = new DateTime();
        }
    }

    /**
     * Set automaticly Slug value when creating or updating Ad
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     *
     * @return void
     */
    public function setSlugValue(): void
    {
        if (!$this->slug) {
            $slugify = new Slugify();
            $this->slug = $slugify->slugify($this->title);
        }
    }

    /**
     * Permet de nous retourner Objet DateTime contenant toutes les dates qui sont déja réservées
     *
     * @return array représentant toutes les dates qui sont déjà occupées
     */
    public function getBusyDays(): array
    {
        $busyDays = [];
  
        foreach($this->bookings as $booking) {
            // Toutes les dates qui sont déja réservées sous la forme de Timestamp
            $timestampDate = range(
                $booking->getStartDate()->getTimestamp(),
                $booking->getEndDate()->getTimestamp(),
                24 * 3600
            );
   
            // Transformation le tableau $timestampDate en DateTime
            $days = array_map(function($dayTimeStamp) {
                return new \DateTime(date('Y-m-d', $dayTimeStamp));
            }, $timestampDate);

            // Fusionner deux tableaux
            $busyDays = array_merge($busyDays, $days);
        }
  
        return $busyDays;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getRooms(): ?int
    {
        return $this->rooms;
    }

    public function setRooms(int $rooms): self
    {
        $this->rooms = $rooms;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setAd($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getAd() === $this) {
                $image->setAd(null);
            }
        }

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): self
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection|Booking[]
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setAd($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getAd() === $this) {
                $booking->setAd(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAd($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAd() === $this) {
                $comment->setAd(null);
            }
        }

        return $this;
    }
}
