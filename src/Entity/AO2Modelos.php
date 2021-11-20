<?php

namespace App\Entity;

use App\Repository\AO2ModelosRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AO2ModelosRepository::class)
 */
class AO2Modelos
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=AO1Marcas::class, inversedBy="modelo")
     * @ORM\JoinColumn(nullable=false)
     */
    private $marca;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $nombre;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMarca(): ?AO1Marcas
    {
        return $this->marca;
    }

    public function setMarca(?AO1Marcas $marca): self
    {
        $this->marca = $marca;

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
