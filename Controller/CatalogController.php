<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller managing catalog operations.
 *
 * @Route("/catalog")
 */
class CatalogController extends AbstractController
{
     /**
     * Displays the shop page with categories.
     *
     * @Route("/shop", name="catalog_shop")
     *
     * @param ProductRepository $productRepository The product repository.
     * @return Response The response containing the shop page.
     */
    public function shop(ProductRepository $productRepository): Response
    {
        $categories = $productRepository->findAllCategories();
        return $this->render('catalog/shop.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * Displays products for a specific category.
     *
     * @Route("/products/{category}", name="catalog_products")
     *
     * @param ProductRepository $productRepository The product repository.
     * @param Request $request The request object.
     * @return Response The response containing the products for the category.
     */
    public function products(ProductRepository $productRepository, Request $request): Response
    {
        $category = $request->get('category');
        $products = $productRepository->findProductsByCategory($category);

        return $this->render('catalog/index.html.twig', [
            'products' => $products
        ]);
    }

    /**
     * Displays details of a specific product.
     *
     * @Route("/{id}", name="catalog_product")
     *
     * @param Product $product The product entity.
     * @return Response The response containing the product details.
     */
    public function product(Product $product): Response
    {
        return $this->render('catalog/product.html.twig', [
            'product' => $product
        ]);
    }

    /**
     * Adds a product to the cart.
     *
     * @Route("/add-to-cart/{id}", name="catalog_add_to_cart")
     *
     * @param Product $product The product entity.
     * @param Request $request The request object.
     * @return Response A redirect response to the cart page.
     */
    public function addToCart(Product $product, Request $request): Response
    {
        $cart = $request->getSession()->get('cart', []);
        $cart[] = [
            'id' => $product->getId(),
            'model' => $product->getModel(),
            'price' => $product->getPrice(),
            'quantity' => $request->get('quantity', 1)
        ];
        $request->getSession()->set('cart', $cart);

        return $this->redirectToRoute('catalog_cart');
    }

     /**
     * Displays the cart page.
     *
     * @Route("/cart", name="catalog_cart")
     *
     * @param Request $request The request object.
     * @return Response The response containing the cart page.
     */
    public function cart(Request $request): Response
    {
        $cart = $request->getSession()->get('cart', []);

        return $this->render('catalog/cart.html.twig', [
            'cart' => $cart
        ]);
    }

    /**
     * Removes a product from the cart.
     *
     * @Route("/remove-from-cart/{id}", name="catalog_remove_from_cart")
     *
     * @param Product $product The product entity.
     * @param Request $request The request object.
     * @return Response A redirect response to the cart page.
     */
    public function removeFromCart(Product $product, Request $request): Response
    {
        $cart = $request->getSession()->get('cart', []);
        foreach ($cart as $key => $item) {
            if ($item['id'] === $product->getId()) {
                unset($cart[$key]);
                break;
            }
        }
        $request->getSession()->set('cart', $cart);

        return $this->redirectToRoute('catalog_cart');
    }

    /**
     * Processes the order and displays the order confirmation page.
     *
     * @Route("/order", name="catalog_order")
     *
     * @param Request $request The request object.
     * @return Response The response containing the order confirmation page.
     */
    public function order(Request $request): Response
    {
        // Process order here
        $request->getSession()->remove('cart');

        return $this->render('catalog/order.html.twig');
    }

    /**
     * Displays categories below a given category.
     *
     * @Route("/categories/{id}", name="catalog_categories")
     *
     * @param CategoryRepository $categoryRepository The category repository.
     * @param int $id The category ID.
     * @return Response The response containing the categories.
     */
    public function categories(CategoryRepository $categoryRepository, $id): Response
    {
        $categories = $categoryRepository->findCategoriesBelow($id);
        $selectedCategory = $categoryRepository->find($id);

        return $this->render('catalog/categories.html.twig', [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory
        ]);
    }

    /**
     * Displays categories below a given category with pagination.
     *
     * @Route("/categories-paged/{id}", name="catalog_categories_paged")
     *
     * @param CategoryRepository $categoryRepository The category repository.
     * @param ProductRepository $productRepository The product repository.
     * @param int $id The category ID.
     * @param Request $request The request object.
     * @return Response The response containing the paginated categories.
     */
    public function categoriesPaged(CategoryRepository $categoryRepository, ProductRepository $productRepository,
                                    $id, Request $request): Response
    {
        $query = $productRepository->findCategoriesBelowPaged($id, $request);
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            2
        );
        $pagination->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
        $selectedCategory = $categoryRepository->find($id);

        return $this->render('catalog/categories_paged.html.twig', [
            'pagination' => $pagination,
            'selectedCategory' => $selectedCategory
        ]);
    }
}
