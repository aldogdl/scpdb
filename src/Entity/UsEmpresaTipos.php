<?php

namespace App\Entity;

use App\Repository\UsEmpresaTiposRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UsEmpresaTiposRepository::class)
 */
class UsEmpresaTipos
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
    private $tipo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $despeq;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $role;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDespeq(): ?string
    {
        return $this->despeq;
    }

    public function setDespeq(string $despeq): self
    {
        $this->despeq = $despeq;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

}
