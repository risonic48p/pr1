<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\UniqueConstraint(name: 'IDX_UNIQUE_REVIEW', columns: ['product_id', 'author_name', 'created_at'])]
#[UniqueEntity(fields: ['product', 'authorName', 'createdAt'])]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $authorName = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $rate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $marketId = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'reviews', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): static
    {
        $this->authorName = $authorName;

        return $this;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(int $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getMarketId(): ?string
    {
        return $this->marketId;
    }

    public function setMarketId(?string $marketId): static
    {
        $this->marketId = $marketId;

        return $this;
    }

}
