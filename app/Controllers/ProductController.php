<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Product;
use App\Models\Vyapari;
use function App\Core\flash;

final class ProductController extends Controller
{
    private $products;

    public function __construct()
    {
        $this->products = new Product();
    }

    public function index(): void
    {
        Auth::requireLogin();
        $this->view('products/index', ['title' => 'Products', 'products' => $this->products->withVyapari()]);
    }

    public function create(): void
    {
        Auth::requireLogin();
        $this->view('products/form', ['title' => 'New Product', 'product' => [], 'vyaparis' => (new Vyapari())->all('vyapari_name ASC')]);
    }

    public function store(): void
    {
        Auth::requireLogin();
        $this->products->create($this->payload());
        flash('success', 'Product saved.');
        $this->redirect('/products');
    }

    public function edit(): void
    {
        Auth::requireLogin();
        $this->view('products/form', ['title' => 'Edit Product', 'product' => $this->products->find((int) $_GET['id']), 'vyaparis' => (new Vyapari())->all('vyapari_name ASC')]);
    }

    public function update(): void
    {
        Auth::requireLogin();
        $this->products->update((int) $_POST['id'], $this->payload());
        flash('success', 'Product updated.');
        $this->redirect('/products');
    }

    public function delete(): void
    {
        Auth::requireLogin();
        $this->products->delete((int) $_POST['id']);
        flash('success', 'Product deleted.');
        $this->redirect('/products');
    }

    private function payload(): array
    {
        return [
            'vyapari_id' => ($_POST['vyapari_id'] ?? '') !== '' ? (int) $_POST['vyapari_id'] : null,
            'product_name' => trim($_POST['product_name'] ?? ''),
            'purity' => (float) ($_POST['purity'] ?? 0),
            'rate' => (float) ($_POST['rate'] ?? 0),
        ];
    }
}
