<?php

namespace App\Utils;

use App\Utils\AbstractClasses\CategoryTreeAbstract;


class CategoryTreeAdminList extends CategoryTreeAbstract
{
    public $categorylist;
    public $slugger;

    /**
     * @param array $categories_array
     * @return mixed
     */
    public function getCategoriesList(array $categories_array)
    {
        $this->categorylist .= '<ul >';

        foreach ($categories_array as $category) {
            $url_edit = $this->urlGenerator->generate('edit_category', [
                'id' => $category['id'],
            ]);
            $url_delete = $this->urlGenerator->generate('delete_category', [
                'id' => $category['id'],
            ]);

            $this->categorylist .= "<li>
            {$category['name']}
            <a href=\"${url_edit}\">Edit</a>
            <a onclick='return confirm(\"Are you sure?\");' href=\"${url_delete}\">Delete</a>
            ";

            if (!empty($category['children'])) {
                $this->getCategoriesList($category['children']);
            }

            $this->categorylist .= '</li>';
        }

        $this->categorylist .= '</ul>';

        return $this->categorylist;
    }
}
