<?php

namespace App\Entity;

use App\Repository\UsSucursalesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UsSucursalesRepository::class)
 */
class UsSucursales
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=UsEmpresa::class, inversedBy="sucursales")
     * @ORM\JoinColumn(nullable=false)
     */
    private $empresa;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $domicilio;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $entreAntes;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $entreDespues;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $referencias;

    /**
     * @ORM\ManyToOne(targetEntity=LO4Localidades::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $localidad;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $telefono;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $fachada;

    /**
     * @ORM\Column(type="text")
     */
    private $palclas;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $latLng;

    /**
     * @ORM\Column(type="string", length=7)
     */
    private $cp;

    /** */
    public function __construct()
    {
        $this->domicilio   = 'tmp';
        $this->entreAntes  = 'tmp';
        $this->entreDespues= 'tmp';
        $this->referencias = 'tmp';
        $this->telefono    = 'tmp';
        $this->fachada     = '0';
        $this->latLng      = '0';
        $this->palclas     = '0';
        $this->cp          = '0';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmpresa(): ?UsEmpresa
    {
        return $this->empresa;
    }

    public function setEmpresa(?UsEmpresa $empresa): self
    {
        $this->empresa = $empresa;

        return $this;
    }

    public function getDomicilio(): ?string
    {
        return $this->domicilio;
    }

    public function setDomicilio(string $domicilio): self
    {
        $this->domicilio = $domicilio;

        return $this;
    }

    public function getEntreAntes(): ?string
    {
        return $this->entreAntes;
    }

    public function setEntreAntes(string $entreAntes): self
    {
        $this->entreAntes = $entreAntes;

        return $this;
    }

    public function getEntreDespues(): ?string
    {
        return $this->entreDespues;
    }

    public function setEntreDespues(string $entreDespues): self
    {
        $this->entreDespues = $entreDespues;

        return $this;
    }

    public function getReferencias(): ?string
    {
        return $this->referencias;
    }

    public function setReferencias(string $referencias): self
    {
        $this->referencias = $referencias;

        return $this;
    }

    public function getLocalidad(): ?LO4Localidades
    {
        return $this->localidad;
    }

    public function setLocalidad(?LO4Localidades $localidad): self
    {
        $this->localidad = $localidad;

        return $this;
    }

    public function getTelefono(): ?int
    {
        return $this->telefono;
    }

    public function setTelefono(int $telefono): self
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getFachada(): ?string
    {
        return $this->fachada;
    }

    public function setFachada(string $fachada): self
    {
        $this->fachada = $fachada;

        return $this;
    }

    public function getPalclas(): ?string
    {
        return $this->palclas;
    }

    public function setPalclas(string $palclas): self
    {
        $this->palclas = $palclas;

        return $this;
    }

    public function getLatLng(): ?string
    {
        return $this->latLng;
    }

    public function setLatLng(string $latLng): self
    {
        $this->latLng = $latLng;

        return $this;
    }

    public function getCp(): ?string
    {
        return $this->cp;
    }

    public function setCp(string $cp): self
    {
        $this->cp = $cp;

        return $this;
    }
}
