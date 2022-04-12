<?php

namespace App\Controller;

use App\Entity\Annonces;
use App\Entity\Images;
use App\Form\AnnoncesType;
use App\Repository\AnnoncesRepository;
use App\Repository\ImagesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/annonces')]
class AnnoncesController extends AbstractController
{
    #[Route('/', name: 'app_annonces_index', methods: ['GET'])]
    public function index(AnnoncesRepository $annoncesRepository): Response
    {
        return $this->render('annonces/index.html.twig', [
            'annonces' => $annoncesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_annonces_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AnnoncesRepository $annoncesRepository): Response
    {
        $annonce = new Annonces();
        $form = $this->createForm(AnnoncesType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //On récupère les images transmises
            $images = $form->get('image')->getData();

            // On boucle sur les images
            foreach($images as $image){
                //On génère un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' .$image->guessExtension();

                // On copie le fichier dans le dossier Uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                // On stocke l'image dans la base de données
                $img = new Images();
                $img->setName($fichier);
                $annonce->addImage($img);
            }

            $annoncesRepository->add($annonce);
            return $this->redirectToRoute('app_annonces_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('annonces/new.html.twig', [
            'annonce' => $annonce,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_annonces_show', methods: ['GET'])]
    public function show(Annonces $annonce): Response
    {
        return $this->render('annonces/show.html.twig', [
            'annonce' => $annonce,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_annonces_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Annonces $annonce, AnnoncesRepository $annoncesRepository): Response
    {
        $form = $this->createForm(AnnoncesType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

             //On récupère les images transmises
             $images = $form->get('image')->getData();

             // On boucle sur les images
             foreach($images as $image){
                 //On génère un nouveau nom de fichier
                 $fichier = md5(uniqid()) . '.' .$image->guessExtension();
 
                 // On copie le fichier dans le dossier Uploads
                 $image->move(
                     $this->getParameter('images_directory'),
                     $fichier
                 );
                 // On stocke l'image dans la base de données
                 $img = new Images();
                 $img->setName($fichier);
                 $annonce->addImage($img);
             }
            $annoncesRepository->add($annonce);
            return $this->redirectToRoute('app_annonces_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('annonces/edit.html.twig', [
            'annonce' => $annonce,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_annonces_delete', methods: ['POST'])]
    public function delete(Request $request, Annonces $annonce, AnnoncesRepository $annoncesRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$annonce->getId(), $request->request->get('_token'))) {
            $annoncesRepository->remove($annonce);
        }

        return $this->redirectToRoute('app_annonces_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/delete/image/{id}', name: 'app_annonces_delete_image', methods: ['DELETE'])]
    public function deleteImage(Images $image, Request $request, ImagesRepository $imagesRepository) {
        $data = json_decode($request->getContent(), true);
      
        // On vérifie si le token est valide
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $data['_token'])) {
            // On récupère le nom de l'image
            $image->getName();
            //ON supprime le fichier
            unlink($this->getParameter('images_directory'). '/'.$image);

            $imagesRepository->remove($image);

            // On répond en json
            return new JsonResponse(['success' => 1]);
        }else{
            return new JsonResponse(['error' => 'Token invalide', 400]);
        }
        
    }   
}
