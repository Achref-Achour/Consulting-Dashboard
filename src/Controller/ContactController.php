<?php

namespace App\Controller;

use App\Entity\Admin;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Temoignage;
use App\Entity\Newsletter;
use App\Entity\Contact;
use App\Entity\RendezVous;
use App\Entity\Formation;
use App\Entity\Seo;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Session\Session;

/* /**
  * Require ROLE_ADMIN for *every* controller method in this class.
  *
  * @IsGranted("ROLE_ADMIN")
  */

class ContactController extends AbstractController
{

    /**
     * @Route("/home",name="dashboard")
     */
    public function dashboard()
    { /** @var \App\Entity\User $user */
        $user = $this->getUser();

        return $this->render('layout/Home.html.twig', [
            'user' => $user
        ]);
    }

    // /**
    //  * @Route("/home", name="dashboard")
    //  */
    // public function dashboard()
    // { 
    //     return $this->render('layout/Home.html.twig');
    // }




    /**
     * @Route("/logout",name="logout")
     */
    public function logout()
    {
        return $this->redirectToRoute();
    }
    

    /**
     * @Route("/", name="red")
     */
    public function red()
    {
        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $utils)
    {
        /**
         * Require ROLE_ADMIN for only this controller method.
         *
         * @IsGranted("ROLE_USER")
         */
        $error = $utils->getLastAuthenticationError();
        $lastUserName = $utils->getLastUsername();
        return $this->render('layout/login.html.twig', [
            'controller_name' => 'ContactController',
            'error' => $error,
            'lastUserName' => $lastUserName,
        ]);
    }

    /**
     * @Route("/Change-Password", name="ChangePassword")
     */
    public function ChangePassword()
    {
        return $this->render('layout/PasswordRecovry.html.twig', [
            'controller_name' => 'ContactController',
        ]);
    }

















//-----------------------Témoignage-----------------------------//

    /**
     * @Route("/temoignage", name="temoignage")
     */
    public function Temoignage()
    {
        $rep = $this->getDoctrine()->getRepository(Temoignage::class);
        $temoignage = $rep->findAll();
        $user = $this->getUser();
        if ($temoignage != null) {
            return $this->render('layout/Temoignage.html.twig', [
                'temoignages' => $temoignage,'user' => $user
            ]);
        } else {
            return $this->render('layout/Temoignage.html.twig', [
                'temoignages' => "",'user' => $user
            ]);
        }
    }

    /**
     * @Route("/modifier-temoignage/{id}", name="GetTemoignage")
     */
    public function GetTemoignage($id)
    {
        $rep = $this->getDoctrine()->getRepository(Temoignage::class);
        $temoignage = $rep->find($id);
        $user = $this->getUser();

        if ($temoignage != null) {
            return $this->render('layout/UpdateTemoignage.html.twig', [
                'temoignage' => $temoignage,'user' => $user
            ]);

        } else {
            // throw $this->createNotFoundException("Articles non trouvés");
        }
        return $this->redirectToRoute('temoignage');
    }


    /**
     * @Route("/update-temoignage/{id}", name="UpdateTemoignage")
     */
    public function UpdateTemoignage($id, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $temoignage = $entityManager->getRepository(Temoignage::class)->find($id);


        if (($request->isMethod('POST')) && ($temoignage != null)) {
            $file = $request->files->get('img');

            if ($file == null) {
                $temoignage->setNom($request->request->get('nom'))
                    ->setTitre($request->request->get('titre'))
                    ->setMessage($request->request->get('message'));
            } else {
                // Generate a unique name for the file before saving it
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();

                $uploadDir = $this->getParameter('uploads_directory');
                move_uploaded_file($file, "$uploadDir/$fileName");

                $temoignage->setNom($request->request->get('nom'))
                    ->setTitre($request->request->get('titre'))
                    ->setMessage($request->request->get('message'))
                    ->setImg($fileName);
            }
            $entityManager->flush();
            $this->addFlash('successUpd', 'Témoignage Modifier avec succés');
            return $this->redirectToRoute('temoignage');

        } else {
            // throw $this->createNotFoundException("Articles non trouvés");
        }
        return $this->redirectToRoute('temoignage');
    }


    /**
     * @Route("/delete-temoignage/{id}", name="DeleteTemoignage")
     */
    public function DeleteTemoignage($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $rep = $this->getDoctrine()->getRepository(Temoignage::class);
        $temoignage = $rep->find($id);


        if ($temoignage != null) {
            $entityManager->remove($temoignage);
            $entityManager->flush();
            $this->addFlash('successDel', 'Témoignage supprimée avec succés');
            return $this->redirectToRoute('temoignage');

        } else {
            // throw $this->createNotFoundException("Articles non trouvés");
        }
        return $this->redirectToRoute('temoignage');
    }


