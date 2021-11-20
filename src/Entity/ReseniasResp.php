<?php

namespace App\Entity;

use App\Repository\ReseniasRespRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReseniasRespRepository::class)
 */
class ReseniasResp
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
    private $howResp;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $howNombre;

    /**
     * @ORM\ManyToOne(targetEntity=Resenias::class, inversedBy="respuestas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $resenia;

    /**
     * @ORM\Column(type="string", length=290)
     */
    private $respuesta;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /** */
    public function __construct()
    {
        $this->createdAt = new DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHowResp(): ?UsAdmin
    {
        return $this->howResp;
    }

    public function setHowResp(?UsAdmin $howResp): self
    {
        $this->howResp = $howResp;

        return $this;
    }

    public function getHowNombre(): ?string
    {
        return $this->howNombre;
    }

    public function setHowNombre(string $howNombre): self
    {
        $this->howNombre = $howNombre;

        return $this;
    }

    public function getResenia(): ?Resenias
    {
        return $this->resenia;
    }

    public function setResenia(?Resenias $resenia): self
    {
        $this->resenia = $resenia;

        return $this;
    }

    public function getRespuesta(): ?string
    {
        return $this->respuesta;
    }

    public function setRespuesta(string $respuesta): self
    {
        $this->respuesta = $respuesta;

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

}
