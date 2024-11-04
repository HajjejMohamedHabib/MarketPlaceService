<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Service;
use App\Form\OrderType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use DateTime;
use function Webmozart\Assert\Tests\StaticAnalysis\lower;

class ReservationController extends AbstractController
{
    #[Route('/reservation/{service_id}', name: 'add.reservation')]
    public function AddReservation($service_id,ManagerRegistry $doctrine,
                                   Request $request,Security $security,
    MailerInterface $mailer): Response
    {
        $order=new Order();
        $service=$doctrine->getRepository(Service::class)->find($service_id);
        $form=$this->createForm(OrderType::class,$order);
        $form->remove('createdAt');
        $form->remove('user');
        $form->remove('status');
        $form->remove('service');
        $form->remove('amount');
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $order->setUser($security->getUser());
            $order->setService($service);
            $order->setAmount($service->getPrice());
            $order->setStatus('pending');
            $available=false;
            $bookingDate = $order->getBookingDate();
            if ($bookingDate instanceof DateTime) {
                foreach ($service->getAvailability() as $day => $val) {
                    if ($val['unavailable']==false) {
                        $morningTime = DateTime::createFromFormat('H:i:s', $val['morning']);
                        $afternoonTime = DateTime::createFromFormat('H:i:s', $val['afternoon']);
                        if ($morningTime !== false && $afternoonTime !== false) {
                            if ($bookingDate->format('l') === ucfirst(strtolower($day)) &&
                                $bookingDate->format('H:i') >= $morningTime->format('H:i') &&
                                $bookingDate->format('H:i') < $afternoonTime->format('H:i')) {

                                $available = true;
                                break;
                            }
                        } else {
                            throw new \Exception("Invalid time format for $day: morning = {$val['morning']}, afternoon = {$val['afternoon']}");
                        }
                    }
                }
            }
            if(!$available){
                $form->get('booking_date')->addError(new FormError('This service is not available for the selected date.'));
            }else {
                $manager = $doctrine->getManager();
                $manager->persist($order);
                $manager->flush();
                $email = (new Email())
                    ->from('mohamedhabibhajjej@gmail.com')
                    ->to('mhdriabi2013@gmail.com')
                    ->subject('Test d\'envoi depuis le contrôleur')
                    ->text('Ceci est un test d\'envoi d\'email depuis un contrôleur Symfony.');
                    $mailer->send($email);
            }
        }
        return $this->render('reservation/index.html.twig', [
            'form'=>$form->createView(),
             'service'=>$service,
        ]);
    }
}
