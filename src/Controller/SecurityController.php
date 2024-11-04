<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{


    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login2.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
       // throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    #[Route(path:'/forgetpassword',name:'forgetpassword')]
    public function forgetPassword(Request $request,ManagerRegistry $doctrine, MailerInterface $mailer):Response{
        $error="";
        if($request->isMethod('POST'))
        {
            $user=$doctrine->getRepository(User::class)->findBy(['email'=>$request->request->get('email')]);
            if($user){
                $token=bin2hex(random_bytes(16));
                $link="http://127.0.0.1:8000/newpassword?token=".$token;
             $email=(new Email())
                 ->from('mohamedhabibhajjej@gmail.com')
                 ->to($request->request->get('email'))
                 ->subject('test-forgetpassword')
                 ->html("<a href=$link>re-enter password</a>");
             $mailer->send($email);
             $request->getSession()->set('token',$token);
             $request->getSession()->set('email',$request->request->get('email'));
            }
            else{
                $error='Address Not Found';
            }
        }
    return $this->render('security/forgetPassword.html.twig',[
        'error'=>$error
    ]);
    }
    #[Route(path:'/newpassword',name:'newpassword')]
   public function newPassword(Request $request,ManagerRegistry $doctrine):Response{
        $tokUrl=$request->query->get('token');
        $tokSession=$request->getSession()->get('token');
        $emailSession=$request->getSession()->get('email');
        $error="";
        if($tokUrl==$tokSession){
           if($request->isMethod('POST')){
               $password=$request->request->get('password');
               $confpassword=$request->request->get('confpassword');
               dd($password."--".$confpassword);
               if($password==$confpassword){
                   $manager=$doctrine->getManager();
                   $users=$doctrine->getRepository(User::class)->findBy(['email'=>$emailSession]);
                   $user=$users[0];
                   $user->setPassword($request->request->get('password'));
                   $manager->persist($user);
                   $manager->flush();
                   $request->getSession()->remove('token');
                   $this->redirectToRoute('app_login');
               }else
                   $error="passwords are not identic";
           }
        }
        else
        {
        return $this->render('security/404.html.twig');
        }
        return $this->render('security/newpassword.html.twig',[
            'error'=>$error
        ]);
    }
}
