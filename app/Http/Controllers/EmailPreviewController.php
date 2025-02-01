<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmailPreviewController extends Controller
{
    public function __invoke() {
        request()->validate(rules: [
            'customer' => ['required', 'string'],
            'email' => ['required', 'email'], #Solo le ponermos email para que ponga las validaciones correspondientes a la estructura del email
            'payment_method' => ['required', 'in:1,2,3'], #El in permite validar datos enteros, puede validar datos estaticos pero tambien dinamicos
            'products' => ['required', 'array'], #Le decimos que solo va a aceptar arreglos, sino no me importa
            'products.*.name' => ['required', 'string', 'max:50'], #En producto pueden venir dentro de producto, pero lo vamos a tratar de formas diferentes
            'products.*.price' => ['required', 'numeric', 'gt:0'], #el .*. me permite acceder al atributo dentro de ese arreglo para no fregar lo demás
            'products.*.quantity' => ['required', 'integer', 'gte:1'], 
        ]);
        
        $request = request()->all(); #.validated(); Me va a traer todos los datos que pasaron la validación, si no no me retorna nada

        $products = $request['products']; // Extraemos los productos
        $total = 0;
        
        // Calculamos el total
        foreach ($products as $product) {
            $total += $product['price'] * $product['quantity'];
        }

        $data =[
            'customer' => $request['customer'],
            'datetime' =>now()->format('Y-m-d H:i:s'),
            'email' => $request['email'],
            'order_number' => 'RB' .now()->format('Y').now()->format('m').'-'.rand(1,100),
            'payment_method' => match ($request['payment_method']) {
                1 => 'Transferencia',
                2 => 'Contraentrega',
                3 => 'Tarjeta de credito',
            },
            'order_status' => match($request['payment_method']){
                1=> 'Pendiente de revisión',
                2=> 'En proceso',
                3=> 'En proceso',
            },
            'products' => $products,
            'total' => $total,
        ];


        return view('EmailPreview', $data);
    }
}