    /**
     * @Route("/ajouter-temoignage", name="TemForm")
     */
    public function TemForm(Request $request)
    { $user = $this->getUser();
        if ($request->isMethod('POST')) {

            $file = $request->files->get('img');

            // Generate a unique name for the file before saving it
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            $uploadDir = $this->getParameter('uploads_directory');
            move_uploaded_file($file, "$uploadDir/$fileName");


            $entityManager = $this->getDoctrine()->getManager();
            $temoignage = new temoignage();
            $temoignage->setNom($request->request->get('nom'))
                ->setTitre($request->request->get('titre'))
                ->setMessage($request->request->get('message'))
                ->setImg($fileName)
                ->setDate(new \DateTime());


            // tell Doctrine you want to (eventually) save the Product (no queries yet)
            $entityManager->persist($temoignage);
            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();


            $this->addFlash('success', 'Votre Témoignage a  été ajouté avec succés');
        }


        return $this->render('layout/AddTemoignage.html.twig', [
            'temoignage' => "",'user' => $user
        ]);
    

    }

//-----------------------Témoignage-----------------------------//    


//-----------------------Newsletter-----------------------------//
    /**
     * @Route("/newsletter", name="newsletter")
     */
    public function Newsletter()
    {
        $rep = $this->getDoctrine()->getRepository(Newsletter::class);
        $newsletter = $rep->findAll();
        $user = $this->getUser();
        if ($newsletter != null) {
            return $this->render('layout/Newsletter.html.twig', [
                'newsletters' => $newsletter
            ]);

        } else {
            return $this->render('layout/Newsletter.html.twig', [
                'newsletters' => [],'user' => $user
            ]);        
        }
    }

    /**
     * @Route("/delete-Newsletter/{id}", name="DeleteNewsletter")
     */
    public function DeleteNewsletter($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $rep = $this->getDoctrine()->getRepository(Newsletter::class);
        $newsletter = $rep->find($id);


        if ($newsletter != null) {
            $entityManager->remove($newsletter);
            $entityManager->flush();
            $this->addFlash('successNew', 'E-mail supprimée avec succés');
            return $this->redirectToRoute('newsletter');

        } else {
            return $this->render('layout/Newsletter.html.twig', [
                'newsletters' => [],'user' => $user
            ]);   
        }
    }
//-----------------------Newsletter-----------------------------//  


//-----------------------Contact-----------------------------//

    /**
     * @Route("/contact", name="contact")
     */
    public function index()
    {
        $rep = $this->getDoctrine()->getRepository(Contact::class);
        $contact = $rep->findAll();
        $user = $this->getUser();
        if ($contact != null) {
            return $this->render('layout/contact.html.twig', [
                'contacts' => $contact,'user' => $user
            ]);

        } else {
            return $this->render('layout/contact.html.twig', [
                'contacts' => [],'user' => $user
            ]);

        }
    }


    /**
     * @Route("/delete-Message/{id}", name="DeleteMessage")
     */
    public function DeleteMessage($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $rep = $this->getDoctrine()->getRepository(Contact::class);
        $contact = $rep->find($id);
        $user = $this->getUser();

        if ($contact != null) {
            $entityManager->remove($contact);
            $entityManager->flush();
            $this->addFlash('successMessage', 'Message supprimée avec succés');
            return $this->redirectToRoute('contact');

        } else {
            return $this->render('layout/contact.html.twig', [
                'contacts' => [],'user' => $user
            ]);        }
    }

//-----------------------Contact-----------------------------// 







//-----------------------Formation-----------------------------//
    /**
     * @Route("/formation", name="Formation")
     */
    public function Formation()
    {
        $rep = $this->getDoctrine()->getRepository(Formation::class);
        $formation = $rep->findAll();
        $user = $this->getUser();
        if ($formation != null) {
            return $this->render('layout/Formation.html.twig', [
                'formations' => $formation,'user' => $user
            ]);

        } else {
            return $this->render('layout/Formation.html.twig', [
                'formations' => [],'user' => $user
            ]);
        }
        

    }

