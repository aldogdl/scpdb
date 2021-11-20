<?php

namespace App\Entity;

use App\Repository\RepoPzaInfoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RepoPzaInfoRepository::class)
 */
class RepoPzaInfo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=RepoMain::class, inversedBy="pzaInfo")
     * @ORM\JoinColumn(nullable=false)
     */
    private $repo;

    /**
     * @ORM\ManyToOne(targetEntity=RepoPzas::class, inversedBy="info")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pzas;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $id_tmp_pza;

    /**
     * @ORM\Column(type="text")
     */
    private $caracteristicas;

    /**
     * @ORM\Column(type="text")
     */
    private $detalles;

    /**
     * @ORM\Column(type="float")
     */
    private $precio;

    /**
     * @ORM\Column(type="float")
     */
    private $costo;

    /**
     * @ORM\Column(type="float")
     */
    private $comision;

    /**
     * @ORM\Column(type="array")
     */
    private $fotos = [];

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=StatusTypes::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=UsContacts::class)
     */
    private $own;

    /**
     * @ORM\ManyToOne(targetEntity=Sistemas::class, inversedBy="repoPzaInfos")
     */
    private $sistema;

    /**
     * @ORM\ManyToOne(targetEntity=SisCategos::class, inversedBy="repoPzaInfos")
     */
    private $sisCat;

    
    public function __construct()
    {
        $this->createdAt = new \DateTime('now');    
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRepo(): ?RepoMain
    {
        return $this->repo;
    }

    public function setRepo(?RepoMain $repo): self
    {
        $this->repo = $repo;

        return $this;
    }

    public function getPzas(): ?RepoPzas
    {
        return $this->pzas;
    }

    public function setPzas(?RepoPzas $pzas): self
    {
        $this->pzas = $pzas;

        return $this;
    }
    
    public function getIdTmpPza(): ?string
    {
        return $this->id_tmp_pza;
    }

    public function setIdTmpPza(string $id_tmp_pza): self
    {
        $this->id_tmp_pza = $id_tmp_pza;

        return $this;
    }

    public function getCaracteristicas(): ?string
    {
        return $this->caracteristicas;
    }

    public function setCaracteristicas(string $caracteristicas): self
    {
        $this->caracteristicas = $caracteristicas;

        return $this;
    }

    public function getDetalles(): ?string
    {
        return $this->detalles;
    }

    public function setDetalles(string $detalles): self
    {
        $this->detalles = $detalles;

        return $this;
    }

    public function getPrecio(): ?float
    {
        return $this->precio;
    }

    public function setPrecio(float $precio): self
    {
        $this->precio = $precio;

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

    public function getComision(): ?float
    {
        return $this->comision;
    }

    public function setComision(float $comision): self
    {
        $this->comision = $comision;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStatus(): ?StatusTypes
    {
        return $this->status;
    }

    public function setStatus(?StatusTypes $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getOwn(): ?UsContacts
    {
        return $this->own;
    }

    public function setOwn(?UsContacts $own): self
    {
        $this->own = $own;

        return $this;
    }

    public function getSistema(): ?Sistemas
    {
        return $this->sistema;
    }

    public function setSistema(?Sistemas $sistema): self
    {
        $this->sistema = $sistema;

        return $this;
    }

    public function getSisCat(): ?SisCategos
    {
        return $this->sisCat;
    }

    public function setSisCat(?SisCategos $sisCat): self
    {
        $this->sisCat = $sisCat;

        return $this;
    }

}
