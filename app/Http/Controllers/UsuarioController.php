<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    public function index()
    {
        $detalle = array('detalle' => 'No encontrado');

        echo json_encode($detalle, true);
    }

    public function store(Request $request)
    {
        $datos = array(
            'nombre'    => $request->input('nombre'),
            'apellidos' => $request->input('apellidos'),
            'email'     => $request->input('correo'),
        );
        $validator = Validator::make($datos, [
            'nombre'    => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return json_encode(array('status' => 404, 'detalle' => 'Registro con errores'), true);
        }
        $cliente_id     = str_replace('$', 'a', Hash::make($datos['nombre'] . $datos['apellidos'] . $datos['email'], ['rounds' => 12]));
        $cliente_secret = str_replace('$', 'o', Hash::make($datos['email'] . $datos['apellidos']));

        $user = new User();

        $user->name           = $datos['nombre'];
        $user->apellidos      = $datos['apellidos'];
        $user->email          = $datos['email'];
        $user->password       = $cliente_secret;
        $user->id_cliente     = $cliente_id;
        $user->secret_cliente = $cliente_secret;

        if ($user->save() > 0) {
            return json_encode(array(
                'status'       => 200,
                'detalle'      => 'Registro exitoso guarde sus datos de API',
                'credenciales' => [
                    'id_cliente'     => $cliente_id,
                    'secret_cliente' => $cliente_secret,
                ],

            ), true);
        } else {
            return json_encode(array(
                'status'  => 404,
                'detalle' => 'Error al registrarse en API intente de nuevo',
            ), true);
        }
    }
}
