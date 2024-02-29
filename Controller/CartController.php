<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\Product;
use App\Form\OrderType;
use App\Service\CartManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Controller managing the cart operations.
 */
class CartController extends AbstractController
{
    /**
     * Displays the contents of the cart.
     *
     * @Route("/cart", name="cart")
     *
     * @param CartManager $cartManager The cart manager service.
     * @return Response The response containing the cart view.
     */
    public function index(CartManager $cartManager)
    {
        $order = new Orders();
        $orderForm = $this->createForm(OrderType::class, $order);
        return $this->render('cart/index.html.twig', [
            'cart' => $cartManager->getCart(),
            'orderForm' => $orderForm->createView()
        ]);
    }

    /**
     * Removes a product from the cart.
     *
     * @Route("/remove-from-cart/{product}", name="remove_from_cart")
     *
     * @param Product $product The product to remove.
     * @param CartManager $cartManager The cart manager service.
     * @return Response A redirect response to the cart.
     */
    public function remove(Product $product, CartManager $cartManager)
    {
        $cartManager->remove($product);
        return $this->redirectToRoute('cart');
    }

    /**
     * Empties the cart.
     *
     * @Route("/empty-cart/", name="empty_cart")
     *
     * @param Request $request The request object.
     * @param CartManager $cartManager The cart manager service.
     * @return Response A redirect response to the referring page.
     */
    public function emptyCart(Request $request, CartManager $cartManager)
    {
        $referer = $request->headers->get('referer');
        $cartManager->emptyCart();
        return $this->redirect($referer);
    }

    /**
     * Adds a product to the cart.
     *
     * @Route("/add-to-cart/{product}", name="add_to_cart")
     *
     * @param Product $product The product to add.
     * @param Request $request The request object.
     * @param CartManager $cartManager The cart manager service.
     * @return Response A redirect response to the cart.
     */
    public function add(Product $product, Request $request, CartManager $cartManager)
    {
        $cartManager->add($product, $request->get('quantity'));
        return $this->redirectToRoute('cart');
    }

    /**
     * Displays an article page for a product.
     *
     * @Route("/article/{productid}", name="article")
     * @ParamConverter("product", options={"mapping": {"productid": "id"}})
     *
     * @param Product $product The product entity.
     * @return Response The response containing the article view.
     */
    public function article(Product $product)
    {
        return $this->render('main/article.html.twig', [
            'product' => $product,
        ]);
    }
}
