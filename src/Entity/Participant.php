<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[UniqueEntity(fields: 'mail')]
#[UniqueEntity(fields: 'pseudo')]
class Participant implements UserInterface,PasswordAuthenticatedUserInterface,\Serializable
//class Participant implements UserInterface,PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Veuillez fournir votre nom d'utilisateur")]
    #[Assert\Length(min: 3, max: 50, minMessage:"Minimum 3 caractères", maxMessage: "Maximum 50 caractères")]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "Veuillez fournir votre numéro de téléphone")]
    #[Assert\Regex(
        pattern: '/^\+?[0-9]{10,15}$/',
        message: "Le numéro de téléphone doit contenir entre 10 chiffres"
    )]
    private ?string $telephone = null;

    #[ORM\Column(length: 180,unique: true)]
    #[Assert\Email(message: "Veuillez entrer une adresse email valide")]
    private ?string $mail = null;

    // #[ORM\Column(name:'motPasse',length: 255)]
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Veuillez fournir un mot de passe")]
    #[Assert\Length(
        min: 6,
        minMessage: "Le mot de passe doit comporter au moins {{ limit }} caractères."
    )]
    private ?string $motPasse = null;

    #[ORM\Column]
    private ?bool $administrateur = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\Column(length: 50,unique: true)]
    #[Assert\NotBlank(message: "Veuillez fournir un pseudo")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Le pseudo doit comporter au moins {{ limit }} caractères.",
        maxMessage: "Le pseudo ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_]+$/',
        message: "Le pseudo ne doit contenir que des lettres, des chiffres et des traits de soulignement."
    )]
    private ?string $pseudo = null;

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\ManyToMany(targetEntity: Sortie::class, inversedBy: 'participants')]
    private Collection $sorties;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'organisateur')]
    private Collection $sortiesOrganisees;

    #[ORM\Column(name: 'imageName', type: 'string', length: 255, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $updatedAt = null;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
        $this->sortiesOrganisees = new ArrayCollection();
        $this->actif = true; // Initialisez par défaut à true ou false selon votre logique
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getMotPasse(): ?string
    {
        return $this->motPasse;
    }

    public function setMotPasse(string $motPasse): static
    {
        $this->motPasse = $motPasse;

        return $this;
    }

    public function isAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): static
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(?bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection<int, sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSortie(Sortie $sortie): static
    {
        if (!$this->sorties->contains($sortie)) {
            $this->sorties->add($sortie);
        }

        return $this;
    }

    public function removeSortie(Sortie $sortie): static
    {
        $this->sorties->removeElement($sortie);

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesOrganisees(): Collection
    {
        return $this->sortiesOrganisees;
    }

    public function addSortieOrganisee(Sortie $sortie): static
    {
        if (!$this->sortiesOrganisees->contains($sortie)) {
            $this->sortiesOrganisees->add($sortie);
            $sortie->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSortieOrganisee(Sortie $sortie): static
    {
        if ($this->sortiesOrganisees->removeElement($sortie)) {
            // set the owning side to null (unless already changed)
            if ($sortie->getOrganisateur() === $this) {
                $sortie->setOrganisateur(null);
            }
        }

        return $this;
    }

    public function getPassword(): ?string
    {
       return $this->motPasse;
    }

    //STEPHANE
    public function setRoles(array $roles): self
    {
        $this->roles = $roles; // Assigne le tableau de rôles à la propriété `roles`

return $this;
}

    public function getRoles(): array
    {
        return $this->administrateur ? ['ROLE_ADMIN'] : ['ROLE_USER'];
    }

    public function eraseCredentials()
    {
        //
    }

    public function getUserIdentifier(): string
    {
        return $this->mail;
    }
    private ?string $plainPassword = null;

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }


    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function serialize(): ?string
    {
        return serialize([
            $this->id,
            $this->pseudo,
            $this->mail,
            $this->motPasse]);
    }

    public function unserialize(string $data): void
    {
        list(
            $this->id,
            $this->pseudo,
            $this->mail,
            $this->motPasse,
            ) = unserialize($data, ['allowed_classes' => false]);
    }

    #[Vich\UploadableField(mapping: 'profile_pictures', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile = null): self
    {
        $this->imageFile = $imageFile;

        if ($imageFile) {
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }
}