<?php

namespace Core\GoogleBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Core\GoogleBundle\Entity\ProductCategory;
use Core\GoogleBundle\Form\ProductCategoryType;

/**
 * ProductCategory controller.
 *
 */
class ProductCategoryController extends Controller
{

    /**
     * Lists all ProductCategory entities.
     *
     */
    public function indexAction($category = null)
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->getRepository('CoreGoogleBundle:ProductCategory')->getProductCategoryQuery($category);
        $page = $this->getRequest()->get("page", 1);
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page/*page number*/,
            100 /*limit per page*/
        );

        return $this->render('CoreGoogleBundle:ProductCategory:index.html.twig', array(
            'entities' => $pagination,
            'category' => $category,
        ));
    }
    /**
     * Creates a new ProductCategory entity.
     *
     */
    public function createAction(Request $request, $product, $category = null)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CoreGoogleBundle:ProductCategory')->findOneByProduct($product);

        if ($entity) {
            return $this->redirect($this->generateUrl('googleproductcategory_edit', array('id' => $entity->getId(), 'category' => $category)));
        }
        
        $entity = new ProductCategory();
        $form = $this->createCreateForm($entity, $product, $category);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $productEntity = $em->getRepository('CoreProductBundle:Product')->find($product);
            if (!$productEntity) {
                throw $this->createNotFoundException('Unable to find Product entity.');
            }
            $entity->setProduct($productEntity);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('product', array('category' => $category)));
        }

        return $this->render('CoreGoogleBundle:ProductCategory:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'product' => $product,
            'category'  => $category,
        ));
    }

    /**
     * Creates a form to create a ProductCategory entity.
     *
     * @param ProductCategory $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(ProductCategory $entity, $product, $category = null)
    {
        $form = $this->createForm(new ProductCategoryType(), $entity, array(
            'action' => $this->generateUrl('googleproductcategory_create', array('category' => $category, 'product' => $product)),
            'method' => 'POST',
            'locale' => $this->getRequest()->get('_locale', 'sk'),
        ));

        //$form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new ProductCategory entity.
     *
     */
    public function newAction($product, $category = null)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CoreGoogleBundle:ProductCategory')->findOneByProduct($product);

        if ($entity) {
            return $this->redirect($this->generateUrl('googleproductcategory_edit', array('id' => $entity->getId(), 'category' => $category)));
        }
        
        $entity = new ProductCategory();
        $form   = $this->createCreateForm($entity, $product, $category);

        return $this->render('CoreGoogleBundle:ProductCategory:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'category' => $category,
            'product' => $product,
        ));
    }

    /**
     * Finds and displays a ProductCategory entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CoreGoogleBundle:ProductCategory')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProductCategory entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('CoreGoogleBundle:ProductCategory:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ProductCategory entity.
     *
     */
    public function editAction($id, $category = null)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CoreGoogleBundle:ProductCategory')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProductCategory entity.');
        }

        $editForm = $this->createEditForm($entity, $category);
        $deleteForm = $this->createDeleteForm($id, $category);

        return $this->render('CoreGoogleBundle:ProductCategory:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'category' => $category,
        ));
    }

    /**
    * Creates a form to edit a ProductCategory entity.
    *
    * @param ProductCategory $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(ProductCategory $entity, $category = null)
    {
        $form = $this->createForm(new ProductCategoryType(), $entity, array(
            'action' => $this->generateUrl('googleproductcategory_update', array('id' => $entity->getId(), 'category' => $category)),
            'method' => 'PUT',
            'locale' => $this->getRequest()->get('_locale', 'sk'),
        ));

        //$form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing ProductCategory entity.
     *
     */
    public function updateAction(Request $request, $id, $category = null)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CoreGoogleBundle:ProductCategory')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProductCategory entity.');
        }

        $deleteForm = $this->createDeleteForm($id, $category);
        $editForm = $this->createEditForm($entity, $category);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('googleproductcategory_edit', array('id' => $id)));
        }

        return $this->render('CoreGoogleBundle:ProductCategory:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'category' => $category,
        ));
    }
    /**
     * Deletes a ProductCategory entity.
     *
     */
    public function deleteAction(Request $request, $id, $category = null)
    {
        $form = $this->createDeleteForm($id, $category);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('CoreGoogleBundle:ProductCategory')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ProductCategory entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('product', array('category' => $category)));
    }

    /**
     * Creates a form to delete a ProductCategory entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id, $category = null)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('googleproductcategory_delete', array('id' => $id, 'category' => $category)))
            ->setMethod('DELETE')
            //->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
