<?php

namespace App\Entity;

use App\Repository\UsEmpresaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UsEmpresaRepository::class)
 */
class UsEmpresa
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $despeq;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $logo;
    
    /**
     * @ORM\Column(type="array")
     */
    private $marcas = [];

    /**
     * @ORM\Column(type="text")
     */
    private $notas;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $pagWeb;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $razonSocial;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $rfc;

    /**
     * @ORM\Column(type="integer")
     */
    private $domFiscal;

    /**
     * @ORM\ManyToOne(targetEntity=UsEmpresaTipos::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $tipo;

    /**
     * @ORM\ManyToOne(targetEntity=UsAdmin::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $avo;

    /**
     * @ORM\OneToMany(targetEntity=UsSucursales::class, mappedBy="empresa")
     */
    private $sucursales;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $slug;

    public function __construct()
    {
        $this->logo = '0';
        $this->marcas = ['TODAS'];
        $this->notas = '0';
        $this->pagWeb = '0';
        $this->slug = '0';
        $this->razonSocial = '0';
        $this->rfc = '0';
        $this->domFiscal = 0;
        $this->sucursales = new ArrayCollection();
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

    public function getDespeq(): ?string
    {
        return $this->despeq;
    }

    public function setDespeq(string $despeq): self
    {
        $this->despeq = $despeq;

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

    public function getMarcas(): ?array
    {
        return $this->marcas;
    }

    public function setMarcas(array $marcas): self
    {
        $this->marcas = $marcas;

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

    public function getPagWeb(): ?string
    {
        return $this->pagWeb;
    }

    public function setPagWeb(string $pagWeb): self
    {
        $this->pagWeb = $pagWeb;

        return $this;
    }

    public function getRazonSocial(): ?string
    {
        return $this->razonSocial;
    }

    public function setRazonSocial(string $razonSocial): self
    {
        $this->razonSocial = $razonSocial;

        return $this;
    }

    public function getRfc(): ?string
    {
        return $this->rfc;
    }

    public function setRfc(string $rfc): self
    {
        $this->rfc = $rfc;

        return $this;
    }

    public function getDomFiscal(): ?int
    {
        return $this->domFiscal;
    }

    public function setDomFiscal(int $domFiscal): self
    {
        $this->domFiscal = $domFiscal;

        return $this;
    }

    public function getTipo(): ?UsEmpresaTipos
    {
        return $this->tipo;
    }

    public function setTipo(?UsEmpresaTipos $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getAvo(): ?UsAdmin
    {
        return $this->avo;
    }

    public function setAvo(?UsAdmin $avo): self
    {
        $this->avo = $avo;

        return $this;
    }

    /**
     * @return Collection|UsSucursales[]
     */
    public function getSucursales(): Collection
    {
        return $this->sucursales;
    }

    public function addSucursale(UsSucursales $sucursale): self
    {
        if (!$this->sucursales->contains($sucursale)) {
            $this->sucursales[] = $sucursale;
            $sucursale->setEmpresa($this);
        }

        return $this;
    }

    public function removeSucursale(UsSucursales $sucursale): self
    {
        if ($this->sucursales->removeElement($sucursale)) {
            // set the owning side to null (unless already changed)
            if ($sucursale->getEmpresa() === $this) {
                $sucursale->setEmpresa(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}