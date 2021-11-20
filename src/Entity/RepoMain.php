<?php

namespace App\Entity;

use App\Repository\RepoMainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RepoMainRepository::class)
 */
class RepoMain
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $regType;

    /**
     * @ORM\ManyToOne(targetEntity=RepoAutos::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $auto;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $via;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=UsAdmin::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $admin;

    /**
     * @ORM\Column(type="integer")
     */
    private $own;

    /**
     * @ORM\ManyToOne(targetEntity=StatusTypes::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=RepoPzas::class, mappedBy="repo", orphanRemoval=true)
     */
    private $pzas;

    /**
     * @ORM\OneToMany(targetEntity=RepoPzaInfo::class, mappedBy="repo", orphanRemoval=true)
     */
    private $pzaInfo;


    ///
    public function __construct()
    {
        $this->createdAt = new \DateTime('now');
        $this->pzas = new ArrayCollection();
        $this->pzaInfo = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegType(): ?string
    {
        return $this->regType;
    }

    public function setRegType(string $regType): self
    {
        $this->regType = $regType;

        return $this;
    }

    public function getAuto(): ?RepoAutos
    {
        return $this->auto;
    }

    public function setAuto(?RepoAutos $auto): self
    {
        $this->auto = $auto;

        return $this;
    }
    
    public function getVia(): ?string
    {
        return $this->via;
    }

    public function setVia(string $via): self
    {
        $this->via = $via;

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

    public function getAdmin(): ?UsAdmin
    {
        return $this->admin;
    }

    public function setAdmin(?UsAdmin $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getOwn(): ?int
    {
        return $this->own;
    }

    public function setOwn(int $own): self
    {
        $this->own = $own;

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

    /**
     * @return Collection|RepoPzas[]
     */
    public function getPzas(): Collection
    {
        return $this->pzas;
    }

    public function addPza(RepoPzas $pza): self
    {
        if (!$this->pzas->contains($pza)) {
            $this->pzas[] = $pza;
            $pza->setRepo($this);
        }

        return $this;
    }

    public function removePza(RepoPzas $pza): self
    {
        if ($this->pzas->removeElement($pza)) {
            // set the owning side to null (unless already changed)
            if ($pza->getRepo() === $this) {
                $pza->setRepo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RepoPzaInfo[]
     */
    public function getPzaInfo(): Collection
    {
        return $this->pzaInfo;
    }

    public function addPzaInfo(RepoPzaInfo $pzaInfo): self
    {
        if (!$this->pzaInfo->contains($pzaInfo)) {
            $this->pzaInfo[] = $pzaInfo;
            $pzaInfo->setRepo($this);
        }

        return $this;
    }

    public function removePzaInfo(RepoPzaInfo $pzaInfo): self
    {
        if ($this->pzaInfo->removeElement($pzaInfo)) {
            // set the owning side to null (unless already changed)
            if ($pzaInfo->getRepo() === $this) {
                $pzaInfo->setRepo(null);
            }
        }

        return $this;
    }

}
