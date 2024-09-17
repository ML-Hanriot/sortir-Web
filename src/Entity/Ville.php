<?php

namespace App\Entity;

use App\Repository\VilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VilleRepository::class)]
class Ville
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?int $codePostal = null;

    /**
     * @var Collection<int, lieu>
     */
    #[ORM\OneToMany(targetEntity: lieu::class, mappedBy: 'ville')]
    private Collection $lieu;

    public function __construct()
    {
        $this->lieu = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCodePostal(): ?int
    {
        return $this->codePostal;
    }

    public function setCodePostal(int $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * @return Collection<int, lieu>
     */
    public function getLieu(): Collection
    {
        return $this->lieu;
    }

    public function addLieu(lieu $lieu): static
    {
        if (!$this->lieu->contains($lieu)) {
            $this->lieu->add($lieu);
            $lieu->setVille($this);
        }

        return $this;
    }

    public function removeLieu(lieu $lieu): static
    {
        if ($this->lieu->removeElement($lieu)) {
            // set the owning side to null (unless already changed)
            if ($lieu->getVille() === $this) {
                $lieu->setVille(null);
            }
        }

        return $this;
    }
}
