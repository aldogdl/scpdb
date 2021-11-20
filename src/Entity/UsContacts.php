<?php

namespace App\Entity;

use App\Repository\UsContactsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UsContactsRepository::class)
 */
class UsContacts
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=UsAdmin::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=UsSucursales::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $sucursal;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $celular;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $cargo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $notifiKey;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $notifWeb;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?UsAdmin
    {
        return $this->user;
    }

    public function setUser(UsAdmin $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getSucursal(): ?UsSucursales
    {
        return $this->sucursal;
    }

    public function setSucursal(?UsSucursales $sucursal): self
    {
        $this->sucursal = $sucursal;

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

    public function getCelular(): ?int
    {
        return $this->celular;
    }

    public function setCelular(int $celular): self
    {
        $this->celular = $celular;

        return $this;
    }

    public function getCargo(): ?string
    {
        return $this->cargo;
    }

    public function setCargo(string $cargo): self
    {
        $this->cargo = $cargo;

        return $this;
    }

    public function getNotifiKey(): ?string
    {
        return $this->notifiKey;
    }

    public function setNotifiKey(string $notifiKey): self
    {
        $this->notifiKey = $notifiKey;

        return $this;
    }

    public function getNotifWeb(): ?string
    {
        return $this->notifWeb;
    }

    public function setNotifWeb(string $notifWeb): self
    {
        $this->notifWeb = $notifWeb;

        return $this;
    }

}
