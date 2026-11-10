<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: \App\Repository\ProductImageRepository::class)]
#[ORM\Table(name: 'product_images', indexes: [
    new ORM\Index(name: 'idx_product_image', columns: ['product_id']),
])]
class ProductImage
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'Ramsey\Uuid\Doctrine\UuidGenerator')]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'images')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\Column(type: 'string', length: 1000)]
    private string $url;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $path = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $filename = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $size = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true, name: 'mime_type')]
    private ?string $mimeType = null;

    #[ORM\Column(type: 'boolean', name: 'is_main')]
    private bool $isMain = false;

    #[ORM\Column(type: 'integer', name: 'sort_order')]
    private int $sortOrder = 0;

    #[ORM\Column(type: 'datetime_immutable', name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true, name: 'downloaded_at')]
    private ?\DateTimeImmutable $downloadedAt = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true, name: 'download_status')]
    private ?string $downloadStatus = null; // 'pending', 'downloaded', 'failed'

    #[ORM\Column(type: 'text', nullable: true, name: 'download_error')]
    private ?string $downloadError = null;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;
        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function isMain(): bool
    {
        return $this->isMain;
    }

    public function setIsMain(bool $isMain): self
    {
        $this->isMain = $isMain;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getDownloadedAt(): ?\DateTimeImmutable
    {
        return $this->downloadedAt;
    }

    public function setDownloadedAt(?\DateTimeImmutable $downloadedAt): self
    {
        $this->downloadedAt = $downloadedAt;
        return $this;
    }

    public function getDownloadStatus(): ?string
    {
        return $this->downloadStatus;
    }

    public function setDownloadStatus(?string $downloadStatus): self
    {
        $this->downloadStatus = $downloadStatus;
        return $this;
    }

    public function getDownloadError(): ?string
    {
        return $this->downloadError;
    }

    public function setDownloadError(?string $downloadError): self
    {
        $this->downloadError = $downloadError;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'url' => $this->url,
            'path' => $this->path,
            'filename' => $this->filename,
            'size' => $this->size,
            'mime_type' => $this->mimeType,
            'is_main' => $this->isMain,
            'sort_order' => $this->sortOrder,
            'download_status' => $this->downloadStatus,
            'download_error' => $this->downloadError,
            'created_at' => $this->createdAt->format('c'),
            'downloaded_at' => $this->downloadedAt?->format('c'),
        ];
    }
}