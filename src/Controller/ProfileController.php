<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\Service;
use App\Entity\User;
use App\Form\OrderType;
use Doctrine\Persistence\ManagerRegistry;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProfileController extends AbstractController
{
    private $gateway;
    public function __construct()
    {
        $this->gateway=new StripeClient($_ENV['STRIPE_SECRETKEY']);
    }

    #[Route('/profile', name: 'app_profile')]
    public function index(Security $security, ManagerRegistry $doctrine): Response
    {
        $userConnected = $security->getUser();
        $user = $doctrine->getRepository(User::class)->find($userConnected->getId());
        $services = $doctrine->getRepository(Service::class)->findBy(['user' => $user]);
        $ordersClient=$doctrine->getRepository(Order::class)->findBy(['user' => $user]);
        $ordersPaidPerUser=$doctrine->getRepository(Payment::class)
            ->createQueryBuilder('p')
            ->select('p','o','u')
            ->join('p.orderr', 'o')
            ->join('o.user', 'u')
            ->where('o.user = :user')
            ->setParameter('user', $user)
            ->getQuery()->getResult();
        //dd($ordersPaidPerUser);
        $ordersDetails = $doctrine->getRepository(Order::class)
            ->createQueryBuilder('o')
            ->select('o', 's', 'u')
            ->join('o.service', 's')
            ->join('o.user', 'u')
            ->where('s.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
        $forms = [];
        foreach ($ordersDetails as $order) {
            $form = $this->createForm(OrderType::class, $order, [
                'action' => $this->generateUrl('approve.order', ['id' => $order->getId()]),
                'method' => 'POST'
            ]);
            $form->remove('status');
            $form->remove('booking_date');
            $form->remove('paymentMethod');
            $form->remove('amount');
            $form->remove('user');
            $form->remove('service');
            $form->remove('createdAt');
            $forms[$order->getId()] = $form->createView();
        }

        return $this->render('profile/index.html.twig', [
            'forms' => $forms,
            'user' => $user,
            'services' => $services,
            'ordersDetails' => $ordersDetails,
            'ordersClient' => $ordersClient,
            'ordersPaidPerUser' => $ordersPaidPerUser,
        ]);
    }

    #[Route('/profile/approve/{id}', name: 'approve.order')]
    public function approveOrder(Request $request,$id, ManagerRegistry $doctrine): Response
    {
        if (!$id) {
            throw $this->createNotFoundException("No order found for id ".$id);
        }
       $order=$doctrine->getRepository(Order::class)->find($id);
        // Créez le formulaire
        $form = $this->createForm(OrderType::class, $order, [
            'method' => 'POST'
        ]);

        // Supprimez les champs inutiles
        $form->remove('status');
        $form->remove('booking_date');
        $form->remove('paymentMethod');
        $form->remove('amount');
        $form->remove('user');
        $form->remove('service');
        $form->remove('createdAt');

        // Gestion de la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $order->setStatus("approved"); // Mettez à jour le statut
            $manager = $doctrine->getManager();
            $manager->persist($order);
            $manager->flush(); // Persistez et enregistrez les changements

            $this->addFlash('success', 'Order approved successfully!'); // Message de succès
            return $this->redirectToRoute('app_profile'); // Redirigez vers le profil
        } else {
            // Debug : Affichez les erreurs si le formulaire n'est pas valide
            if (!$form->isValid()) {
                $errors = (string) $form->getErrors(true, false);
                $this->addFlash('error', 'Form errors: ' . $errors);
            }
        }

        // Si le formulaire n'est pas valide, redirigez vers le profil
        return $this->redirectToRoute('app_profile');
    }
    #[Route('/checkout/{id}', name: 'checkout.order')]
public function checkout(Request $request,$id, ManagerRegistry $doctrine): Response{
        $order=$doctrine->getRepository(Order::class)->find($id);
        $checkout=$this->gateway->checkout->sessions->create([
            'line_items' => [[
                'price_data'=>[
                    'currency'=>$_ENV['STRIPE_CURRENCY'],
                    'product_data'=>[
                        'name'=>$order->getService()->getTitle(),
                ],
                    'unit_amount'=>$order->getAmount()*100,
            ],
            'quantity'=>1,
            ]],
            'mode'=>'payment',
            'success_url'=>$this->generateUrl('payment.add', ['id'=>$id],UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url'=>$this->generateUrl('app_profile',[],UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
     return $this->redirect($checkout->url);
    }
    #[Route('/payment/{id}', name: 'payment.add')]
public function addPayment($id, ManagerRegistry $doctrine,Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Utilisateur non connecté');
        }
        $order=$doctrine->getRepository(Order::class)->find($id);
        $payment =new Payment();
        $payment->setPaymentStatus('paid');
        $payment->setAmount($order->getAmount());
        $payment->setOrderr($order);
        $payment->setPaymentMethod($order->getPaymentMethod());
        $manager = $doctrine->getManager();
        $manager->persist($payment);
        $manager->flush();
        return $this->redirectToRoute('app_profile');
    }
}