    /**
     * @Route("/ajouter-formation", name="AddFormation")
     */
    public function AddFormation(Request $request)
    {
        $user = $this->getUser();
        if ($request->isMethod('POST')) {

            $file = $request->files->get('img');

            // Generate a unique name for the file before saving it
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            $uploadDir = $this->getParameter('uploads_directory');
            move_uploaded_file($file, "$uploadDir/$fileName");


            $entityManager = $this->getDoctrine()->getManager();
            $formation = new formation();
            $formation->setTitre($request->request->get('titre'))
                ->setFormation($request->request->get('formation'))
                ->setIntervenants($request->request->get('intervenants'))
                ->setAdresse($request->request->get('adresse'))
                ->setDate(\DateTime::createFromFormat('Y-m-d', $request->request->get('date')))
                ->setImg($fileName);


            // tell Doctrine you want to (eventually) save the Product (no queries yet)
            $entityManager->persist($formation);
            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();


            $this->addFlash('success', 'Votre Formation a été ajouté avec succés');
        }


        return $this->render('layout/AddFormation.html.twig', [
            'formations' => [],'user' => $user
        ]);

    }
    //  /**
    //  * @Route("/modifier-formation", name="UpdateFormation")
    //  */
    // public function UpdateFormation()
    // {
    //     return $this->render('layout/UpdateFormation.html.twig', [
    //         'controller_name' => 'ContactController',
    //     ]);
    // }

    /**
     * @Route("/delete-formation/{id}", name="DeleteFormation")
     */
    public function DeleteFormation($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $rep = $this->getDoctrine()->getRepository(Formation::class);
        $formation = $rep->find($id);
        $user = $this->getUser();

        if ($formation != null) {
            $entityManager->remove($formation);
            $entityManager->flush();
            $this->addFlash('successMessage', 'formation supprimée avec succés');
            return $this->redirectToRoute('Formation');

        } else {
            return $this->render('layout/Formation.html.twig', [
                'formations' => [],'user' => $user
            ]);
        }
    }


    /**
     * @Route("/Update-formation/{id}", name="UpdateFormation")
     */
    public function UpdateFormation($id, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $formation = $entityManager->getRepository(Formation::class)->find($id);


        if (($request->isMethod('POST')) && ($formation != null)) {
            $file = $request->files->get('img');

            if ($file == null) {
                $formation->setTitre($request->request->get('titre'))
                    ->setFormation($request->request->get('formation'))
                    ->setIntervenants($request->request->get('intervenants'))
                    ->setAdresse($request->request->get('adresse'));
            } else {
                // Generate a unique name for the file before saving it
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();

                $uploadDir = $this->getParameter('uploads_directory');
                move_uploaded_file($file, "$uploadDir/$fileName");

                $formation->setTitre($request->request->get('titre'))
                    ->setFormation($request->request->get('formation'))
                    ->setIntervenants($request->request->get('intervenants'))
                    ->setAdresse($request->request->get('adresse'))
                    ->setImg($fileName);
            }
            $entityManager->flush();
            $this->addFlash('successUpd', 'Formation Modifier avec succés');
            return $this->redirectToRoute('Formation');

        } else {
            return $this->render('layout/Formation.html.twig', [
                'formations' => [],'user' => $user
            ]);        }
    }


    /**
     * @Route("/modifier-formation/{id}", name="GetFormation")
     */
    public function GetFormation($id)
    {
        $rep = $this->getDoctrine()->getRepository(Formation::class);
        $formation = $rep->find($id);
        $user = $this->getUser();


        if ($formation != null) {
            return $this->render('layout/UpdateFormation.html.twig', [
                'formation' => $formation,'user' => $user
            ]);

        } else {
            return $this->render('layout/Formation.html.twig', [
                'formations' => [],'user' => $user
            ]);        }
    }
//-----------------------Formation-----------------------------//  


//-----------------------RDV-----------------------------//

    /**
     * @Route("/rendez-vous", name="rdv")
     */
    public function rdv()
    {
        $rep = $this->getDoctrine()->getRepository(RendezVous::class);
        $rdv = $rep->findAll();
        $user = $this->getUser();

        if ($rdv != null) {
            return $this->render('layout/rdv.html.twig', [
                'rdvs' => $rdv
            ]);

        } else {
            return $this->render('layout/rdv.html.twig', [
                'rdvs' => [],'user' => $user
            ]);       
        }
        
    }


    /**
     * @Route("/delete-Rdv/{id}", name="DeleteRdv")
     */
    public function DeleteRdv($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $rep = $this->getDoctrine()->getRepository(RendezVous::class);
        $rdv = $rep->find($id);


        if ($rdv != null) {
            $entityManager->remove($rdv);
            $entityManager->flush();
            $this->addFlash('successMessage', 'Rdv supprimée avec succés');
            return $this->redirectToRoute('rdv');

        } else {
            return $this->render('layout/rdv.html.twig', [
                'rdvs' => [],'user' => $user
            ]);         }
        
    }

//-----------------------RDV-----------------------------// 


//-----------------------Seo-----------------------------//
    /**
     * @Route("/seo", name="seo")
     */
    public function Seo()
    {
        $rep = $this->getDoctrine()->getRepository(Seo::class);
        $seo = $rep->findAll();
        $user = $this->getUser();

        if ($seo != null) {
            return $this->render('layout/Seo.html.twig', [
                'seos' => $seo,'user' => $user
            ]);

        } else {
            return $this->render('layout/Seo.html.twig', [
                'seos' => "",'user' => $user
            ]);        }
        

    }

