<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    const STATUT_ACTIF = 'actif';
    const STATUT_INACTIF = 'inactif';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="text")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastConnexion;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Firm", inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     */
    private $firm;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="usersToValidate")
     */
    private $validators;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="validators")
     */
    private $usersToValidate;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Access", inversedBy="users")
     */
    private $accesses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Holiday", mappedBy="requester")
     */
    private $holidays;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $RTT;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $CP;

    public function __construct()
    {
        $this->validators = new ArrayCollection();
        $this->usersToValidate = new ArrayCollection();
        $this->accesses = new ArrayCollection();
        $this->holidays = new ArrayCollection();
    }

    public function __toString()
	{
		$name = ucfirst($this->getFirstname());
		if (!empty($this->getFirstname()) && !empty($this->getName())) $name .= ' ';
		$name .= strtoupper($this->getName());

		return $name;
	}

	public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }



    public function eraseCredentials()
    {
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getLastConnexion(): ?\DateTimeInterface
    {
        return $this->lastConnexion;
    }

    public function setLastConnexion(?\DateTimeInterface $lastConnexion): self
    {
        $this->lastConnexion = $lastConnexion;

        return $this;
    }

    public function getFirm(): ?Firm
    {
        return $this->firm;
    }

    public function setFirm(?Firm $firm): self
    {
        $this->firm = $firm;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getValidators(): Collection
    {
        return $this->validators;
    }

    public function addValidator(User $validator): self
    {
        if (!$this->validators->contains($validator)) {
            $this->validators[] = $validator;
        }

        return $this;
    }

    public function removeValidator(User $validator): self
    {
        if ($this->validators->contains($validator)) {
            $this->validators->removeElement($validator);
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsersToValidate(): Collection
    {
        return $this->usersToValidate;
    }

    public function addUsersToValidate(User $usersToValidate): self
    {
        if (!$this->usersToValidate->contains($usersToValidate)) {
            $this->usersToValidate[] = $usersToValidate;
            $usersToValidate->addValidator($this);
        }

        return $this;
    }

    public function removeUsersToValidate(User $usersToValidate): self
    {
        if ($this->usersToValidate->contains($usersToValidate)) {
            $this->usersToValidate->removeElement($usersToValidate);
            $usersToValidate->removeValidator($this);
        }

        return $this;
    }

    /**
     * @return Collection|Access[]
     */
    public function getAccesses(): Collection
    {
        return $this->accesses;
    }

    public function addAccess(Access $access): self
    {
        if (!$this->accesses->contains($access)) {
            $this->accesses[] = $access;
        }

        return $this;
    }

    public function removeAccess(Access $access): self
    {
        if ($this->accesses->contains($access)) {
            $this->accesses->removeElement($access);
        }

        return $this;
    }

    /**
     * @return Collection|Holiday[]
     */
    public function getHolidays(): Collection
    {
        return $this->holidays;
    }

    public function addHoliday(Holiday $holiday): self
    {
        if (!$this->holidays->contains($holiday)) {
            $this->holidays[] = $holiday;
            $holiday->setRequester($this);
        }

        return $this;
    }

    public function removeHoliday(Holiday $holiday): self
    {
        if ($this->holidays->contains($holiday)) {
            $this->holidays->removeElement($holiday);
            // set the owning side to null (unless already changed)
            if ($holiday->getRequester() === $this) {
                $holiday->setRequester(null);
            }
        }

        return $this;
    }

    public function getRTT(): ?float
    {
        return $this->RTT;
    }

    public function setRTT(?float $RTT): self
    {
        $this->RTT = $RTT;

        return $this;
    }

    public function getCP(): ?float
    {
        return $this->CP;
    }

    public function setCP(?float $CP): self
    {
        $this->CP = $CP;

        return $this;
    }

    public function incrementStockHolidays(Holiday $holiday) {
        $this->setCP(($this->getCP()) - ($holiday->getCp()));
		if ($this->getRTT() !== null) {
			$this->setRTT(($this->getRTT()) - ($holiday->getRtt()));
		}
        return $this;
    }

    public function decrementStockHolidays(Holiday $holiday) {
        $this->setCP(($this->getCP()) + ($holiday->getCp()));
		if ($this->getRTT() !== null) {
			$this->setRTT(($this->getRTT()) + ($holiday->getRtt()));
		}
        return $this;
    }

    public function endOfMonth(Parameters $parameters) {
    	$this->setCP(($this->getCP() + $parameters->getCpIncrement()));
		if ($this->getRTT() !== null) {
			$this->setRTT($this->getRTT() + $parameters->getRttIncrement());
		}
        return $this;
    }
}
