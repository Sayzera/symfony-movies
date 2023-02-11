<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
         $this->loadMainCategories($manager);
         $this->loadSubcategories($manager, 'Electronics','Electronics');
         $this->loadSubcategories($manager, 'Computers','Computers');
         $this->loadSubcategories($manager, 'Laptops','Laptops');
         $this->loadSubcategories($manager, 'Books','Books');
         $this->loadSubcategories($manager, 'Movies','Movies');
    }

    private  function  loadMainCategories(ObjectManager $manager): void
    {
        foreach ($this->getMainCategoriesData() as [$name, $id]) {
            $category = new Category();
            $category->setName($name);
            $manager->persist($category);
        }

        $manager->flush();
    }


    private  function loadSubcategories(ObjectManager $manager, $category, $parent_name): void
    {
        $parent = $manager->getRepository(Category::class)->findOneBy(['name' => $parent_name]);

        $method_name ="get{$category}Data";

        foreach ($this->$method_name() as [$name]) {
            $category = new Category();
            $category->setName($name);
            $category->setParent($parent);
            $manager->persist($category);
        }

        $manager->flush();
    }

    private function  getMainCategoriesData() {
            return [
                [ 'Electronics',  1 ],
                [ 'Toys', 2 ],
                ['Books',   3],
                ['Movies',  4 ]
            ];
    }

    private function getElectronicsData() {
        return [
            [ 'Cameras',  5 ],
            [ 'Computers', 6 ],
            ['Cell Phones',   7],
        ];
    }

    private function getComputersData() {
        return [
            [ 'Laptops',  8 ],
            [ 'Desktops', 9 ],
        ];
    }

    private function getLaptopsData() {

        return [
            [ 'Apple',  10 ],
            [ 'Asus', 11 ],
            ['Dell',   12],
            ['HP',  13 ],
            ['Lenovo', 14 ]
            ];
    }
    private function getBooksData() {
            return [
                ['Clildren\'s Books', 15],
                ['Kindle eBooks', 16]
            ];
    }

    private function getMoviesData() {
        return [
            ['Family', 17],
            ['Romance', 18]
        ];
    }



}
