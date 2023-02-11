<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Utils\CategoryTreeAdminList;
use App\Utils\CategoryTreeAdminOptionList;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */

class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin_main_page")
     */
    public function index(): Response
    {
        return $this->render('admin/my_profile.html.twig');
    }

    /**
     * @Route("/su/categories", name="categories", methods={"GET", "POST"})
     */
    public function categories(CategoryTreeAdminList $categories, Request $request): Response
    {

        $categories->getCategoriesList($categories->buildTree());

        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);

        if ($this->saveCategory($form, $request, $category)) {
            return $this->redirectToRoute('categories');
        }


        return $this->render(
            'admin/categories.html.twig',
            [
                'categories' => $categories->categorylist,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/videos", name="videos")
     */
    public function videos(): Response
    {
        return $this->render('admin/videos.html.twig');
    }


    /**
     * @Route("/su/upload-video", name="upload_video")
     */
    public function upload_video(): Response
    {
        return $this->render('admin/upload_video.html.twig');
    }

    /**
     * @Route("/users", name="users")
     */
    public function users(): Response
    {
        return $this->render('admin/users.html.twig');
    }

    /**
     * @Route("/su/edit-category/{id}", name="edit_category", methods={"GET", "POST"})
     */
    public function edit_category(Category $category, Request $request): Response
    {
        $form = $this->createForm(CategoryType::class, $category);


        if ($this->saveCategory($form, $request, $category)) {
            return $this->redirectToRoute('categories');
        }


        return $this->render('admin/edit_category.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/su/delete-category/{id}", name="delete_category")
     */
    public function delete_category(Category $category)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($category);
        $entityManager->flush();

        return $this->redirectToRoute('categories');
    }

    public function getAllCategories(CategoryTreeAdminOptionList $categories, $editedCategory = null)
    {

        $categories->getCategoriesList($categories->buildTree());

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render(
            'admin/_all_categories.html.twig',
            [
                'categories' => $categories,
                'editedCategory' => $editedCategory
            ]
        );
    }


    private function saveCategory($form, $request, $category)
    {
        $form->handleRequest($request);

        /**
         * daha önce eklenmediyse ekler, eklenmişse günceller
         */

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setName($request->request->get('category')['name']);
            $repository = $this->getDoctrine()->getRepository(Category::class);
            $parent = $repository->find($request->request->get('category')['parent']);
            $category->setParent($parent);


            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('categories');
        }
        return false;
    }
}
