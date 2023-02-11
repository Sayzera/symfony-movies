<?php

namespace App\Utils\AbstractClasses;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class CategoryTreeAbstract
{
    public  $categoriesArrayFromDb;
    protected static $dbconnection;
    public $entityManager;
    public $urlGenerator;

    public $categorylist;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->categoriesArrayFromDb = $this->getCategories();
    }

    abstract public function getCategoriesList(array $categories_array);


    public function buildTree(int $parent_id = null): array
    {
        $subcategory = [];

        // Kategorileri al 
        foreach ($this->categoriesArrayFromDb as $category) {

            // Alt kategorisi var ise 
            if ($category['parent_id'] == $parent_id) {
                // alt kategorileri topla
                $children = $this->buildTree($category['id']);

                // eğer varsa 
                if ($children) {
                    $category['children'] = $children;
                }
                $subcategory[] = $category;
            }
        };

        return $subcategory;
    }

    /**
     * @throws Exception
     */
    private function getCategories()
    {
        if (self::$dbconnection) {
            return self::$dbconnection;
        } else {
            $conn = $this->entityManager->getConnection();
            $sql = "SELECT * FROM categories";
            $stmt = $conn->prepare($sql);
            $resultSet =  $stmt->executeQuery();
            return self::$dbconnection = $resultSet->fetchAllAssociative();
        }
    }
}
