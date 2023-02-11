<?php

namespace App\Utils;

use App\Utils\AbstractClasses\CategoryTreeAbstract;

class CategoryTreeAdminOptionList extends CategoryTreeAbstract
{


    /**
     * @param array $categories_array
     * @return mixed
     */
    public function getCategoriesList(array $categories_array, int $repeat = 0)
    {
        dump($categories_array);
        foreach ($categories_array as $category) {
            $this->categorylist[] = [
                'name' => str_repeat('-', $repeat) . $category['name'],
                'id' => $category['id']
            ];

            if (!empty($category['children'])) {
                $repeat = $repeat + 2;
                $this->getCategoriesList($category['children'], $repeat);
                $repeat = $repeat - 2;
            }
        }

        return $this->categorylist;
    }
}
