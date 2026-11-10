<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: \App\Repository\ProductRepository::class)]
#[ORM\Table(name: 'products', indexes: [
    new ORM\Index(name: 'idx_external_code', columns: ['external_code']),
    new ORM\Index(name: 'idx_name', columns: ['name']),
])]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'Ramsey\Uuid\Doctrine\UuidGenerator')]
    private UuidInterface $id;

    #[ORM\Column(type: 'string', length: 255, unique: true, name: 'external_code')]
    private string $externalCode;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $price;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $discount = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $currency = 'RUB';

    #[ORM\Column(type: 'string', length: 255, nullable: true, name: 'sku')]
    private ?string $sku = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true, name: 'unit')]
    private ?string $unit = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true, name: 'country')]
    private ?string $country = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true, name: 'vat')]
    private ?string $vat = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true, name: 'brand')]
    private ?string $brand = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true, name: 'barcode')]
    private ?string $barcode = null;

    #[ORM\Column(type: 'boolean', name: 'is_archived')]
    private bool $isArchived = false;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 3, nullable: true, name: 'weight')]
    private ?string $weight = null;

    #[ORM\Column(type: 'boolean', name: 'is_weight_product')]
    private bool $isWeightProduct = false;

    #[ORM\Column(type: 'boolean', name: 'is_marked')]
    private bool $isMarked = false;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 3, nullable: true, name: 'volume')]
    private ?string $volume = null;

    #[ORM\Column(type: 'boolean', name: 'has_excise_mark')]
    private bool $hasExciseMark = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true, name: 'category')]
    private ?string $category = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true, name: 'seo_title')]
    private ?string $seoTitle = null;

    #[ORM\Column(type: 'text', nullable: true, name: 'seo_h1')]
    private ?string $seoH1 = null;

    #[ORM\Column(type: 'text', nullable: true, name: 'seo_description')]
    private ?string $seoDescription = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'package_quantity')]
    private ?int $packageQuantity = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true, name: 'package_url')]
    private ?string $packageUrl = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true, name: 'packaging_url')]
    private ?string $packagingUrl = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'product_weight')]
    private ?int $productWeight = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'width')]
    private ?int $width = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'height')]
    private ?int $height = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'length')]
    private ?int $length = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'package_weight')]
    private ?int $packageWeight = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'package_width')]
    private ?int $packageWidth = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'package_height')]
    private ?int $packageHeight = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'package_length')]
    private ?int $packageLength = null;

    #[ORM\OneToMany(targetEntity: ProductAttribute::class, mappedBy: 'product', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['key' => 'ASC'])]
    private Collection $attributes;

    #[ORM\OneToMany(targetEntity: ProductImage::class, mappedBy: 'product', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $images;

    #[ORM\Column(type: 'datetime_immutable', name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', name: 'updated_at')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->attributes = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getExternalCode(): string
    {
        return $this->externalCode;
    }

    public function setExternalCode(string $externalCode): self
    {
        $this->externalCode = $externalCode;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getDiscount(): ?string
    {
        return $this->discount;
    }

    public function setDiscount(?string $discount): self
    {
        $this->discount = $discount;
        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): self
    {
        $this->sku = $sku;
        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): self
    {
        $this->unit = $unit;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function getVat(): ?string
    {
        return $this->vat;
    }

    public function setVat(?string $vat): self
    {
        $this->vat = $vat;
        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function setBarcode(?string $barcode): self
    {
        $this->barcode = $barcode;
        return $this;
    }

    public function isArchived(): bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(bool $isArchived): self
    {
        $this->isArchived = $isArchived;
        return $this;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(int|string|null $weight): self
    {
        $this->weight = $weight !== null ? (string)$weight : null;
        return $this;
    }

    public function isWeightProduct(): bool
    {
        return $this->isWeightProduct;
    }

    public function setIsWeightProduct(bool $isWeightProduct): self
    {
        $this->isWeightProduct = $isWeightProduct;
        return $this;
    }

    public function isMarked(): bool
    {
        return $this->isMarked;
    }

    public function setIsMarked(bool $isMarked): self
    {
        $this->isMarked = $isMarked;
        return $this;
    }

    public function getVolume(): ?string
    {
        return $this->volume;
    }

    public function setVolume(int|string|null $volume): self
    {
        $this->volume = $volume !== null ? (string)$volume : null;
        return $this;
    }

    public function hasExciseMark(): bool
    {
        return $this->hasExciseMark;
    }

    public function setHasExciseMark(bool $hasExciseMark): self
    {
        $this->hasExciseMark = $hasExciseMark;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function setSeoTitle(?string $seoTitle): self
    {
        $this->seoTitle = $seoTitle;
        return $this;
    }

    public function getSeoH1(): ?string
    {
        return $this->seoH1;
    }

    public function setSeoH1(?string $seoH1): self
    {
        $this->seoH1 = $seoH1;
        return $this;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seoDescription;
    }

    public function setSeoDescription(?string $seoDescription): self
    {
        $this->seoDescription = $seoDescription;
        return $this;
    }

    public function getPackageQuantity(): ?int
    {
        return $this->packageQuantity;
    }

    public function setPackageQuantity(int|string|null $packageQuantity): self
    {
        $this->packageQuantity = $packageQuantity !== null ? (int)$packageQuantity : null;
        return $this;
    }

    public function getPackageUrl(): ?string
    {
        return $this->packageUrl;
    }

    public function setPackageUrl(?string $packageUrl): self
    {
        $this->packageUrl = $packageUrl;
        return $this;
    }

    public function getPackagingUrl(): ?string
    {
        return $this->packagingUrl;
    }

    public function setPackagingUrl(?string $packagingUrl): self
    {
        $this->packagingUrl = $packagingUrl;
        return $this;
    }

    public function getProductWeight(): ?int
    {
        return $this->productWeight;
    }

    public function setProductWeight(int|string|null $productWeight): self
    {
        $this->productWeight = $productWeight !== null ? (int)$productWeight : null;
        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int|string|null $width): self
    {
        $this->width = $width !== null ? (int)$width : null;
        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int|string|null $height): self
    {
        $this->height = $height !== null ? (int)$height : null;
        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(int|string|null $length): self
    {
        $this->length = $length !== null ? (int)$length : null;
        return $this;
    }

    public function getPackageWeight(): ?int
    {
        return $this->packageWeight;
    }

    public function setPackageWeight(int|string|null $packageWeight): self
    {
        $this->packageWeight = $packageWeight !== null ? (int)$packageWeight : null;
        return $this;
    }

    public function getPackageWidth(): ?int
    {
        return $this->packageWidth;
    }

    public function setPackageWidth(int|string|null $packageWidth): self
    {
        $this->packageWidth = $packageWidth !== null ? (int)$packageWidth : null;
        return $this;
    }

    public function getPackageHeight(): ?int
    {
        return $this->packageHeight;
    }

    public function setPackageHeight(int|string|null $packageHeight): self
    {
        $this->packageHeight = $packageHeight !== null ? (int)$packageHeight : null;
        return $this;
    }

    public function getPackageLength(): ?int
    {
        return $this->packageLength;
    }

    public function setPackageLength(int|string|null $packageLength): self
    {
        $this->packageLength = $packageLength !== null ? (int)$packageLength : null;
        return $this;
    }

    /**
     * @return Collection<int, ProductAttribute>
     */
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function addAttribute(ProductAttribute $attribute): self
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes->add($attribute);
            $attribute->setProduct($this);
        }
        return $this;
    }

    public function removeAttribute(ProductAttribute $attribute): self
    {
        if ($this->attributes->removeElement($attribute)) {
            if ($attribute->getProduct() === $this) {
                $attribute->setProduct(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, ProductImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ProductImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProduct($this);
        }
        return $this;
    }

    public function removeImage(ProductImage $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getProduct() === $this) {
                $image->setProduct(null);
            }
        }
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function toArray(): array
    {
        $attributes = [];
        foreach ($this->attributes as $attr) {
            $attributes[$attr->getKey()] = $attr->getValue();
        }

        $images = [];
        foreach ($this->images as $image) {
            $images[] = $image->toArray();
        }

        return [
            'id' => $this->id->toString(),
            'external_code' => $this->externalCode,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'discount' => $this->discount,
            'currency' => $this->currency,
            'sku' => $this->sku,
            'unit' => $this->unit,
            'country' => $this->country,
            'vat' => $this->vat,
            'brand' => $this->brand,
            'barcode' => $this->barcode,
            'is_archived' => $this->isArchived,
            'weight' => $this->weight,
            'is_weight_product' => $this->isWeightProduct,
            'is_marked' => $this->isMarked,
            'volume' => $this->volume,
            'has_excise_mark' => $this->hasExciseMark,
            'category' => $this->category,
            'seo_title' => $this->seoTitle,
            'seo_h1' => $this->seoH1,
            'seo_description' => $this->seoDescription,
            'package_quantity' => $this->packageQuantity,
            'package_url' => $this->packageUrl,
            'packaging_url' => $this->packagingUrl,
            'product_weight' => $this->productWeight,
            'width' => $this->width,
            'height' => $this->height,
            'length' => $this->length,
            'package_weight' => $this->packageWeight,
            'package_width' => $this->packageWidth,
            'package_height' => $this->packageHeight,
            'package_length' => $this->packageLength,
            'attributes' => $attributes,
            'images' => $images,
            'created_at' => $this->createdAt->format('c'),
            'updated_at' => $this->updatedAt->format('c'),
        ];
    }
}