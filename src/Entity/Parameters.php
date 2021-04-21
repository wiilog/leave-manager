<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParametersRepository")
 */
class Parameters
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $cpIncrement;

    /**
     * @ORM\Column(type="float")
     */
    private $rttIncrement;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCpIncrement(): ?float
    {
        return $this->cpIncrement;
    }

    public function setCpIncrement(float $cpIncrement): self
    {
        $this->cpIncrement = $cpIncrement;

        return $this;
    }

    public function getRttIncrement(): ?float
    {
        return $this->rttIncrement;
    }

    public function setRttIncrement(float $rttIncrement): self
    {
        $this->rttIncrement = $rttIncrement;

        return $this;
    }
}
