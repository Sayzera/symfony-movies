<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Video;
use App\Form\UserType;
use App\Repository\VideoRepository;
use App\Utils\CategoryTreeFronPage;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    /**
     * @Route("/front", name="main_page")
     */
    public function index(): Response
    {
        return $this->render('front/index.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }

    /**
     * @Route("/video-list/category/{categoryname},{id}/{page}", defaults={"page": 1}, name="video_list")
     */
    public function videoList($id, $page, CategoryTreeFronPage $categories, Request $request, VideoRepository $repo)
    {

        $filter = ['id' => 'ASC'];
        // get query param
        $sort = $request->query->get('sort');
        if ($sort) {
            $filter = ['title' => $sort];
        }


        // Kategoriye bağlı olan bütün kategorileri
        $ids = $categories->getChildIds($id);
        array_push($ids, $id);


        $limit = 3;
        $videos = $this->getDoctrine()->getRepository(Video::class)
            ->findBy(
                ['category' => $ids],
                ['id' => 'DESC']
            );

        $pagesCount = ceil(count($videos) / $limit);


        $videos = $repo->getVideos($id, $page, $limit);
        dump($videos);
        // en çok beğenilen videolara göre sırala


        // SELECT * FROM video WHERE category_id = $id ORDER BY id DESC LIMIT $limit OFFSET ($page - 1) * $limit
        // 1. sayfa için limit 10, offset 0
        // 2. sayfa için limit 10, offset 10
        // 3. sayfa için limit 10, offset 20
        // 4. sayfa için limit 10, offset 30

        list($min, $max) = $this->getPageRange($page, $pagesCount);

        // range 5-7 => 5 ve 7 dahil 5 6 7    dd(range($min, $max));

        /**
         * classın methodları çalışır durumda ve çıktları categories değişkenine aktarır
         */
        $categories->getCategoryListAndParent($id);


        return $this->render(
            'front/videolist.html.twig',
            [
                "subcategories" => $categories,
                "videos" => $videos,
                "pagesCount" => $pagesCount,
                'page' => $page,
                'range' => range($min, $max),
            ]
        );
    }


    public function getPageRange($current, $max, $total_pages = 3)
    {
        // total pages 7

        $desired_pages = $max < $total_pages ? $max : $total_pages; // sayfa aralıgını belirliyoruz örnek 1 2 3 şeklinde bir gösterim olacak veya sayfa sayısı kadar 
        $middle = ceil($desired_pages / 2);

        if ($current <= $middle) { // 
            return [1, $desired_pages]; // 1 den başla ve 3 e kadar git
        }

        if ($current > $middle && $current <= ($max - $middle)) {
            return [
                $current - $middle,
                $current + $middle
            ];
        }

        if ($current <= $max) {
            // son sayfa 7 ise bunu şu şekide yapıyor 7-5  => range ise bunu alıyor 5-6-7 olarak döndürüyor
            return [
                $current - ($desired_pages - 1), // 3 -1 => 2 
                $max
            ];
        }
    }

    /**
     * @Route("/video-details/{video}", name="video_detail")
     */
    public function videoDetail(VideoRepository $repo, $video)
    {
        $video = $repo->videoDetails($video);

        return $this->render('front/video_details.html.twig', [
            'video' => $video
        ]);
    }

    /**
     * @Route("/like-unlike/{video}", name="like_unlike", methods={"POST"})
     */
    public function like_unlike(Video $video, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $type = $request->request->get('type');
        $em = $this->getDoctrine()->getManager();

        if ($type == 'like') {
            // kullanıcı daha önceden beğenmiş mi
            if ($video->getUsersThatLike()->contains($this->getUser())) {

                return $this->json([
                    'status' => false,
                    'message' => 'Video zaten beğenilmiş',
                ]);
            } else {
                $video->addUsersThatLike($this->getUser());
                $video->removeUsersThatDontLike($this->getUser());

                $em->persist($video);
                $em->flush();

                return $this->json([
                    'status' => true,
                    'message' => 'Video beğenildi',
                    'like' => $video->getUsersThatLike()->count(),
                    'dislike' => $video->getUsersThatDontLike()->count(),
                    'id' => $video->getId()
                ]);
            }
        } else {
            $video->removeUsersThatLike($this->getUser());
            $video->addUsersThatDontLike($this->getUser());
            $em->persist($video);
            $em->flush();


            return $this->json([
                'status' => true,
                'message' => 'Video beğenisi kaldırıldı',
                'like' => $video->getUsersThatLike()->count(),
                'dislike' => $video->getUsersThatDontLike()->count(),
                'id' => $video->getId()
            ]);
        }
    }

    public function unlike($video)
    {

    }

    public function like($video)
    {

    }

    /**
     * @Route("/new-comment/{video}", methods={"POST"}, name="new_comment")
     */
    public function new_comment(Video $video, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!empty(trim($request->request->get('comment')))) {
            $comment = new Comment();
            $comment->setContent($request->request->get('comment'));
            $comment->setVideo($video);
            $comment->setUser($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
        }

        return $this->redirectToRoute('video_detail', ['video' => $video->getId()]);
    }


    /**
     * @Route("/search-results", methods={"POST"}, name="search_results")
     */
    public function searchResults()
    {
        return $this->render('front/search_results.html.twig');
    }

    /**
     * @Route("/pricing", name="pricing")
     */
    public function pricing()
    {
        return $this->render('front/pricing.html.twig');
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function app_register(Request $request, UserPasswordHasherInterface $passwordHasher)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashhedPassword = $passwordHasher->hashPassword($user, $form->get('password')->getData());
            $user->setPassword($hashhedPassword);
            $user->setRoles(['ROLE_USER']);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('main_page');
        }

        return $this->render('front/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout(): void
    {
        // controller can be blank: it will never be called!
        throw new Exception('Don\'t forget to activate logout in security.yaml');
    }


    public function mainCategories()
    {
        $categories = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findBy(['parent' => null], ['name' => 'ASC']);


        return $this->render('front/_main_categories.html.twig', [
            'categories' => $categories
        ]);
    }
}
