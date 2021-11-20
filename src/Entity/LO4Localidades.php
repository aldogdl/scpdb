<?php

namespace App\Entity;

use App\Repository\LO4LocalidadesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LO4LocalidadesRepository::class)
 */
class LO4Localidades
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=LO3Ciudades::class, inversedBy="localidades")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ciudad;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $tipo;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nombre;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCiudad(): ?LO3Ciudades
    {
        return $this->ciudad;
    }

    public function setCiudad(?LO3Ciudades $ciudad): self
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): self
    {
        $this->tipo = $tipo;

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
}
