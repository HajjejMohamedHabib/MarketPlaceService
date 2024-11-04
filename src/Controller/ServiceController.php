<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\ServiceType;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Array_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ServiceController extends AbstractController
{
    #[Route('/service', name: 'service')]
    public function index(): Response
    {
        return $this->render('service/index.html.twig', [
            'controller_name' => 'ServiceController',
        ]);
    }

    #[Route('/service/add', name: 'add.service')]
    public function addService(Request                                                      $request,
                               ManagerRegistry                                              $doctrine,
                               Security                                                     $security,
                               SluggerInterface                                             $slugger,
                               #[Autowire
                               ('%kernel.project_dir%/public/uploads/servicePhoto')] string $servicePhotoDirectory

    ): Response
    {
        $service = new Service();
        $form = $this->createForm('App\Form\ServiceType', $service);
        $form->remove('createdAt');
        $form->remove('user');
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /** @var UploadedFile $photos */
            $manager = $doctrine->getManager();
            $service->setUser($security->getUser());
            //begin upload photos
            $photos = $form->get('photos')->getData();
            //dd($photos);
            $newFilesName = [];
            if ($photos) {
                $i = 0;
                foreach ($photos as $photo) {
                    $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $photo->guessExtension();
                    try {
                        $photo->move($servicePhotoDirectory, $newFilename);
                        $newFilesName[$i] = $newFilename;
                        $i++;
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }
                }
                $service->setImage($newFilesName);
            }

            //end upload photos
            $manager->persist($service);
            $manager->flush();
        }

        return $this->render('service/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/service/display', name: 'display.service')]
    public function displayService(ManagerRegistry $doctrine): Response
    {
        $services = $doctrine->getRepository(Service::class)
            ->createQueryBuilder('s')
            ->leftJoin('s.user', 'u') // Joindre la table User
            ->addSelect('u') // Sélectionner aussi l'utilisateur
            ->getQuery()
            ->getResult();
        return $this->render('service/service_list.html.twig', [
            'services' => $services

        ]);
    }

    #[Route('/service/update/{id}', name: 'update.service')]
    public function updateService(Request $request, Service $service=null,
                                  ManagerRegistry $doctrine,
                                  Security $security,
                                  SluggerInterface $slugger,
                                  #[Autowire
                                  ('%kernel.project_dir%/public/uploads/servicePhoto')] string $servicePhotoDirectory):
    Response
    {
        $form = $this->createForm(ServiceType::class, $service);
        $form->remove('createdAt');
        $photoss=$service->getImage();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /** @var UploadedFile $photos */
            //dd($photoss);
            $manager = $doctrine->getManager();
            $service->setUser($security->getUser());

            //begin upload photos
            $photos = $form->get('photos')->getData();
            //dd($photos);
            $newFilesName = [];
            foreach ($photoss as $photo) {
                array_push($newFilesName, $photo);
            }
            if ($photos) {
                foreach ($photos as $photo) {
                    $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $photo->guessExtension();
                    try {
                        $photo->move($servicePhotoDirectory, $newFilename);
                        array_push($newFilesName, $newFilename);

                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }
                }
                $service->setImage($newFilesName);
            }

            //end upload photos
            $manager->persist($service);
            $manager->flush();

        }
        return $this->render('service/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/service/dashboard/display/{page?1}', name: 'dashboard.user.display.service')]
    public function dashboardDisplayServiceByUser(ManagerRegistry $doctrine,Security $security,$page): Response{
        $nbreElementPerPage=2;
        $nbServices=$doctrine->getRepository(Service::class)->count();
        $nbrePages=ceil($nbServices/$nbreElementPerPage);
        $user=$security->getUser();
        $services=$doctrine->getRepository(Service::class)
            ->findBy(['user'=>$user],['id' => 'ASC'],limit: $nbreElementPerPage,offset:($nbreElementPerPage*($page-1)));
        return $this->render('service/serviceDashboard.html.twig', [
            'services' => $services,
            'nbreElementPerPage'=>$nbreElementPerPage,
            'nbrePages'=>$nbrePages,
            'page'=>$page,
        ]);
    }
    #[Route('/service/dashboard/display', name: 'dashboard.display.service')]
public function dashboardDisplayService(ManagerRegistry $doctrine): Response{
        $services = $doctrine->getRepository(Service::class)->findAll();
        return $this->render('service/serviceDashboard.html.twig', [
            'services' => $services
        ]);
    }
    #[Route('/service/delete/{id}', name: 'delete.service')]
public function deleteService(Service $service, ManagerRegistry $doctrine){
        $manager = $doctrine->getManager();
        $manager->remove($service);
        $manager->flush();
       return $this->redirectToRoute('dashboard.user.display.service',['page'=>1]);
    }
#[Route('/service/details/{id}', name: 'details.service')]
public function detailsService(ManagerRegistry $doctrine,$id):Response
{
    $service=$doctrine->getRepository(Service::class)->find($id);
    $images=$service->getImage();
    unset($images[0]);
    return $this->render('service/details.html.twig', [
        'service'=>$service,
        'images'=>$images
    ]);
}

}
