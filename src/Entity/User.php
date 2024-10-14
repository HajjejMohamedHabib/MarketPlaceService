<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\traits\TimeStampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\HasLifecycleCallbacks()]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimeStampTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[Assert\NotBlank(message: 'This field is required')]
    #[Assert\Email(message: 'This email is not valid')]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    //#[Assert\NotBlank(message: 'This field is required')]
    //#[Assert\Choice(choices: ['ROLE_USER', 'ROLE_ADMIN','ROLE_PROVIDER'],message: 'Select a valid role')]
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    //#[Assert\NotBlank(message: 'This field is required')]
    //#[Assert\Length(min: 8,minMessage: 'Password must contain at least 8 characters')]
    #[ORM\Column]
    private ?string $password = null;
    #[Assert\NotBlank(message: 'This field is required')]
    #[Assert\Length(max: 50,maxMessage: 'Name cannot exceed 50 characters',)]
    #[Assert\Regex(pattern: "/^[a-zA-ZÀ-ÿ '-]+$/u",message: 'The first name can only contain letters
')]
    #[ORM\Column(length: 50)]
    private ?string $name = null;
    #[Assert\Regex(pattern: '/^\+?[0-9\s\-]+$/',message: 'The phone number can only contain digits')]
    #[Assert\Length(min: 8, max: 8,minMessage: 'the phone number must contain 8 digits')]
    #[ORM\Column(length: 50)]
    private ?string $phone_number = null;
    #[Assert\Length(max: 255,maxMessage: 'The address cannot exceed 255 characters',)]
    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profile_image = null;

    /**
     * @var Collection<int, Service>
     */
    #[ORM\OneToMany(targetEntity: Service::class, mappedBy: 'user')]
    private Collection $service;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user')]
    private Collection $orderr;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'user')]
    private Collection $review;

    #[ORM\Column]
    private bool $isVerified = false;

    public function __construct()
    {
        $this->service = new ArrayCollection();
        $this->orderr = new ArrayCollection();
        $this->review = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(string $phone_number): static
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getProfileImage(): ?string
    {
        return $this->profile_image;
    }

    public function setProfileImage(?string $profile_image): static
    {
        $this->profile_image = $profile_image;

        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getService(): Collection
    {
        return $this->service;
    }

    public function addService(Service $service): static
    {
        if (!$this->service->contains($service)) {
            $this->service->add($service);
            $service->setUser($this);
        }

        return $this;
    }

    public function removeService(Service $service): static
    {
        if ($this->service->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getUser() === $this) {
                $service->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrderr(): Collection
    {
        return $this->orderr;
    }

    public function addOrderr(Order $orderr): static
    {
        if (!$this->orderr->contains($orderr)) {
            $this->orderr->add($orderr);
            $orderr->setUser($this);
        }

        return $this;
    }

    public function removeOrderr(Order $orderr): static
    {
        if ($this->orderr->removeElement($orderr)) {
            // set the owning side to null (unless already changed)
            if ($orderr->getUser() === $this) {
                $orderr->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReview(): Collection
    {
        return $this->review;
    }

    public function addReview(Review $review): static
    {
        if (!$this->review->contains($review)) {
            $this->review->add($review);
            $review->setUser($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->review->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getUser() === $this) {
                $review->setUser(null);
            }
        }

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
