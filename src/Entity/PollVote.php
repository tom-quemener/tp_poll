<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PollVoteRepository")
 */
class PollVote
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="pollVotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Poll", inversedBy="pollVotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $poll;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PollOption", inversedBy="pollVotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $option;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser($user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getPoll(): ?Poll
    {
        return $this->poll;
    }

    public function setPoll($poll): self
    {
        $this->poll = $poll;

        return $this;
    }

    public function getOption(): ?PollOption
    {
        return $this->option;
    }

    public function setOption($option): self
    {
        $this->option = $option;

        return $this;
    }
}
