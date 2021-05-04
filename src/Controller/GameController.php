<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;
use App\Entity\User;
use App\Entity\Carte;
use App\Entity\Partie;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
/**
 * Class GameController
 * @package App\Controller
 * @Route("/partie")
 */
class GameController extends AbstractController
{
    /**
     * @Route("/menu", name="menu_game")
     */
    public function nouvellePartie() {
        $adversaires = [];
        $joueurs = $this->getDoctrine()->getRepository(User::class)->findAll();
        $joueur_session = $this->getUser();
        $amis = (array)$joueur_session->getFriends();
        $parties = $this->getDoctrine()->getRepository('App:Partie')->cherchePartie($joueur_session->getId());
        foreach($parties as $partie){
            $adversaires[$partie->getJ2_id()] = $this->getDoctrine()->getRepository(User::class)->findById($partie->getJ2_id());
        }
        
        return $this->render('game/menu.html.twig', ['joueurs' => $joueurs, 'amis' => $amis]);
    }

    /**
    * @Route("/friends", name="friends")
    */

    public function jouerAmis() {
            $adversaires = [];
            $joueur_session = $this->getUser();
            $amis = (array)$joueur_session->getFriends();
            
            foreach($amis as $ami){
                $adversaires[$ami] = $this->getDoctrine()->getRepository(User::class)->findById($ami);
                
            }
            return $this->render('game/friends.html.twig', ['amis' => $amis, 'adversaires' => $adversaires]);
    }


    /**
    * @Route("/random", name="random")
    */

    public function random() {
        $adversaires = [];  
        $joueur_session = $this->getUser();
        $amis = (array)$joueur_session->getFriends();
        $adversaires = $this->getDoctrine()->getRepository(User::class)->findAll();        
        $random = rand (1, count($adversaires));
        $joueur = $this->getDoctrine()->getRepository(User::class)->findById($random);
        var_dump($joueur);
        
        return $this->render('game/random.html.twig', ['amis' => $amis, 'adversaires' => $joueur]);
    }

    /**
    * @Route("/mygame", name="mygame")
    */

    public function mygame() {
        $adversaires = [];
        $joueur_session = $this->getUser();
        $amis = (array)$joueur_session->getFriends();
        $parties = $this->getDoctrine()->getRepository('App:Partie')->cherchePartie($joueur_session->getId());
        foreach($parties as $partie){
            $adversaires[$partie->getJ2_id()] = $this->getDoctrine()->getRepository(User::class)->findById($partie->getJ2_id());
        }

        return $this->render('game/mygame.html.twig', ['amis' => $amis, 'adversaires' => $adversaires, 'parties' => $parties]);
    }

    
    
    







    /**
     * @Route("/creer", name="creer_partie")
     */
    public function creerPartie(Request $request) {

        $idAdversaire = $request->request->get('adversaire');
        $joueur = $this->getDoctrine()->getRepository(User::class)->find($this->getUser()->getId());
        $adversaire = $this->getDoctrine()->getRepository(User::class)->find($idAdversaire);
        $cartes = $this->getDoctrine()->getRepository(Carte::class)->findAll();
        foreach ($cartes as $carte) {
            $tabCartes[] = $carte->getId();
        }
        shuffle($tabCartes); //mélange le tableau contenant les id
        //retrait de la première carte
        $carteecarte = array_pop($tabCartes);
        //Distribution des cartes aux joueurs,
        $tabMainJ1 = array();
        for($i=0; $i<6; $i++) {
            $tabMainJ1[] = array_pop($tabCartes);
        }
        $tabMainJ2 = array();
        for($i=0; $i<6; $i++) {
            $tabMainJ2[] = array_pop($tabCartes);
        }
        //La création de la pioche
        $tabPioche = $tabCartes; //sauvegarde des dernières cartes dans la pioche
        //créer un objet de type Partie
        $partie = new Partie();
        $partie->setJ1_id($this->getUser()->getId());
        $partie->setJ2_id($idAdversaire);
        $partie->setCarte_rejected($carteecarte);


        $partie->setCarte_placed_j1(json_encode(array('pirate1'=>0,'pirate2'=>0,'pirate3'=>0,'pirate4'=>0,'pirate5'=>0,'pirate6'=>0,'pirate7'=>0)));
        $partie->setCarte_placed_j2(json_encode(array('pirate1'=>0,'pirate2'=>0,'pirate3'=>0,'pirate4'=>0,'pirate5'=>0,'pirate6'=>0,'pirate7'=>0)));
        $partie->setManche(1);

        $random_tour = rand(1, 2);
        if ($random_tour==1) {
            $partie->setTour($joueur);
            $tabMainJ1[] = array_pop($tabPioche);
        }else{
            $partie->setTour($adversaire);
            $tabMainJ2[] = array_pop($tabPioche);
        }


        sort($tabMainJ1);
        sort($tabMainJ2);

        $partie->setMain_j1(json_encode($tabMainJ1));
        $partie->setMain_j2(json_encode($tabMainJ2));
        $partie->setPioche(json_encode($tabPioche));
        $partie->setScore_j1(0);
        $partie->setScore_j2(0);

        $secret = ['etat'=>0,'cartes'=>0];
        $compromis = ['etat'=>0,'cartes'=>[]];
        $cadeau = ['etat'=>0,'cartes'=>[]];
        $duo1 = [];
        $duo2 = [];
        $duos = ['duo1'=>$duo1, 'duo2'=>$duo2];
        $concurrence = ['etat'=>0,'cartes'=>$duos];
        $actions = array('secret'=>$secret, 'compromis'=>$compromis, 'cadeau'=>$cadeau, 'concurrence'=>$concurrence);
        $partie->setActions_j1(json_encode($actions));
        $partie->setActions_j2(json_encode($actions));

        $partie->setObjectifs(json_encode(array('pirate1'=>0,'pirate2'=>0,'pirate3'=>0,'pirate4'=>0,'pirate5'=>0,'pirate6'=>0,'pirate7'=>0)));

        //Récupérer le manager de doctrine
        $em = $this->getDoctrine()->getManager();
        //Sauvegarde mon objet Partie dans la base de données
        $em->persist($partie);
        $em->flush();
        return $this->redirectToRoute('afficher_partie', ['id' => $partie->getId()]);
    }




