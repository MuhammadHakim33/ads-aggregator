<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_role('manajemen');
        $this->load->model('Product_model');
    }

    public function index()
    {
        $filters = [
            'q' => $this->input->get('q'),
            'category' => $this->input->get('category'),
            'is_active' => $this->input->get('is_active'),
        ];

        $data = [
            'title' => 'Products',
            'active_menu' => 'product',
            'filters' => $filters,
            'products' => $this->Product_model->get_all($filters),
        ];

        $this->render('product/index', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules([
                [
                    'field' => 'name',
                    'label' => 'Product Name',
                    'rules' => 'trim|required|max_length[255]',
                ],
                [
                    'field' => 'category',
                    'label' => 'Category',
                    'rules' => 'trim|required|in_list[content_marketing,banner_ads,social_media]',
                ],
                [
                    'field' => 'price',
                    'label' => 'Price',
                    'rules' => 'trim|required|numeric|greater_than[0]',
                ],
                [
                    'field' => 'price_model',
                    'label' => 'Price Model',
                    'rules' => 'trim|required|in_list[fixed,cpm]',
                ],
            ]);

            if ($this->form_validation->run() === TRUE) {
                $data = [
                    'name' => $this->input->post('name'),
                    'category' => $this->input->post('category'),
                    'price' => $this->input->post('price'),
                    'price_model' => $this->input->post('price_model'),
                    'is_active' => 1,
                ];

                $id = $this->Product_model->insert($data);

                if ($id) {
                    $this->session->set_flashdata('success', 'Product <strong>' . htmlspecialchars($data['name']) . '</strong> created successfully.');
                    redirect('product');
                    return;
                } else {
                    $this->session->set_flashdata('errors', '<p>Failed to create product. Please try again.</p>');
                }
            }
        }

        $data = [
            'title' => 'Create Product',
            'active_menu' => 'product',
        ];

        $this->render('product/create', $data);
    }

    public function edit($id)
    {
        $product = $this->Product_model->get_by_id($id);
        if (!$product) {
            $this->session->set_flashdata('errors', '<p>Product not found.</p>');
            redirect('product');
            return;
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules([
                [
                    'field' => 'name',
                    'label' => 'Product Name',
                    'rules' => 'trim|required|max_length[255]',
                ],
                [
                    'field' => 'category',
                    'label' => 'Category',
                    'rules' => 'trim|required|in_list[content_marketing,banner_ads,social_media]',
                ],
                [
                    'field' => 'price',
                    'label' => 'Price',
                    'rules' => 'trim|required|numeric|greater_than[0]',
                ],
                [
                    'field' => 'price_model',
                    'label' => 'Price Model',
                    'rules' => 'trim|required|in_list[fixed,cpm]',
                ],
                [
                    'field' => 'is_active',
                    'label' => 'Status',
                    'rules' => 'in_list[0,1]',
                ],
            ]);

            if ($this->form_validation->run() === TRUE) {
                $data = [
                    'name' => $this->input->post('name'),
                    'category' => $this->input->post('category'),
                    'price' => $this->input->post('price'),
                    'price_model' => $this->input->post('price_model'),
                    'is_active' => (int) $this->input->post('is_active'),
                ];

                $ok = $this->Product_model->update($id, $data);

                if ($ok !== FALSE) {
                    $this->session->set_flashdata('success', 'Product <strong>' . htmlspecialchars($data['name']) . '</strong> updated successfully.');
                    redirect('product');
                    return;
                } else {
                    $this->session->set_flashdata('errors', '<p>Failed to update product. Please try again.</p>');
                }
            }
        }

        $data = [
            'title' => 'Edit Product',
            'active_menu' => 'product',
            'product' => $product,
        ];

        $this->render('product/edit', $data);
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') {
            redirect('product');
        }

        $product = $this->Product_model->get_by_id($id);
        if (!$product) {
            $this->session->set_flashdata('errors', '<p>Product not found.</p>');
            redirect('product');
            return;
        }

        // Check if product is used by any contract_items
        $in_use = $this->db->where('product_id', $id)->count_all_results('contract_items');
        if ($in_use > 0) {
            $this->session->set_flashdata('errors', '<p>Cannot delete product <strong>' . htmlspecialchars($product->name) . '</strong> because it is used in one or more contracts. Deactivate it instead.</p>');
            redirect('product');
            return;
        }

        $ok = $this->Product_model->delete($id);

        if ($ok) {
            $this->session->set_flashdata('success', 'Product <strong>' . htmlspecialchars($product->name) . '</strong> deleted successfully.');
        } else {
            $this->session->set_flashdata('errors', '<p>Failed to delete product. Please try again.</p>');
        }

        redirect('product');
    }
}
