<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Participant;
use App\Entity\Sortie;
/**
 * @ORM\Entity()
 * @ORM\Table(name="participant_sortie")
 */
class ParticipantSortie
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="participant_id", referencedColumnName="id", nullable=false)
     */
    private $participant;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Entity\Sortie")
     * @ORM\JoinColumn(name="sortie_id", referencedColumnName="id", nullable=false)
     */
    private $sortie;

    // Getters and Setters...

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(?Participant $participant): self
    {
        $this->participant = $participant;
        return $this;
    }

    public function getSortie(): ?Sortie
    {
        return $this->sortie;
    }

    public function setSortie(?Sortie $sortie): self
    {
        $this->sortie = $sortie;
        return $this;
    }
}
