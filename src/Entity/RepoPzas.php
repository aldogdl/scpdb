<?php

namespace App\Entity;

use App\Repository\RepoPzasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RepoPzasRepository::class)
 */
class RepoPzas
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=RepoMain::class, inversedBy="pzas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $repo;

    /**
     * @ORM\ManyToOne(targetEntity=StatusTypes::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $idTmp;

    /**
     * @ORM\Column(type="integer")
     */
    private $cant;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $pieza;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $lugar;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $lado;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $posicion;

    /**
     * @ORM\Column(type="text")
     */
    private $notas;

    /**
     * @ORM\Column(type="array")
     */
    private $fotos = [];

    /**
     * @ORM\Column(type="float")
     */
    private $precioLess;

    /**
     * @ORM\OneToMany(targetEntity=RepoPzaInfo::class, mappedBy="pzas", orphanRemoval=true)
     */
    private $info;

    public function __construct()
    {
        $this->info = new ArrayCollection();
        $this->precioLess = 0.0;
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

    public function getStatus(): ?StatusTypes
    {
        return $this->status;
    }

    public function setStatus(?StatusTypes $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getIdTmp(): ?string
    {
        return $this->idTmp;
    }

    public function setIdTmp(string $idTmp): self
    {
        $this->idTmp = $idTmp;

        return $this;
    }
    
    public function getCant(): ?int
    {
        return $this->cant;
    }

    public function setCant(int $cant): self
    {
        $this->cant = $cant;

        return $this;
    }
    
    public function getPieza(): ?string
    {
        return $this->pieza;
    }

    public function setPieza(string $pieza): self
    {
        $this->pieza = $pieza;

        return $this;
    }

    public function getLugar(): ?string
    {
        return $this->lugar;
    }

    public function setLugar(string $lugar): self
    {
        $this->lugar = $lugar;

        return $this;
    }

    public function getLado(): ?string
    {
        return $this->lado;
    }

    public function setLado(string $lado): self
    {
        $this->lado = $lado;

        return $this;
    }

    public function getPosicion(): ?string
    {
        return $this->posicion;
    }

    public function setPosicion(string $posicion): self
    {
        $this->posicion = $posicion;

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
    
    public function getPrecioLess(): ?float
    {
        return $this->precioLess;
    }

    public function setPrecioLess(float $precioLess): self
    {
        $this->precioLess = $precioLess;

        return $this;
    }

    /**
     * @return Collection|RepoPzaInfo[]
     */
    public function getInfo(): Collection
    {
        return $this->info;
    }

    public function addInfo(RepoPzaInfo $info): self
    {
        if (!$this->info->contains($info)) {
            $this->info[] = $info;
            $info->setPzas($this);
        }

        return $this;
    }

    public function removeInfo(RepoPzaInfo $info): self
    {
        if ($this->info->removeElement($info)) {
            // set the owning side to null (unless already changed)
            if ($info->getPzas() === $this) {
                $info->setPzas(null);
            }
        }

        return $this;
    }

    public function getNotas(): ?string
    {
        return $this->notas;
    }

    public function setNotas(string $notas): self
    {
        $this->notas = $notas;

        return $this;
    }
}
