<?php

namespace App\Entity;

use App\Repository\PublicacionesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PublicacionesRepository::class)
 */
class Publicaciones
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=UsAdmin::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=UsContacts::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $contact;

    /**
     * @ORM\ManyToOne(targetEntity=UsEmpresa::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $emp;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $titulo;

    /**
     * @ORM\Column(type="string", length=42)
     */
    private $sTitulo;

    /**
     * @ORM\Column(type="text")
     */
    private $descr;

    /**
     * @ORM\Column(type="float")
     */
    private $costo;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $catego;

    /**
     * @ORM\Column(type="integer")
     */
    private $visitas;

    /**
     * @ORM\Column(type="datetime")
     */
    private $pubAt;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $conx;

    /**
     * @ORM\Column(type="array")
     */
    private $fotos = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPub;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDelete;


    public function __construct()
    {
        $this->visitas = 0;
        $this->pubAt = new \DateTime('now');
        $this->isPub = true;
        $this->costo = 0.0;
        $this->sTitulo = '0';
        $this->isDelete = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?UsAdmin
    {
        return $this->user;
    }

    public function setUser(?UsAdmin $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getContact(): ?UsContacts
    {
        return $this->contact;
    }

    public function setContact(?UsContacts $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getEmp(): ?UsEmpresa
    {
        return $this->emp;
    }

    public function setEmp(?UsEmpresa $emp): self
    {
        $this->emp = $emp;

        return $this;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getSTitulo(): ?string
    {
        return $this->sTitulo;
    }

    public function setSTitulo(string $sTitulo): self
    {
        $this->sTitulo = $sTitulo;

        return $this;
    }

    public function getDescr(): ?string
    {
        return $this->descr;
    }

    public function setDescr(string $descr): self
    {
        $this->descr = $descr;

        return $this;
    }

    public function getCosto(): ?float
    {
        return $this->costo;
    }

    public function setCosto(float $costo): self
    {
        $this->costo = $costo;

        return $this;
    }

    public function getCatego(): ?string
    {
        return $this->catego;
    }

    public function setCatego(string $catego): self
    {
        $this->catego = $catego;

        return $this;
    }

    public function getVisitas(): ?int
    {
        return $this->visitas;
    }

    public function setVisitas(int $visitas): self
    {
        $this->visitas = $visitas;

        return $this;
    }

    public function getPubAt(): ?\DateTimeInterface
    {
        return $this->pubAt;
    }

    public function setPubAt(\DateTimeInterface $pubAt): self
    {
        $this->pubAt = $pubAt;

        return $this;
    }

    public function getConx(): ?string
    {
        return $this->conx;
    }

    public function setConx(string $conx): self
    {
        $this->conx = $conx;

        return $this;
    }

    public function getFotos(): ?array
    {
        return $this->fotos;
    }

    public function setFotos(array $fotos): self
    {
        $this->fotos = $fotos;

        return $this;
    }

    public function getIsPub(): ?bool
    {
        return $this->isPub;
    }

    public function setIsPub(bool $isPub): self
    {
        $this->isPub = $isPub;

        return $this;
    }

    public function getIsDelete(): ?bool
    {
        return $this->isDelete;
    }

    public function setIsDelete(bool $isDelete): self
    {
        $this->isDelete = $isDelete;

        return $this;
    }

}
