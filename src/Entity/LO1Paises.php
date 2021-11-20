<?php

namespace App\Entity;

use App\Repository\LO1PaisesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LO1PaisesRepository::class)
 */
class LO1Paises
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nombre;

    /**
     * @ORM\OneToMany(targetEntity=LO2Estados::class, mappedBy="pais")
     */
    private $estados;

    public function __construct()
    {
        $this->estados = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * @return Collection|LO2Estados[]
     */
    public function getEstados(): Collection
    {
        return $this->estados;
    }

    public function addEstado(LO2Estados $estado): self
    {
        if (!$this->estados->contains($estado)) {
            $this->estados[] = $estado;
            $estado->setPais($this);
        }

        return $this;
    }

    public function removeEstado(LO2Estados $estado): self
    {
        if ($this->estados->removeElement($estado)) {
            // set the owning side to null (unless already changed)
            if ($estado->getPais() === $this) {
                $estado->setPais(null);
            }
        }

        return $this;
    }
}
