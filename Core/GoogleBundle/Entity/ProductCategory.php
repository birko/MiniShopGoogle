<?php

namespace Core\GoogleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * ProductCategory
 *
 * @ORM\Table(name="google_product_category")
 * @ORM\Entity(repositoryClass="Core\GoogleBundle\Entity\ProductCategoryRepository")
 */
class ProductCategory
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="google_category", type="integer")
     */
    private $googleCategory;
    
    /**
     * @ORM\ManyToOne(targetEntity="Core\ProductBundle\Entity\Product", inversedBy="prices")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $product;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set googleCategory
     *
     * @param string $googleCategory
     * @return CategoryCategory
     */
    public function setGoogleCategory($googleCategory)
    {
        $this->googleCategory = $googleCategory;

        return $this;
    }

    /**
     * Get googleCategory
     *
     * @return int 
     */
    public function getGoogleCategory()
    {
        return $this->googleCategory;
    }
    
    /**
     * Set product
     *
     * @param Product $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * Get product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }
}
