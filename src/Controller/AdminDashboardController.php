<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminDashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function index(): Response
    {
        return $this->render('admin_dashboard/index.html.twig', [
            'controller_name' => 'AdminDashboardController',
        ]);
    }
    #[Route('/admin/category/add', name: 'app_admin_category.add')]
   public function addCategory(Request $request, ManagerRegistry $doctrine): Response
    {
        $category=new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $manager = $doctrine->getManager();
            $manager->persist($category);
            $manager->flush();
        }

        return $this->render('admin_dashboard/add.html.twig',
                [
                    'form' => $form->createView(),
                ]);
    }
    #[Route('/admin/category/display', name: 'app_admin_category.display')]
public function displayCategory(ManagerRegistry $doctrine): Response{
        $categories=$doctrine->getRepository(Category::class)->findAll();
        return $this->render('admin_dashboard/displayCat.html.twig', [
            'categories' => $categories,
                    ]);
}
}
