<?php
namespace App\Controller;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ProductController extends Controller
{
	/**
	*
	* @Route ("/product", name="product_index")
	*
	*/
	public function index()
	{
		$products = $this->getDoctrine()->getRepository(Product::class)->findAll();

		return $this->render("Product/index.html.twig", ['products' => $products]);
	}

	/**
	*
	* @Route ("/product/create", name="product_create")
	*
	*/
	public function create(Request $request)
	{
		$product = new Product();
		
		$form = $this->createProductForm($product);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$em = $this->getDoctrine()->getManager();
			$em->persist($product);
			$em->flush();

			$this->addFlash('success', 'Le produit à bien été sauvegardé.');

			return $this->redirectToRoute('product_read', ['id' => $product->getId()]);
		}

		return $this->render('Product/create.html.twig', ['form' => $form->createView(),]);
	}

	/**
	*
	* @Route ("/product/{id}", name="product_read")
	*
	*/
	public function read(Request $request)
	{
		$product = $this->findProduct($request);	
		return $this->render('Product/read.html.twig',['product' => $product]);
	}

	/**
	*
	* @Route ("/product/{id}/delete", name="product_delete")
	*
	*/
	public function delete(Request $request)
	{
		$product = $this->findProduct($request);
		$form = $this->createFormBuilder()->add('confirm', Type\CheckboxType::class, [ 'label' => 'Confirmer la supression ?'])->getForm();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid())
		{
			$em = $this->getDoctrine()->getManager();
			$em->remove($product);
			$em->flush();

			$this->addFlash('success', 'Le produit a bien été supprimé.');
			return $this->redirectToRoute('product_index');
		}
		return $this->render('Product/delete.html.twig', ['form' => $form->createView()]);
	}

	/**
	*
	* @Route ("/product/{id}/update", name="product_update")
	*
	*/
	public function update(Request $request)
	{
		$product = $this->findProduct($request);

		$form = $this->createProductForm($product);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$em = $this->getDoctrine()->getManager();
			$em->persist($product);
			$em->flush();

			return $this->redirectToRoute('product_index');
		}
		return $this->render('Product/update.html.twig',['form' => $form->createView()]);
	}



	private function createProductForm(Product $product)
	{
		return $this->createFormBuilder($product)
		->add('designation')
		->add('reference')
		->add('brand')
		->add('price')
		->add('stock')
		->add('active', Type\CheckboxType::class)
		->add('description')
		->add('submit', Type\SubmitType::class)
		->getForm();
	}


	public function findProduct(Request $request)
	{
		$repository = $this->getDoctrine()->getRepository(Product::class);

		$product = $repository->find($request->attributes->get('id'));

		if (null === $product)
		{
			throw $this->createNotFoundException("Produit non existant");
		}

		return $product;
	}
}