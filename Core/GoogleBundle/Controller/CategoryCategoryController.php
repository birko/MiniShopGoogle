<?php

namespace Core\GoogleBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Core\GoogleBundle\Entity\CategoryCategory;
use Core\GoogleBundle\Form\CategoryCategoryType;

/**
 * CategoryCategory controller.
 *
 */
class CategoryCategoryController extends Controller
{

    /**
     * Lists all CategoryCategory entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->getRepository('CoreGoogleBundle:CategoryCategory')->getCategoryCategoryQuery();
        $page = $this->getRequest()->get("page", 1);
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page/*page number*/,
            100 /*limit per page*/
        );

        return $this->render('CoreGoogleBundle:CategoryCategory:index.html.twig', array(
           'entities' => $pagination,
        ));
    }
    /**
     * Creates a new CategoryCategory entity.
     *
     */
    public function createAction(Request $request, $category)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CoreGoogleBundle:CategoryCategory')->findOneByCategory($category);

        if ($entity) {
            return $this->redirect($this->generateUrl('googlecategorycategory_edit', array('id' => $entity->getId())));
        }
        
        $entity = new CategoryCategory();
        $form = $this->createCreateForm($entity, $category);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $categoryEntity = $em->getRepository('CoreCategoryBundle:Category')->find($category);
            if (!$categoryEntity) {
                throw $this->createNotFoundException('Unable to find Category entity.');
            }
            $entity->setCategory($categoryEntity);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('category'));
        }

        return $this->render('CoreGoogleBundle:CategoryCategory:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'category' => $category
        ));
    }

    /**
     * Creates a form to create a CategoryCategory entity.
     *
     * @param CategoryCategory $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(CategoryCategory $entity, $category)
    {
        $form = $this->createForm(new CategoryCategoryType(), $entity, array(
            'action' => $this->generateUrl('googlecategorycategory_create', array('category' => $category)),
            'method' => 'POST',
            'locale' => $this->getRequest()->get('_locale', 'sk'),
        ));

        //$form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new CategoryCategory entity.
     *
     */
    public function newAction($category)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CoreGoogleBundle:CategoryCategory')->findOneByCategory($category);

        if ($entity) {
            return $this->redirect($this->generateUrl('googlecategorycategory_edit', array('id' => $entity->getId())));
        }
        
        $entity = new CategoryCategory();
        $form   = $this->createCreateForm($entity, $category);

        return $this->render('CoreGoogleBundle:CategoryCategory:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'category' => $category
        ));
    }

    /**
     * Finds and displays a CategoryCategory entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CoreGoogleBundle:CategoryCategory')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find CategoryCategory entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('CoreGoogleBundle:CategoryCategory:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing CategoryCategory entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CoreGoogleBundle:CategoryCategory')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find CategoryCategory entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('CoreGoogleBundle:CategoryCategory:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a CategoryCategory entity.
    *
    * @param CategoryCategory $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(CategoryCategory $entity)
    {
        $form = $this->createForm(new CategoryCategoryType(), $entity, array(
            'action' => $this->generateUrl('googlecategorycategory_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'locale' => $this->getRequest()->get('_locale', 'sk'),
        ));

        //$form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing CategoryCategory entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CoreGoogleBundle:CategoryCategory')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find CategoryCategory entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('googlecategorycategory_edit', array('id' => $id)));
        }

        return $this->render('CoreGoogleBundle:CategoryCategory:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a CategoryCategory entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('CoreGoogleBundle:CategoryCategory')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find CategoryCategory entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('category'));
    }

    /**
     * Creates a form to delete a CategoryCategory entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('googlecategorycategory_delete', array('id' => $id)))
            ->setMethod('DELETE')
            //->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
