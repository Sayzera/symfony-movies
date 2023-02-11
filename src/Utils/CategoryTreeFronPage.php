<?php

namespace App\Utils;

use App\Twig\AppExtension;
use App\Utils\AbstractClasses\CategoryTreeAbstract;

class CategoryTreeFronPage extends CategoryTreeAbstract
{
    public $slugger;

    public function getCategoriesList(array $categories_array)
    {

        $this->categorylist .= '<ul>';

        foreach ($categories_array as $category) {
            $catName = $this->slugger->slugify($category['name']);
            $url = $this->urlGenerator->generate('video_list', [
                'id' => $category['id'],
                'categoryname' => $catName,
            ]);

            $this->categorylist .= " <li><a href=\"${url}\">${catName}</a>";


            if (!empty($category['children'])) {
                $this->getCategoriesList($category['children']);
            }

            $this->categorylist .= '</li>';
        }

        $this->categorylist .= '</ul>';


        return $this->categorylist;
    }

    public function getMainParent(int $id): array
    {
        /**
         * array_column() dizi içerisindeki belirtilen sütünün değerlerini toplar ve yeni bir dizi oluşturur.
         */
        $key = array_search($id, array_column($this->categoriesArrayFromDb, 'id'));

        if ($this->categoriesArrayFromDb[$key]['parent_id'] != null) {
            return $this->getMainParent($this->categoriesArrayFromDb[$key]['parent_id']);
        } else {
            return [
                'id' => $this->categoriesArrayFromDb[$key]['id'],
                'name' => $this->categoriesArrayFromDb[$key]['name'],
            ];
        }
    }

    public function getCategoryListAndParent(int $id): string
    {
        $this->slugger =  new AppExtension(); // Twig extension to slugify url's for categories

        $parentData = $this->getMainParent($id);

        $this->getMainParentName = $parentData['name'];
        $this->mainParentId = $parentData['id'];

        $key = array_search($id, array_column($this->categoriesArrayFromDb, 'id'));

        $this->currentCategoryName = $this->categoriesArrayFromDb[$key]['name'];
        $categories_array = $this->buildTree($parentData['id']);


        return $this->getCategoriesList($categories_array);
    }


    public function getChildIds(int $parent): array
    {
        static $ids = [];

        // Butun categorileri çek 
        foreach ($this->categoriesArrayFromDb as $category) {
            if ($category['parent_id'] == $parent) { // Gelen  kategorinin alt kategorisi varsa 
                $ids[] = $category['id']; // ids arrayine ekle

                $this->getChildIds($category['id']); // alt kategorileri de ids arrayine ekle
            }
        }

        return $ids;
    }
}