    /**
     * @Route("/afficher/{id}", name="afficher_partie")
     */
    public function afficherPartie(Partie $partie) {
        //$partie
        $cartes = $this->getDoctrine()->getRepository("App:Carte")->findAll();
        $objectifs = $this->getDoctrine()->getRepository("App:Objectif")->findAll();

        $tabCartes = array();
        foreach ($cartes as $carte)
        {
            $tabCartes[$carte->getId()] = $carte;
        }

        $actionsJ1 = $partie->getActions_j1();
        $actionsJ2 = $partie->getActions_j2();
        $j1Id = $partie->getJ1_id();
        $j2Id = $partie->getJ2_id();
        $j1 = $this->getDoctrine()->getRepository("App:User")->find($j1Id);
        $j2 = $this->getDoctrine()->getRepository("App:User")->find($j2Id);
        if ($actionsJ1->secret->etat==1 && $actionsJ1->compromis->etat==1 && $actionsJ1->cadeau->etat==2 && $actionsJ1->concurrence->etat==2){
            if ($actionsJ2->secret->etat==1 && $actionsJ2->compromis->etat==1 && $actionsJ2->cadeau->etat==2 && $actionsJ2->concurrence->etat==2){
                return $this->redirectToRoute('fin_manche', ['id' => $partie->getId()]);
            }else{
                return $this->render('game/afficher.html.twig', ['cartes' => $tabCartes, 'objectifs' => $objectifs, 'partie' => $partie, 'joueur1' => $j1, 'joueur2' => $j2]);
            }
        }else {
            return $this->render('game/afficher.html.twig', ['cartes' => $tabCartes, 'objectifs' => $objectifs, 'partie' => $partie, 'joueur1' => $j1, 'joueur2' => $j2]);
        }
    }

