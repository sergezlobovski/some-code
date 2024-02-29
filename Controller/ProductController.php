<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller managing product operations.
 *
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * Lists all products.
     *
     * @Route("/", name="product_index", methods={"GET"})
     *
     * @param ProductRepository $productRepository The product repository.
     * @return Response The response containing the list of products.
     */
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();
        return $this->render('product/index.html.twig', ['products' => $products]);
    }

    /**
     * Creates a new product.
     *
     * @Route("/new", name="product_new", methods={"GET","POST"})
     *
     * @param Request $request The request object.
     * @return Response The response containing the form for creating a new product or redirecting to the product index.
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a specific product.
     *
     * @Route("/{id}", name="product_show", methods={"GET"})
     *
     * @param Product $product The product entity.
     * @return Response The response containing the details of the product.
     */
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', ['product' => $product]);
    }

    /**
     * Edits a specific product.
     *
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"})
     *
     * @param Request $request The request object.
     * @param Product $product The product entity.
     * @return Response The response containing the form for editing the product or redirecting to the product index.
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_edit', ['id' => $product->getId()]);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a specific product.
     *
     * @Route("/{id}", name="product_delete", methods={"DELETE"})
     *
     * @param Request $request The request object.
     * @param Product $product The product entity.
     * @return Response A redirect response to the product index.
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('product_index');
    }
}