    /**
     * @Route("/ajouter-page", name="SeoForm")
     */
    public function SeoForm(Request $request)
    {        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $entityManager = $this->getDoctrine()->getManager();
            $seo = new seo();
            $seo->setPage($request->request->get('page'))
                ->setTitle($request->request->get('title'))
                ->setKeywords($request->request->get('keywords'))
                ->setDescription($request->request->get('description'));


            // tell Doctrine you want to (eventually) save the Product (no queries yet)
            $entityManager->persist($seo);
            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();


            $this->addFlash('success', 'Votre page a été ajouté avec succés');
        }
        return $this->render('layout/AddPageSeo.html.twig', [
            'user' => $user
        ]);   

    }


    //     //  /**
    //     //  * @Route("/modifier-formation", name="UpdateFormation")
    //     //  */
    //     // public function UpdateFormation()
    //     // {
    //     //     return $this->render('layout/UpdateFormation.html.twig', [
    //     //         'controller_name' => 'ContactController',
    //     //     ]);
    //     // }

    /**
     * @Route("/delete-seo/{id}", name="DeleteSeo")
     */
    public function DeleteSeo($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $rep = $this->getDoctrine()->getRepository(Seo::class);
        $seo = $rep->find($id);
        $user = $this->getUser();

        if ($seo != null) {
            $entityManager->remove($seo);
            $entityManager->flush();
            $this->addFlash('successMessage', 'Page supprimée avec succés');
            return $this->redirectToRoute('seo');
            
        } else {
            return $this->redirectToRoute('seo');
         
        }
       
    }


    /**
     * @Route("/Update-seo/{id}", name="UpdateSeo")
     */
    public function UpdateSeo($id, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $seo = $entityManager->getRepository(Seo::class)->find($id);
        $user = $this->getUser();

        if (($request->isMethod('POST')) && ($seo != null)) {

            $seo->setPage($request->request->get('page'))
                ->setTitle($request->request->get('title'))
                ->setKeywords($request->request->get('keywords'))
                ->setDescription($request->request->get('description'));


            $entityManager->flush();
            $this->addFlash('successUpd', 'contunu Modifier avec succés');
            return $this->redirectToRoute('seo');

        } else {
            // throw $this->createNotFoundException("Articles non trouvés");
        }
        return $this->redirectToRoute('seo');
    }


    /**
     * @Route("/modifier-seo/{id}", name="GetSeo")
     */
    public function GetSeo($id)
    {
        $rep = $this->getDoctrine()->getRepository(Seo::class);
        $seo = $rep->find($id);
        $user = $this->getUser();

        if ($seo != null) {
            return $this->render('layout/UpdateSeo.html.twig', [
                'seo' => $seo,'user' => $user
            ]);

        } else {
            return $this->render('layout/UpdateSeo.html.twig', [
                'seo' => "",'user' => $user
            ]);
        }
    }
//-----------------------Seo-----------------------------//  









