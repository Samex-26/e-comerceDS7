<?php

class InicioController extends Controller
{
    public function index(): void
    {
        $productoModel  = $this->model('Producto');
        $categoriaModel = $this->model('Categoria');

        $totalProductos  = count($productoModel->listarActivos());
        $totalCategorias = count($categoriaModel->listarTodas());

        $this->view('inicio/index', [
            'totalProductos'  => $totalProductos,
            'totalCategorias' => $totalCategorias,
        ]);
    }
}