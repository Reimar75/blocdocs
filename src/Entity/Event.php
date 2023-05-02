<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Event
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?int $time = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $color = null;

    #[ORM\PrePersist]
    public function handleTime(): void
    {
        $pattern = '/(\d+(?:[\.,]\d+)?)\s*(?:stunden?|std|hours?|h|minuten?|min)/i';
        $string = $this->getName();

        if (preg_match($pattern, $string, $matches)) {
            $timeString = $matches[1];
            $timeString = str_replace(',', '.', $timeString);
            $timeInMinutes = 0;

            if (stripos($matches[0], 'h') !== false) {
                $timeInMinutes = (float) $timeString * 60;
            } else {
                $timeInMinutes = (float) $timeString;
            }

            $string = trim(str_replace($matches[0], '', $string));

            $this->setName($string);
            $this->setTime($timeInMinutes);
        }
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTime(): ?int
    {
        return $this->time;
    }

    public function setTime(?int $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }
}
