<?php

namespace App\Entity;

use App\Repository\LO3CiudadesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LO3CiudadesRepository::class)
 */
class LO3Ciudades
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=LO2Estados::class, inversedBy="ciudades")
     * @ORM\JoinColumn(nullable=false)
     */
    private $estado;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nombre;

    /**
     * @ORM\OneToMany(targetEntity=LO4Localidades::class, mappedBy="ciudad")
     */
    private $localidades;

    public function __construct()
    {
        $this->localidades = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEstado(): ?LO2Estados
    {
        return $this->estado;
    }

    public function setEstado(?LO2Estados $estado): self
    {
        $this->estado = $estado;

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
     * @return Collection|LO4Localidades[]
     */
    public function getLocalidades(): Collection
    {
        return $this->localidades;
    }

    public function addLocalidade(LO4Localidades $localidade): self
    {
        if (!$this->localidades->contains($localidade)) {
            $this->localidades[] = $localidade;
            $localidade->setCiudad($this);
        }

        return $this;
    }

    public function removeLocalidade(LO4Localidades $localidade): self
    {
        if ($this->localidades->removeElement($localidade)) {
            // set the owning side to null (unless already changed)
            if ($localidade->getCiudad() === $this) {
                $localidade->setCiudad(null);
            }
        }

        return $this;
    }
}
