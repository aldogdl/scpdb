<?php

namespace App\Entity;

use App\Repository\SisCategosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SisCategosRepository::class)
 */
class SisCategos
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
     * @ORM\OneToMany(targetEntity=RepoPzaInfo::class, mappedBy="sisCat")
     */
    private $repoPzaInfos;

    public function __construct()
    {
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
            $repoPzaInfo->setSisCat($this);
        }

        return $this;
    }

    public function removeRepoPzaInfo(RepoPzaInfo $repoPzaInfo): self
    {
        if ($this->repoPzaInfos->removeElement($repoPzaInfo)) {
            // set the owning side to null (unless already changed)
            if ($repoPzaInfo->getSisCat() === $this) {
                $repoPzaInfo->setSisCat(null);
            }
        }

        return $this;
    }
}
