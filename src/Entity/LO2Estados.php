<?php

namespace App\Entity;

use App\Repository\LO2EstadosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LO2EstadosRepository::class)
 */
class LO2Estados
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=LO1Paises::class, inversedBy="estados")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pais;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nombre;

    /**
     * @ORM\OneToMany(targetEntity=LO3Ciudades::class, mappedBy="estado")
     */
    private $ciudades;

    public function __construct()
    {
        $this->ciudades = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPais(): ?LO1Paises
    {
        return $this->pais;
    }

    public function setPais(?LO1Paises $pais): self
    {
        $this->pais = $pais;

        return $this;
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
     * @return Collection|LO3Ciudades[]
     */
    public function getCiudades(): Collection
    {
        return $this->ciudades;
    }

    public function addCiudade(LO3Ciudades $ciudade): self
    {
        if (!$this->ciudades->contains($ciudade)) {
            $this->ciudades[] = $ciudade;
            $ciudade->setEstado($this);
        }

        return $this;
    }

    public function removeCiudade(LO3Ciudades $ciudade): self
    {
        if ($this->ciudades->removeElement($ciudade)) {
            // set the owning side to null (unless already changed)
            if ($ciudade->getEstado() === $this) {
                $ciudade->setEstado(null);
            }
        }

        return $this;
    }
}
