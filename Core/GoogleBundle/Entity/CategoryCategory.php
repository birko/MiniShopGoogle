<?php

namespace Core\GoogleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * CategoryCategory
 *
 * @ORM\Table(name="google_category_category")
 * @ORM\Entity(repositoryClass="Core\GoogleBundle\Entity\CategoryCategoryRepository")
 */
class CategoryCategory
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
     * @ORM\ManyToOne(targetEntity="Core\CategoryBundle\Entity\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
     protected $category;


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
     * Set category
     *
     * @param string $category
     * @return CategoryCategory
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
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
}