    /**
     * @Route("/passetour/{id}", name="passer_tour")
     */
    public function passerTour(Partie $partie){

        $tour_prec = $partie->getTour();
        if($tour_prec->getID() == $partie->getJ1_id()){
            $newTourJoueurId = $partie->getJ2_id();
            $partie->setTour($this->getDoctrine()->getRepository("App:User")->find($newTourJoueurId));

            $pioche = $partie->getPioche();
            if(empty($pioche) == false){
                $mainJTour = $partie->getMain_J2();
                $mainJTour[] = array_pop($pioche);
                sort($mainJTour);
                $partie->setPioche(json_encode($pioche));
                $partie->setMain_j2(json_encode($mainJTour));
            }

        }elseif($tour_prec->getID() == $partie->getJ2_id()) {
            $newTourJoueurId = $partie->getJ1_id();
            $partie->setTour($this->getDoctrine()->getRepository("App:User")->find($newTourJoueurId));


            $pioche = $partie->getPioche();
            if(empty($pioche) == false){
                $mainJTour = $partie->getMain_J1();
                $mainJTour[] = array_pop($pioche);
                sort($mainJTour);
                $partie->setPioche(json_encode($pioche));
                $partie->setMain_j1(json_encode($mainJTour));
            }
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($partie);
        $em->flush();

        return $this->redirectToRoute('afficher_partie', ['id' => $partie->getId()]);
    }

    /**
     * @Route("/action/secret/{id}", name="action_secret")
     */
    public function actionSecret(Partie $partie, Request $request) {

        $idcarte = $request->request->get('idCarte');
        $joueurId = $partie->getTour()->getId();

        if($joueurId == $partie->getJ1_id()){
            $main = $partie->getMain_j1();
            unset($main[array_search($idcarte, $main)]);
            $main = array_values($main);
            sort($main);

            $actions = $partie->getActions_j1();
            $actions->secret->etat = 1;
            $actions->secret->cartes = $idcarte;

            $partie->setMain_j1(json_encode($main));
            $partie->setActions_j1(json_encode($actions));

        }elseif ($joueurId == $partie->getJ2_id()) {
            $main = $partie->getMain_j2();
            unset($main[array_search($idcarte, $main)]);
            $main = array_values($main);
            sort($main);

            $actions = $partie->getActions_j2();
            $actions->secret->etat = 1;
            $actions->secret->cartes = $idcarte;

            $partie->setMain_j2(json_encode($main));
            $partie->setActions_j2(json_encode($actions));
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($partie);
        $em->flush();
        return $this->redirectToRoute('passer_tour', ['id' => $partie->getId()]);
    }


    /**
     * @Route("/action/compromis/{id}", name="action_compromis")
     */
    public function actioncompromis(Partie $partie, Request $request){

        $idcarte1 = $request->request->get('idCarte1');
        $idcarte2 = $request->request->get('idCarte2');
        $joueurId = $partie->getTour()->getId();

        if($joueurId == $partie->getJ1_id()){
            $main = $partie->getMain_j1();
            unset($main[array_search($idcarte1, $main)]);
            unset($main[array_search($idcarte2, $main)]);
            $main = array_values($main);
            sort($main);

            $actions = $partie->getActions_j1();
            $actions->compromis->etat = 1;
            $actions->compromis->cartes[] = $idcarte1;
            $actions->compromis->cartes[] = $idcarte2;

            $partie->setMain_j1(json_encode($main));
            $partie->setActions_j1(json_encode($actions));

        }elseif ($joueurId == $partie->getJ2_id()) {
            $main = $partie->getMain_j2();
            unset($main[array_search($idcarte1, $main)]);
            unset($main[array_search($idcarte2, $main)]);
            $main = array_values($main);
            sort($main);

            $actions = $partie->getActions_j2();
            $actions->compromis->etat = 1;
            $actions->compromis->cartes[] = $idcarte1;
            $actions->compromis->cartes[] = $idcarte2;

            $partie->setMain_j2(json_encode($main));
            $partie->setActions_j2(json_encode($actions));
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($partie);
        $em->flush();
        return $this->redirectToRoute('passer_tour', ['id' => $partie->getId()]);
    }

    /**
     * @Route("/action/cadeau/{id}", name="action_cadeau")
     */

    public function actionCadeau(Partie $partie, Request $request){
        $idCarte1 = $request->request->get('idCarte1');
        $idCarte2 = $request->request->get('idCarte2');
        $idCarte3 = $request->request->get('idCarte3');

        if ($partie->getTour()->getId() == $partie->getJ1_id()) {
            $main = $partie->getMain_j1();
            unset($main[array_search($idCarte1, $main)]);
            unset($main[array_search($idCarte2, $main)]);
            unset($main[array_search($idCarte3, $main)]);
            $main = array_values($main);
            sort($main);

            $actions = $partie->getActions_j1();
            $actions->cadeau->etat = 1;
            $actions->cadeau->cartes[] = $idCarte1;
            $actions->cadeau->cartes[] = $idCarte2;
            $actions->cadeau->cartes[] = $idCarte3;

            $partie->setMain_j1(json_encode($main));
            $partie->setActions_j1(json_encode($actions));

            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->flush();
            return $this->redirectToRoute('afficher_partie', ['id' => $partie->getId()]);

        }else if ($partie->getTour()->getId() == $partie->getJ2_id()) {
            $main = $partie->getMain_j2();
            unset($main[array_search($idCarte1, $main)]);
            unset($main[array_search($idCarte2, $main)]);
            unset($main[array_search($idCarte3, $main)]);
            $main = array_values($main);
            sort($main);

            $actions = $partie->getActions_j2();
            $actions->cadeau->etat = 1;
            $actions->cadeau->cartes[] = $idCarte1;
            $actions->cadeau->cartes[] = $idCarte2;
            $actions->cadeau->cartes[] = $idCarte3;

            $partie->setMain_j2(json_encode($main));
            $partie->setActions_j2(json_encode($actions));

            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->flush();
            return $this->redirectToRoute('afficher_partie', ['id' => $partie->getId()]);

        }
    }

    /**
     * @Route("/action/cadeau/reponse/{id}", name="action_cadeau_rep")
     */
    public function reponseCadeau(Partie $partie, Request $request){

        $idCarte = $request->request->get('idCarte');
        $carteSelect = $this->getDoctrine()->getRepository('App:Carte')->find($idCarte);
        $valeurCarteSelect = $carteSelect->getObjectif_id();
        $valeurCarteSelect = "pirate".$valeurCarteSelect;

        if ($partie->getTour()->getId() == $partie->getJ1_id()) {
            $cartesPlacedJ2 = $partie->getCarte_placed_j2();
            $cartesPlacedJ2[$valeurCarteSelect] += 1;
            $partie->setCarte_placed_j2(json_encode($cartesPlacedJ2));
            $actions = $partie->getActions_j1();
            $tabCartesRestantes = $actions->cadeau->cartes;
            foreach ($tabCartesRestantes as $value){
                if ($value != $idCarte){
                    $carteRestante = $this->getDoctrine()->getRepository('App:Carte')->find($value);
                    $valeurCarteRestante = $carteRestante->getObjectif_id();
                    $valeurCarteRestante = "pirate".$valeurCarteRestante;
                    $cartesPlacedJ1 = $partie->getCarte_placed_j1();
                    $cartesPlacedJ1[$valeurCarteRestante] += 1;
                    $partie->setCarte_placed_j1(json_encode($cartesPlacedJ1));
                }
            }
            $actions->cadeau->etat = 2;
            $partie->setActions_j1(json_encode($actions));

        }else if ($partie->getTour()->getId() == $partie->getJ2_id()) {
            $cartesPlacedJ1 = $partie->getCarte_placed_j1();
            $cartesPlacedJ1[$valeurCarteSelect] += 1;
            $partie->setCarte_placed_j1(json_encode($cartesPlacedJ1));
            $actions = $partie->getActions_j2();
            $tabCartesRestantes = $actions->cadeau->cartes;
            foreach ($tabCartesRestantes as $value){
                if ($value != $idCarte){
                    $carteRestante = $this->getDoctrine()->getRepository('App:Carte')->find($value);
                    $valeurCarteRestante = $carteRestante->getObjectif_id();
                    $valeurCarteRestante = "pirate".$valeurCarteRestante;
                    $cartesPlacedJ2 = $partie->getCarte_placed_j2();
                    $cartesPlacedJ2[$valeurCarteRestante] += 1;
                    $partie->setCarte_placed_j2(json_encode($cartesPlacedJ2));
                }
            }
            $actions->cadeau->etat = 2;
            $partie->setActions_j2(json_encode($actions));
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($partie);
        $em->flush();
        return $this->redirectToRoute('passer_tour', ['id' => $partie->getId()]);
    }

    /**
     * @Route("/action/concurrence/{id}", name="action_concurrence")
     */
    public function actionConcurrence(Partie $partie, Request $request){

        $idCarte1 = $request->request->get('idCarte1');
        $idCarte2 = $request->request->get('idCarte2');
        $idCarte3 = $request->request->get('idCarte3');
        $idCarte4 = $request->request->get('idCarte4');

        if ($partie->getTour()->getId() == $partie->getJ1_id()) {
            $main = $partie->getMain_j1();
            unset($main[array_search($idCarte1, $main)]);
            unset($main[array_search($idCarte2, $main)]);
            unset($main[array_search($idCarte3, $main)]);
            unset($main[array_search($idCarte4, $main)]);
            $main = array_values($main);
            sort($main);

            $actions = $partie->getActions_j1();
            $actions->concurrence->etat = 1;
            $actions->concurrence->cartes->duo1[] = $idCarte1;
            $actions->concurrence->cartes->duo1[] = $idCarte2;
            $actions->concurrence->cartes->duo2[] = $idCarte3;
            $actions->concurrence->cartes->duo2[] = $idCarte4;

            $partie->setMain_j1(json_encode($main));
            $partie->setActions_j1(json_encode($actions));

            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->flush();
            return $this->redirectToRoute('afficher_partie', ['id' => $partie->getId()]);

        }else if ($partie->getTour()->getId() == $partie->getJ2_id()) {
            $main = $partie->getMain_j2();
            unset($main[array_search($idCarte1, $main)]);
            unset($main[array_search($idCarte2, $main)]);
            unset($main[array_search($idCarte3, $main)]);
            unset($main[array_search($idCarte4, $main)]);
            $main = array_values($main);
            sort($main);

            $actions = $partie->getActions_j2();
            $actions->concurrence->etat = 1;
            $actions->concurrence->cartes->duo1[] = $idCarte1;
            $actions->concurrence->cartes->duo1[] = $idCarte2;
            $actions->concurrence->cartes->duo2[] = $idCarte3;
            $actions->concurrence->cartes->duo2[] = $idCarte4;

            $partie->setMain_j2(json_encode($main));
            $partie->setActions_j2(json_encode($actions));

            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->flush();
            return $this->redirectToRoute('afficher_partie', ['id' => $partie->getId()]);

        }
    }

    /**
     * @Route("/action/concurrence/reponse/{id}", name="action_concurrence_rep")
     */
    public function reponseConcurrence(Partie $partie, Request $request)
    {
        $idDuoChoisi = $request->request->get('idDuo');

        if ($partie->getTour()->getId() == $partie->getJ1_id()) {
            if ($idDuoChoisi == 'duo1') {
                $actions = $partie->getActions_j1();
                $cartesChoisies = $actions->concurrence->cartes->duo1;
                foreach ($cartesChoisies as $value) {
                    $carte = $this->getDoctrine()->getRepository("App:Carte")->find($value);
                    $idObjectifCarte = $carte->getObjectif_Id();
                    $idObjectifCarte = "pirate" . $idObjectifCarte;
                    $CartesPlacedJ2 = $partie->getCarte_placed_j2();
                    $CartesPlacedJ2[$idObjectifCarte] += 1;
                    $partie->setCarte_placed_j2(json_encode($CartesPlacedJ2));
                }
                $autreDuo = $actions->concurrence->cartes->duo2;
                foreach ($autreDuo as $value) {
                    $carte = $this->getDoctrine()->getRepository("App:Carte")->find($value);
                    $idObjectifCarte = $carte->getObjectif_Id();
                    $idObjectifCarte = "pirate" . $idObjectifCarte;
                    $CartesPlacedJ1 = $partie->getCarte_placed_j1();
                    $CartesPlacedJ1[$idObjectifCarte] += 1;
                    $partie->setCarte_placed_j1(json_encode($CartesPlacedJ1));
                }
                $actions->concurrence->etat = 2;
                $partie->setActions_j1(json_encode($actions));

            } else if ($idDuoChoisi == 'duo2') {

                $actions = $partie->getActions_j1();
                $cartesChoisies = $actions->concurrence->cartes->duo2;
                foreach ($cartesChoisies as $value) {
                    $carte = $this->getDoctrine()->getRepository("App:Carte")->find($value);
                    $idObjectifCarte = $carte->getObjectif_Id();
                    $idObjectifCarte = "pirate" . $idObjectifCarte;
                    $CartesPlacedJ2 = $partie->getCarte_placed_j2();
                    $CartesPlacedJ2[$idObjectifCarte] += 1;
                    $partie->setCarte_placed_j2(json_encode($CartesPlacedJ2));
                }
                $autreDuo = $actions->concurrence->cartes->duo1;
                foreach ($autreDuo as $value) {
                    $carte = $this->getDoctrine()->getRepository("App:Carte")->find($value);
                    $idObjectifCarte = $carte->getObjectif_Id();
                    $idObjectifCarte = "pirate" . $idObjectifCarte;
                    $CartesPlacedJ1 = $partie->getCarte_placed_j1();
                    $CartesPlacedJ1[$idObjectifCarte] += 1;
                    $partie->setCarte_placed_j1(json_encode($CartesPlacedJ1));
                }
                $actions->concurrence->etat = 2;
                $partie->setActions_j1(json_encode($actions));
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->flush();
            return $this->redirectToRoute('passer_tour', ['id' => $partie->getId()]);

        } else if ($partie->getTour()->getId() == $partie->getJ2_id()) {

            if ($idDuoChoisi == 'duo1') {
                $actions = $partie->getActions_j2();
                $cartesChoisies = $actions->concurrence->cartes->duo1;
                foreach ($cartesChoisies as $value) {
                    $carte = $this->getDoctrine()->getRepository("App:Carte")->find($value);
                    $idObjectifCarte = $carte->getObjectif_Id();
                    $idObjectifCarte = "pirate" . $idObjectifCarte;
                    $CartesPlacedJ1 = $partie->getCarte_placed_j1();
                    $CartesPlacedJ1[$idObjectifCarte] += 1;
                    $partie->setCarte_placed_j1(json_encode($CartesPlacedJ1));
                }
                $autreDuo = $actions->concurrence->cartes->duo2;
                foreach ($autreDuo as $value) {
                    $carte = $this->getDoctrine()->getRepository("App:Carte")->find($value);
                    $idObjectifCarte = $carte->getObjectif_Id();
                    $idObjectifCarte = "pirate" . $idObjectifCarte;
                    $CartesPlacedJ2 = $partie->getCarte_placed_j2();
                    $CartesPlacedJ2[$idObjectifCarte] += 1;
                    $partie->setCarte_placed_j2(json_encode($CartesPlacedJ2));
                }
                $actions->concurrence->etat = 2;
                $partie->setActions_j2(json_encode($actions));

            } else if ($idDuoChoisi == 'duo2') {

                $actions = $partie->getActions_j2();
                $cartesChoisies = $actions->concurrence->cartes->duo2;
                foreach ($cartesChoisies as $value) {
                    $carte = $this->getDoctrine()->getRepository("App:Carte")->find($value);
                    $idObjectifCarte = $carte->getObjectif_Id();
                    $idObjectifCarte = "pirate" . $idObjectifCarte;
                    $CartesPlacedJ1 = $partie->getCarte_placed_j1();
                    $CartesPlacedJ1[$idObjectifCarte] += 1;
                    $partie->setCarte_placed_j1(json_encode($CartesPlacedJ1));
                }
                $autreDuo = $actions->concurrence->cartes->duo1;
                foreach ($autreDuo as $value) {
                    $carte = $this->getDoctrine()->getRepository("App:Carte")->find($value);
                    $idObjectifCarte = $carte->getObjectif_Id();
                    $idObjectifCarte = "pirate" . $idObjectifCarte;
                    $CartesPlacedJ2 = $partie->getCarte_placed_j2();
                    $CartesPlacedJ2[$idObjectifCarte] += 1;
                    $partie->setCarte_placed_j2(json_encode($CartesPlacedJ2));
                }
                $actions->concurrence->etat = 2;
                $partie->setActions_j2(json_encode($actions));
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->flush();
            return $this->redirectToRoute('passer_tour', ['id' => $partie->getId()]);
        }
    }

    /**
     * @Route("/fin_manche/{id}", name="fin_manche")
     */
    public function finManche(Partie $partie){

        $cartePlacedJ1 = $partie->getCarte_placed_j1();
        $cartePlacedJ2 = $partie->getCarte_placed_j2();

        $objectifs = $partie->getObjectifs();
        $scoreJ1 = $partie->getScore_j1();
        $scoreJ2 = $partie->getScore_J2();

        $idJ1 = $partie->getJ1_id();
        $idJ2 = $partie->getJ2_id();

        $actionsJ1 = $partie->getActions_j1();
        $actionsJ2 = $partie->getActions_j2();
        $secretJ1 = $actionsJ1->secret->cartes;
        $secretJ2 = $actionsJ2->secret->cartes;

        $carteSecretJ1 = $this->getDoctrine()->getRepository("App:Carte")->find($secretJ1);
        $carteSecretJ2 = $this->getDoctrine()->getRepository("App:Carte")->find($secretJ2);

        $objectifIdSecretJ1 = $carteSecretJ1->getObjectif_Id();
        $objectifIdSecretJ2 = $carteSecretJ2->getObjectif_Id();
        $objectifIdSecretJ1 = "pirate".$objectifIdSecretJ1;
        $objectifIdSecretJ2 = "pirate".$objectifIdSecretJ2;

        $cartePlacedJ1[$objectifIdSecretJ1] += 1;
        $cartePlacedJ2[$objectifIdSecretJ2] += 1;
        foreach ($cartePlacedJ1 as $key => $value){
            if ($cartePlacedJ1[$key] > $cartePlacedJ2[$key]){
                $objectifs[$key] = 1;
            }else if ($cartePlacedJ1[$key] < $cartePlacedJ2[$key]){
                $objectifs[$key] = 2;

            }
        }
        foreach ($objectifs as $key=>$value){
            if ($value ==1){
                if ($key =='pirate1'){
                    $scoreJ1 += 2;
                }if ($key =='pirate2'){
                    $scoreJ1 += 2;
                }if ($key =='pirate3'){
                    $scoreJ1 += 2;
                }if ($key =='pirate4'){
                    $scoreJ1 += 3;
                }if ($key =='pirate5'){
                    $scoreJ1 += 3;
                }if ($key =='pirate6'){
                    $scoreJ1 += 4;
                }if ($key =='pirate7'){
                    $scoreJ1 += 5 ;
                }
            }else if($value == 2){
                if ($key =='pirate1'){
                    $scoreJ2 += 2;
                }if ($key =='pirate2'){
                    $scoreJ2 += 2;
                }if ($key =='pirate3'){
                    $scoreJ2 += 2;
                }if ($key =='pirate4'){
                    $scoreJ2 += 3;
                }if ($key =='pirate5'){
                    $scoreJ2 += 3;
                }if ($key =='pirate6'){
                    $scoreJ2 += 4;
                }if ($key =='pirate7'){
                    $scoreJ2 += 5 ;
                }
            }
        }
        $partie->setCarte_placed_j1(json_encode($cartePlacedJ1));
        $partie->setCarte_placed_j2(json_encode($cartePlacedJ2));
        $partie->setObjectifs(json_encode($objectifs));
        $partie->setScore_j1($scoreJ1);
        $partie->setScore_j2($scoreJ2);

        $ptJ1 = 0;
        $ptJ2 = 0;

        foreach ($objectifs as $key => $value){
            if ($value == 1){
                $ptJ1 += 1;
            }elseif ($value == 2){
                $ptJ2 += 1;
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($partie);
        $em->flush();

        if ($scoreJ1 >= 11 AND $scoreJ1>$scoreJ2){
            $winner = $this->getDoctrine()->getRepository('App:User')->find($idJ1);
            $looser = $this->getDoctrine()->getRepository('App:User')->find($idJ2);

            $wPartiesPlayed = $winner->getParties_played();
            $wPartiesWin = $winner->getParties_win();
            $wPartiesLoose = $winner->getParties_loose();
            $lPartiesPlayed = $looser->getParties_played();
            $lPartiesWin = $looser->getParties_win();
            $lPartiesLoose = $looser->getParties_loose();

            $wPartiesPlayed += 1;
            $lPartiesPlayed += 1;
            $wPartiesWin += 1;
            $lPartiesLoose += 1;

            $scoreWinner = $winner->getScore();
            $scoreLooser = $looser->getScore();
            $scoreWinner +=30;
            $scoreLooser -=10;

            $winner->setScore($scoreWinner);
            $winner->setParties_played($wPartiesPlayed);
            $winner->setParties_win($wPartiesWin);
            $looser->setScore($scoreLooser);
            $looser->setParties_played($lPartiesPlayed);
            $looser->setParties_loose($lPartiesLoose);

            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->persist($winner);
            $em->persist($looser);
            $em->flush();
            return $this->redirectToRoute('profil');
        }elseif ($scoreJ2 >= 11 AND $scoreJ2>$scoreJ1){
            $winner = $this->getDoctrine()->getRepository('App:User')->find($idJ2);
            $looser = $this->getDoctrine()->getRepository('App:User')->find($idJ1);

            $wPartiesPlayed = $winner->getParties_played();
            $wPartiesWin = $winner->getParties_win();
            $wPartiesLoose = $winner->getParties_loose();
            $lPartiesPlayed = $looser->getParties_played();
            $lPartiesWin = $looser->getParties_win();
            $lPartiesLoose = $looser->getParties_loose();

            $wPartiesPlayed += 1;
            $lPartiesPlayed += 1;
            $wPartiesWin += 1;
            $lPartiesLoose += 1;

            $scoreWinner = $winner->getScore();
            $scoreLooser = $looser->getScore();
            $scoreWinner +=30;
            $scoreLooser -=10;

            $winner->setScore($scoreWinner);
            $winner->setParties_played($wPartiesPlayed);
            $winner->setParties_win($wPartiesWin);
            $looser->setScore($scoreLooser);
            $looser->setParties_played($lPartiesPlayed);
            $looser->setParties_loose($lPartiesLoose);

            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->persist($winner);
            $em->persist($looser);
            $em->flush();
            return $this->redirectToRoute('profil');
        }else if ($ptJ1 >= 4){

            $winner = $this->getDoctrine()->getRepository('App:User')->find($idJ1);
            $looser = $this->getDoctrine()->getRepository('App:User')->find($idJ2);

            $wPartiesPlayed = $winner->getParties_played();
            $wPartiesWin = $winner->getParties_win();
            $wPartiesLoose = $winner->getParties_loose();
            $lPartiesPlayed = $looser->getParties_played();
            $lPartiesWin = $looser->getParties_win();
            $lPartiesLoose = $looser->getParties_loose();

            $wPartiesPlayed += 1;
            $lPartiesPlayed += 1;
            $wPartiesWin += 1;
            $lPartiesLoose += 1;

            $scoreWinner = $winner->getScore();
            $scoreLooser = $looser->getScore();
            $scoreWinner +=30;
            $scoreLooser -=10;

            $winner->setScore($scoreWinner);
            $winner->setParties_played($wPartiesPlayed);
            $winner->setParties_win($wPartiesWin);
            $looser->setScore($scoreLooser);
            $looser->setParties_played($lPartiesPlayed);
            $looser->setParties_loose($lPartiesLoose);

            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->persist($winner);
            $em->persist($looser);
            $em->flush();
            return $this->redirectToRoute('profil');
        }else if ($ptJ2 >= 4){

            $winner = $this->getDoctrine()->getRepository('App:User')->find($idJ2);
            $looser = $this->getDoctrine()->getRepository('App:User')->find($idJ1);

            $wPartiesPlayed = $winner->getParties_played();
            $wPartiesWin = $winner->getParties_win();
            $wPartiesLoose = $winner->getParties_loose();
            $lPartiesPlayed = $looser->getParties_played();
            $lPartiesWin = $looser->getParties_win();
            $lPartiesLoose = $looser->getParties_loose();

            $wPartiesPlayed += 1;
            $lPartiesPlayed += 1;
            $wPartiesWin += 1;
            $lPartiesLoose += 1;

            $scoreWinner = $winner->getScore();
            $scoreLooser = $looser->getScore();
            $scoreWinner +=30;
            $scoreLooser -=10;

            $winner->setScore($scoreWinner);
            $winner->setParties_played($wPartiesPlayed);
            $winner->setParties_win($wPartiesWin);
            $looser->setScore($scoreLooser);
            $looser->setParties_played($lPartiesPlayed);
            $looser->setParties_loose($lPartiesLoose);

            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->persist($winner);
            $em->persist($looser);
            $em->flush();
            return $this->redirectToRoute('profil');
        }else{

            $joueur = $this->getDoctrine()->getRepository('App:User')->find($idJ1);
            $adversaire = $this->getDoctrine()->getRepository('App:User')->find($idJ2);
            //récupérer les cartes depuis la base de données
            $cartes = $this->getDoctrine()->getRepository(Carte::class)->findAll();
            //Affecter des valeurs pour les différentes propriétés de partie
            //mélanger les cartes
            foreach ($cartes as $carte) {
                $tabCartes[] = $carte->getId();
            }
            shuffle($tabCartes); //mélange le tableau contenant les id
            //retrait de la première carte
            $carteecarte = array_pop($tabCartes);
            //Distribution des cartes aux joueurs,
            $tabMainJ1 = array();
            for($i=0; $i<6; $i++) {
                $tabMainJ1[] = array_pop($tabCartes);
            }
            $tabMainJ2 = array();
            for($i=0; $i<6; $i++) {
                $tabMainJ2[] = array_pop($tabCartes);
            }
            //La création de la pioche
            $tabPioche = $tabCartes; //sauvegarde des dernières cartes dans la pioche
            //remise à zéro

            $partie->setCarte_rejected($carteecarte);


            $partie->setCarte_placed_j1(json_encode(array('pirate1'=>0,'pirate2'=>0,'pirate3'=>0,'pirate4'=>0,'pirate5'=>0,'pirate6'=>0,'pirate7'=>0)));
            $partie->setCarte_placed_j2(json_encode(array('pirate1'=>0,'pirate2'=>0,'pirate3'=>0,'pirate4'=>0,'pirate5'=>0,'pirate6'=>0,'pirate7'=>0)));
            $manche = $partie->getManche();
            $manche += 1;
            $partie->setManche($manche);

            $random_tour = rand(1, 2);
            if ($random_tour==1) {
                $partie->setTour($joueur);
                $tabMainJ1[] = array_pop($tabPioche);
            }else{
                $partie->setTour($adversaire);
                $tabMainJ2[] = array_pop($tabPioche);
            }


            sort($tabMainJ1);
            sort($tabMainJ2);

            $partie->setMain_j1(json_encode($tabMainJ1));
            $partie->setMain_j2(json_encode($tabMainJ2));
            $partie->setPioche(json_encode($tabPioche));
            $partie->setScore_j1(0);
            $partie->setScore_j2(0);

            $secret = ['etat'=>0,'cartes'=>0];
            $compromis = ['etat'=>0,'cartes'=>[]];
            $cadeau = ['etat'=>0,'cartes'=>[]];
            $duo1 = [];
            $duo2 = [];
            $duos = ['duo1'=>$duo1, 'duo2'=>$duo2];
            $concurrence = ['etat'=>0,'cartes'=>$duos];
            $actions = array('secret'=>$secret, 'compromis'=>$compromis, 'cadeau'=>$cadeau, 'concurrence'=>$concurrence);
            $partie->setActions_j1(json_encode($actions));
            $partie->setActions_j2(json_encode($actions));

            //Récupérer le manager de doctrine
            $em = $this->getDoctrine()->getManager();
            //Sauvegarde mon objet Partie dans la base de données
            $em->persist($partie);
            $em->flush();
            return $this->redirectToRoute('afficher_partie', ['id' => $partie->getId()]);
        }
    }
}