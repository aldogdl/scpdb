<?php

namespace App\Entity;

use App\Repository\ReseniasRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReseniasRepository::class)
 */
class Resenias
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * El ID de la empresa a la cual pertenece esta resenia.
     * 
     * @ORM\ManyToOne(targetEntity=UsEmpresa::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $emp;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $fromNombre;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $toSlug;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $calif;

    /**
     * @ORM\Column(type="string", length=290)
     */
    private $resenia;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublic;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=ReseniasResp::class, mappedBy="resenia", orphanRemoval=true)
     */
    private $respuestas;


    /** */
    public function __construct()
    {
        $this->createdAt = new DateTime('now');
        $this->isPublic = false;
        $this->respuestas = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFromNombre(): ?string
    {
        return $this->fromNombre;
    }

    public function setFromNombre(string $fromNombre): self
    {
        $this->fromNombre = $fromNombre;

        return $this;
    }

    public function getToSlug(): ?string
    {
        return $this->toSlug;
    }

    public function setToSlug(string $toSlug): self
    {
        $this->toSlug = $toSlug;

        return $this;
    }

    public function getCalif(): ?string
    {
        return $this->calif;
    }

    public function setCalif(string $calif): self
    {
        $this->calif = $calif;

        return $this;
    }

    public function getResenia(): ?string
    {
        return $this->resenia;
    }

    public function setResenia(string $resenia): self
    {
        $this->resenia = $resenia;

        return $this;
    }

    public function getIsPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;

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

    /**
     * @return Collection|ReseniasResp[]
     */
    public function getRespuestas(): Collection
    {
        return $this->respuestas;
    }

    public function addRespuesta(ReseniasResp $respuesta): self
    {
        if (!$this->respuestas->contains($respuesta)) {
            $this->respuestas[] = $respuesta;
            $respuesta->setResenia($this);
        }

        return $this;
    }

    public function removeRespuesta(ReseniasResp $respuesta): self
    {
        if ($this->respuestas->removeElement($respuesta)) {
            // set the owning side to null (unless already changed)
            if ($respuesta->getResenia() === $this) {
                $respuesta->setResenia(null);
            }
        }

        return $this;
    }
}
