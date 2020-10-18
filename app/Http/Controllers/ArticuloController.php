<?php

namespace App\Http\Controllers;

use App\Articulo;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ArticuloController extends Controller
{
    public function index(Request $request)
    {
        $token     = $request->header('Authorization');
        $usuarios  = User::all();
        $json      = null;
        foreach ($usuarios as $user) {
            if ("Basic " . base64_encode($user->id_cliente . ":" . $user->secret_cliente) == $token) {
                // $articulos = Articulo::all();                

                if (isset($_GET['page'])) {
                    $articulos = DB::table('articulo')->join('users', 'articulo.id_user', '=', 'users.id')->select('articulo.*', 'users.id', 'users.name', 'users.apellidos', 'users.email')->paginate(10);
                } else {
                    $articulos = DB::table('articulo')->join('users', 'articulo.id_user', '=', 'users.id')->select('articulo.*', 'users.id', 'users.name', 'users.apellidos', 'users.email')->get();
                }
                if (empty($articulos)) {

                    $json = json_encode([
                        'status'  => 200,
                        'detalle' => 'No articulos para mostrar',
                    ], true);
                } else {

                    $json = json_encode(['status' => 200, 'cantidad de registros' => count($articulos), 'articulos' => $articulos], true);
                }
                break;
            } else {
                $json = json_encode([
                    'status'  => 403,
                    'detalle' => 'No tiene Autorización',
                ], true);
            }
        }
        return $json;
    }
    public function store(Request $request)
    {
        $token     = $request->header('Authorization');
        $articulos = Articulo::all();
        $usuarios  = User::all();
        $json      = null;
        foreach ($usuarios as $user) {
            if ("Basic " . base64_encode($user->id_cliente . ":" . $user->secret_cliente) == $token) {
                $datos = array(
                    'categoria'       => $request->input('categoria'),
                    'titulo'          => $request->input('titulo'),
                    'descripcion'     => $request->input('descripcion'),
                    'palabras_claves' => $request->input('palabras_claves'),
                    'ruta'            => $request->input('ruta'),
                    'contenido'       => $request->input('contenido'),
                    'img'             => $request->input('img'),
                );
                $validar_art = Validator::make($datos, [
                    'categoria'       => ['required', 'numeric'],
                    'titulo'          => ['required', 'unique:articulo'],
                    'descripcion'     => ['required', 'max:255'],
                    'palabras_claves' => ['required', 'max:255'],
                    'ruta'            => ['required', 'unique:articulo'],
                    'contenido'       => ['required', 'max:1000'],
                    'img'             => ['required', 'max:255'],
                ]);
                if ($validar_art->fails()) {
                    $json = json_encode([
                        'status'  => 200,
                        'detalle' => 'Error en los campos, mal llenados y/o vacios',
                        'errors'  => $validar_art->errors(),
                    ], true);
                    break;
                } else {
                    $article                  = new Articulo();
                    $article->id_categoria    = $datos['categoria'];
                    $article->titulo          = $datos['titulo'];
                    $article->descripcion     = $datos['descripcion'];
                    $article->palabras_claves = json_encode(explode(',', $datos['palabras_claves']));
                    $article->ruta            = $datos['ruta'];
                    $article->contenido       = $datos['contenido'];
                    $article->img             = $datos['img'];
                    $article->id_user         = $user->id;
                    if ($article->save() > 0) {
                        $json = json_encode([
                            'status'   => 200,
                            'detalle'  => 'Articulo registrado exitosamente',
                            'articulo' => $datos,
                        ], true);
                        break;
                    } else {
                        $json = json_encode([
                            'status'  => 404,
                            'detalle' => 'Error la registrar articulo, intente de nuevo',
                        ], true);
                        break;
                    }
                }
            }
        }
        return $json;
    }

    public function show(Request $request, $id)
    {
        $token     = $request->header('Authorization');
        $usuarios  = User::all();
        $json      = null;
        foreach ($usuarios as $user) {
            if ("Basic " . base64_encode($user->id_cliente . ":" . $user->secret_cliente) == $token) {
                $articulo = Articulo::where('id_user', $user->id)->where('id', $id)->first();
                if (!empty($articulo)) {
                    $json = json_encode([
                        'status'   => 200,
                        'articulo' => $articulo,
                    ], true);
                    break;
                } else {
                    $json = json_encode([
                        'status'  => 404,
                        'detalle' => 'No tiene ningun articulo creado',
                    ], true);
                    break;
                }
            }
        }
        return $json;
    }
    public function update(Request $request, $id)
    {
        $token     = $request->header('Authorization');
        $articulos = Articulo::all();
        $usuarios  = User::all();
        $json      = null;
        foreach ($usuarios as $user) {
            if ("Basic " . base64_encode($user->id_cliente . ":" . $user->secret_cliente) == $token) {
                $datos = array(
                    'categoria'       => $request->input('categoria'),
                    'titulo'          => $request->input('titulo'),
                    'descripcion'     => $request->input('descripcion'),
                    'palabras_claves' => $request->input('palabras_claves'),
                    'ruta'            => $request->input('ruta'),
                    'contenido'       => $request->input('contenido'),
                    'img'             => $request->input('img'),
                );
                $validar_art = Validator::make($datos, [
                    'categoria'       => ['required', 'numeric'],
                    'titulo'          => ['required'],
                    'descripcion'     => ['required', 'max:255'],
                    'palabras_claves' => ['required', 'max:255'],
                    'ruta'            => ['required'],
                    'contenido'       => ['required', 'max:1000'],
                    'img'             => ['required', 'max:255'],
                ]);
                if ($validar_art->fails()) {
                    $json = json_encode([
                        'status'  => 200,
                        'detalle' => 'Error en los campos, mal llenados y/o vacios',
                        'errors'  => $validar_art->errors(),
                    ], true);
                    break;
                } else {
                    $art = Articulo::where('id_user', $user->id)->where('id', $id)->first();
                    if (!empty($art)) {
                        $article = [
                            'id_categoria'    => $datos['categoria'],
                            'titulo'          => $datos['titulo'],
                            'descripcion'     => $datos['descripcion'],
                            'palabras_claves' => json_encode(explode(',', $datos['palabras_claves'])),
                            'ruta'            => $datos['ruta'],
                            'contenido'       => $datos['contenido'],
                            'img'             => $datos['img'],
                            'id_user'         => $user->id,
                        ];
                        if (Articulo::where('id', $art->id)->update($article) > 0) {
                            $json = json_encode([
                                'status'   => 200,
                                'detalle'  => 'Articulo actualizado exitosamente',
                                'articulo' => $datos,
                            ], true);
                            break;
                        } else {
                            $json = json_encode([
                                'status'  => 404,
                                'detalle' => 'Error la actualizar articulo, intente de nuevo',
                            ], true);
                            break;
                        }
                    } else {
                        $json = json_encode([
                            'status'  => 403,
                            'detalle' => 'No tiene permiso para editar el articulo',
                        ], true);
                        break;
                    }
                }
            }
        }
        return $json;
    }
    public function destroy(Request $request, $id)
    {
        $token     = $request->header('Authorization');
        $usuarios  = User::all();
        $json      = null;
        foreach ($usuarios as $user) {
            if ("Basic " . base64_encode($user->id_cliente . ":" . $user->secret_cliente) == $token) {
                $article = Articulo::where('id_user', $user->id)->where('id', $id)->first();
                if (!empty($article)) {
                    if (Articulo::where('id_user', $user->id)->where('id', $id)->delete() > 0) {
                        $json = json_encode([
                            'status'  => 200,
                            'detalle' => 'Articulo eliminado con exito',
                        ], true);
                        break;
                    } else {
                        $json = json_encode([
                            'status'  => 404,
                            'detalle' => 'Se produjo un error al intentar eliminar el registro',
                        ], true);
                        break;
                    }
                } else {
                    $json = json_encode([
                        'status'  => 403,
                        'detalle' => 'No tiene permiso para eliminar el articulo',
                    ], true);
                    break;
                }
            }
        }
        return $json;
    }
}
