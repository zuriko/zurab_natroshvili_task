<?php

namespace App\Entity;

use App\Entity\Category;
use App\Entity\Comment;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class News
{
    private ?int $id = null;
    private ?string $title = null;
    private ?string $shortDescription = null;
    private ?string $content = null;
    private ?\DateTimeInterface $insertedAt = null;
    private ?string $image = null;

    private ?News $news = null;

    /** @var Collection<int, Category> */
    private Collection $categories;

    /** @var Collection<int, Comment> */
    private Collection $comments;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->comments = new ArrayCollection();
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

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $sd): static
    {
        $this->shortDescription = $sd;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getInsertedAt(): ?\DateTimeInterface
    {
        return $this->insertedAt;
    }

    public function setInsertedAt(\DateTimeInterface $date): static
    {
        $this->insertedAt = $date;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }
        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);
        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
        }
        return $this;
    }
    
}