//-----------------------Profile-----------------------------//
     /**
     * @Route("/Profile",name="profile")
     */
    public function profile()
    { 
        $user = $this->getUser();
        return $this->render('layout/Profile.html.twig', [
            'user' => $user
        ]);
    }

     /**
     * @Route("/users",name="users")
     */
    public function users()
    { 
        $rep = $this->getDoctrine()->getRepository(Admin::class);
        $users = $rep->findAll();
        $user = $this->getUser();
        if ($users != null) {
            return $this->render('layout/Users.html.twig', [
                'us' => $users,'user' => $user
            ]);

        } else {
            return $this->render('layout/Users.html.twig', [
                'us' => "",'user' => $user
            ]);        
        }

    }


     /**
     * @Route("/Add-user",name="Adduser")
     */
    public function Adduser()
    {  
        $rep = $this->getDoctrine()->getRepository(Admin::class);
        $users = $rep->findAll();
        $user = $this->getUser();
        if ($users != null) {
            return $this->render('layout/AddUser.html.twig', [
                'user' => $user
            ]);

        } else {
            return $this->render('layout/AddUser.html.twig', [
                'user' => $user
            ]);        
        }

    }

    // /**
    //  * @Route("/ajouter-user", name="Adduser")
    //  */
    // public function Adduser(Request $request)
    // {
    //     $user = $this->getUser();
    //     if ($request->isMethod('POST')) {

    //         $file = $request->files->get('img');

    //         // Generate a unique name for the file before saving it
    //         $fileName = md5(uniqid()) . '.' . $file->guessExtension();

    //         $uploadDir = $this->getParameter('uploads_directory');
    //         move_uploaded_file($file, "$uploadDir/$fileName");


    //         $entityManager = $this->getDoctrine()->getManager();
    //         $formation = new formation();
    //         $formation->setTitre($request->request->get('titre'))
    //             ->setFormation($request->request->get('formation'))
    //             ->setIntervenants($request->request->get('intervenants'))
    //             ->setAdresse($request->request->get('adresse'))
    //             ->setDate(\DateTime::createFromFormat('Y-m-d', $request->request->get('date')))
    //             ->setImg($fileName);


    //         // tell Doctrine you want to (eventually) save the Product (no queries yet)
    //         $entityManager->persist($formation);
    //         // actually executes the queries (i.e. the INSERT query)
    //         $entityManager->flush();


    //         $this->addFlash('success', 'Votre Formation a été ajouté avec succés');
    //     }


    //     return $this->render('layout/AddFormation.html.twig', [
    //         'formations' => [],'user' => $user
    //     ]);

    // }
    //  /**
    //  * @Route("/modifier-formation", name="UpdateFormation")
    //  */
    // public function UpdateFormation()
    // {
    //     return $this->render('layout/UpdateFormation.html.twig', [
    //         'controller_name' => 'ContactController',
    //     ]);
    // }

    // /**
    //  * @Route("/delete-user/{id}", name="DeleteUser")
    //  */
    // public function DeleteUser($id)
    // {
    //     $entityManager = $this->getDoctrine()->getManager();

    //     $rep = $this->getDoctrine()->getRepository(Formation::class);
    //     $formation = $rep->find($id);
    //     $user = $this->getUser();

    //     if ($formation != null) {
    //         $entityManager->remove($formation);
    //         $entityManager->flush();
    //         $this->addFlash('successMessage', 'formation supprimée avec succés');
    //         return $this->redirectToRoute('Formation');

    //     } else {
    //         return $this->render('layout/Formation.html.twig', [
    //             'formations' => [],'user' => $user
    //         ]);
    //     }
    // }


    // /**
    //  * @Route("/Update-user", name="UpdateProfile")
    //  */
    // public function UpdateProfile($id, Request $request)
    // {$user = $this->getUser();
    //     $entityManager = $this->getDoctrine()->getManager();
    //     $admin = $entityManager->getRepository(Admin::class)->find($user.$id);
        


    //     if (($request->isMethod('POST')) && ($admin != null)) {
    //         $file = $request->files->get('img');

    //         if ($file == null) {
    //             $formation->setTitre($request->request->get('titre'))
    //                 ->setFormation($request->request->get('formation'))
    //                 ->setIntervenants($request->request->get('intervenants'))
    //                 ->setAdresse($request->request->get('adresse'));
    //         } else {
    //             // Generate a unique name for the file before saving it
    //             $fileName = md5(uniqid()) . '.' . $file->guessExtension();

    //             $uploadDir = $this->getParameter('uploads_directory');
    //             move_uploaded_file($file, "$uploadDir/$fileName");

    //             $formation->setTitre($request->request->get('titre'))
    //                 ->setFormation($request->request->get('formation'))
    //                 ->setIntervenants($request->request->get('intervenants'))
    //                 ->setAdresse($request->request->get('adresse'))
    //                 ->setImg($fileName);
    //         }
    //         $entityManager->flush();
    //         $this->addFlash('successUpd', 'Profile Modifier avec succés');
    //         return $this->redirectToRoute('profile');

    //     } else {
    //         return $this->render('layout/Profile.html.twig', [
    //             'formations' => [],'user' => $user
    //         ]);        }
    // }


    // /**
    //  * @Route("/modifier-formation/{id}", name="GetFormation")
    //  */
    // public function GetFormation($id)
    // {
    //     $rep = $this->getDoctrine()->getRepository(Formation::class);
    //     $formation = $rep->find($id);
    //     $user = $this->getUser();


    //     if ($formation != null) {
    //         return $this->render('layout/UpdateFormation.html.twig', [
    //             'formation' => $formation,'user' => $user
    //         ]);

    //     } else {
    //         return $this->render('layout/Formation.html.twig', [
    //             'formations' => [],'user' => $user
    //         ]);        }
    // }
//-----------------------Formation-----------------------------//  








}
