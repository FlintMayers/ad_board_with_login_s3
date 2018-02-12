<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Advert;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Advert controller.
 *
 * @Route("advert")
 */
class AdvertController extends Controller
{
    /**
     * Lists all advert entities.
     *
     * @Route("/", name="advert_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $adverts = $em->getRepository('AppBundle:Advert')->findBy([], ['modified_at' => 'DESC']);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $adverts,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('advert/index.html.twig', array('pagination' => $pagination));
    }

    /**
     * Lists all advert entities belonging to current logged in user.
     *
     * @Route("/user", name="advert_user")
     * @Method("GET")
     */
    public function indexUserAction()
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page');
        $em = $this->getDoctrine()->getManager();
        $currentUserId = $this->getUser()->getId();

        $adverts = $em->getRepository('AppBundle:Advert')->findBy(['user' => $currentUserId]);

        return $this->render('advert/indexUser.html.twig', array(
            'adverts' => $adverts,
        ));
    }

    /**
     * Creates a new advert entity.
     *
     * @Route("/new", name="advert_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page');
        $advert = new Advert();
        $form = $this->createForm('AppBundle\Form\AdvertType', $advert);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $currentUser = $this->getUser();
            $advert->setUser($currentUser);
            $em->persist($advert);
            $em->flush();

            return $this->redirectToRoute('advert_show', array('id' => $advert->getId()));
        }

        return $this->render('advert/new.html.twig', array(
            'advert' => $advert,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a advert entity.
     *
     * @Route("/{id}", name="advert_show")
     * @Method("GET")
     */
    public function showAction(Advert $advert)
    {
        $deleteForm = $this->createDeleteForm($advert);

        return $this->render('advert/show.html.twig', array(
            'advert' => $advert,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing advert entity.
     *
     * @Route("/{id}/edit", name="advert_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Advert $advert)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page');
        $currentUser = $this->getUser()->getId();
        $advertOwner = $advert->getUser()->getId();

        if ($currentUser != $advertOwner){
            return $this->redirectToRoute('advert_index');
        }

        $deleteForm = $this->createDeleteForm($advert);
        $editForm = $this->createForm('AppBundle\Form\AdvertType', $advert);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('advert_edit', array('id' => $advert->getId()));
        }

        return $this->render('advert/edit.html.twig', array(
            'advert' => $advert,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes an advert entity.
     *
     * @Route("/{id}", name="advert_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Advert $advert)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page');
        $form = $this->createDeleteForm($advert);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentUser = $this->getUser()->getId();
            $advertOwner = $advert->getUser()->getId();

            if ($currentUser == $advertOwner){
                $em = $this->getDoctrine()->getManager();
                $em->remove($advert);
                $em->flush();
            }
        }

        return $this->redirectToRoute('advert_index');
    }

    /**
     * Creates a form to delete a advert entity.
     *
     * @param Advert $advert The advert entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Advert $advert)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('advert_delete', array('id' => $advert->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}