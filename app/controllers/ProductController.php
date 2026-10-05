<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ProductController extends Controller {
    public function __construct()
    {
        parent::__construct();
        $this->call->database();
        $this->call->library('api');
        $this->call->model('ProductModel');
    }

    // GET /api/products
    public function index() {
        $this->api->require_method('GET');
        $this->api->require_jwt();

        $products = $this->ProductModel->all();
        $this->api->respond(['data' => $products]);
    }

    // POST /api/products
    public function store() {
        $this->api->require_method('POST');
        $payload = $this->api->require_jwt();

        if (!in_array('write', $payload['scopes'] ?? [])) {
            $this->api->respond_error('Forbidden: write access required.', 403);
        }

        $data = $this->api->body();

        if (empty($data['product_name']) || !isset($data['price']) || !isset($data['quantity'])) {
            $this->api->respond_error('product_name, price, and quantity are required.', 422);
        }

        $insert = [
            'product_name' => $data['product_name'],
            'description'  => $data['description'] ?? '',
            'price'        => $data['price'],
            'quantity'     => $data['quantity'],
        ];

        $id = $this->ProductModel->insert($insert);
        $this->api->respond(['message' => 'Product created.', 'id' => $id], 201);
    }

    // PUT/PATCH /api/products/{id}
    public function update($id) {
        $this->api->require_method($_SERVER['REQUEST_METHOD']); // accepts PUT or PATCH as sent
        $payload = $this->api->require_jwt();

        if (!in_array('write', $payload['scopes'] ?? [])) {
            $this->api->respond_error('Forbidden: write access required.', 403);
        }

        $existing = $this->ProductModel->find($id);
        if (!$existing) {
            $this->api->respond_error('Product not found.', 404);
        }

        $data = $this->api->body();

        $update = [
            'product_name' => $data['product_name'] ?? $existing['product_name'],
            'description'  => $data['description'] ?? $existing['description'],
            'price'        => $data['price'] ?? $existing['price'],
            'quantity'     => $data['quantity'] ?? $existing['quantity'],
        ];

        $this->ProductModel->update($id, $update);
        $this->api->respond(['message' => 'Product updated.']);
    }

    // DELETE /api/products/{id}
    public function delete($id) {
        $this->api->require_method('DELETE');
        $payload = $this->api->require_jwt();

        if (!in_array('delete', $payload['scopes'] ?? [])) {
            $this->api->respond_error('Forbidden: delete access required.', 403);
        }

        $existing = $this->ProductModel->find($id);
        if (!$existing) {
            $this->api->respond_error('Product not found.', 404);
        }

        $this->ProductModel->delete($id);
        $this->api->respond(['message' => 'Product deleted.']);
    }
}