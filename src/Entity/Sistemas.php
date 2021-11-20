<?php

namespace App\Entity;

use App\Repository\SistemasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SistemasRepository::class)
 */
class Sistemas
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
    private $nombre;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $ftec;

    /**
     * @ORM\OneToMany(targetEntity=RepoPzaInfo::class, mappedBy="sistema")
     */
    private $repoPzaInfos;

    public function __construct()
    {
        $this->ftec = '0';
        $this->repoPzaInfos = new ArrayCollection();
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

    public function getFtec(): ?string
    {
        return $this->ftec;
    }

    public function setFtec(string $ftec): self
    {
        $this->ftec = $ftec;

        return $this;
    }

    /**
     * @return Collection|RepoPzaInfo[]
     */
    public function getRepoPzaInfos(): Collection
    {
        return $this->repoPzaInfos;
    }

    public function addRepoPzaInfo(RepoPzaInfo $repoPzaInfo): self
    {
        if (!$this->repoPzaInfos->contains($repoPzaInfo)) {
            $this->repoPzaInfos[] = $repoPzaInfo;
            $repoPzaInfo->setSistema($this);
        }

        return $this;
    }

    public function removeRepoPzaInfo(RepoPzaInfo $repoPzaInfo): self
    {
        if ($this->repoPzaInfos->removeElement($repoPzaInfo)) {
            // set the owning side to null (unless already changed)
            if ($repoPzaInfo->getSistema() === $this) {
                $repoPzaInfo->setSistema(null);
            }
        }

        return $this;
    }
}
