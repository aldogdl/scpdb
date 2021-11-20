<?php

namespace App\Entity;

use App\Repository\AO1MarcasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AO1MarcasRepository::class)
 */
class AO1Marcas
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
     * @ORM\Column(type="string", length=70)
     */
    private $logo;

    /**
     * @ORM\OneToMany(targetEntity=AO2Modelos::class, mappedBy="marca")
     */
    private $modelo;


    public function __construct()
    {
        $this->modelo = new ArrayCollection();
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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * @return Collection|AO2Modelos[]
     */
    public function getModelo(): Collection
    {
        return $this->modelo;
    }

    public function addModelo(AO2Modelos $modelo): self
    {
        if (!$this->modelo->contains($modelo)) {
            $this->modelo[] = $modelo;
            $modelo->setMarca($this);
        }

        return $this;
    }

    public function removeModelo(AO2Modelos $modelo): self
    {
        if ($this->modelo->removeElement($modelo)) {
            // set the owning side to null (unless already changed)
            if ($modelo->getMarca() === $this) {
                $modelo->setMarca(null);
            }
        }

        return $this;
    }

}
