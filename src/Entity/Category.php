<?php

namespace App\Entity;
use App\Entity\News;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


class Category
{
    private ?int $id = null;
    private ?string $title = null;

    private Collection $news;

    public function __construct()
    {
        $this->news = new ArrayCollection();
    }

    public function getNews(): Collection
    {
        return $this->news;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }
    
    public function __toString(): string
    {
        return (string) $this->getTitle();
    }
}
