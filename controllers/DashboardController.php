<?php

namespace Controllers;

use MVC\Router;
use Model\Usuario;
use Model\Proyecto;

class DashboardController {


    public static function index(Router $router){
        
        session_start();
        isAuth();

        $id = $_SESSION['id'];
        $proyectos = Proyecto::belongsTo('propietarioId', $id);

        $router->render('dashboard/index', [
           'titulo' => 'Proyectos',
           'proyectos' => $proyectos
        ]);
    }

    public static function crear_proyecto(Router $router){
        
        session_start();
        isAuth();
        $alertas = [];
       

        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            $proyecto = new Proyecto($_POST);

            $alertas = $proyecto->validarProyecto();
        
            if(empty($alertas)){

                //Generar una URL unica
                $hash = md5(uniqid());
                $proyecto->url = $hash;

                //Almacenar el creador del proyecto
                $proyecto->propietarioId = $_SESSION['id'];

                //Guardar el proyecto
                $proyecto->guardar();

                //Redireccionar
                header('Location: /proyecto?url=' . $proyecto->url);

            }
        }

        $router->render('dashboard/crear-proyecto', [
           'titulo' => 'Crear Proyecto',
           'alertas' => $alertas
        ]);
    }

    public static function proyecto(Router $router){
        
        session_start();
        isAuth();

        $token = $_GET['url'];

        if(!$token) header('Location: /dashboard');

        $proyecto = Proyecto::where('url', $token);

        if($proyecto->propietarioId !== $_SESSION['id']){
            header('Location: /dashboard');
        }

        if(!$proyecto) header('Location: /dashboard');

        $router->render('dashboard/proyecto', [
           'titulo' => $proyecto->proyecto
        ]);
    }

    public static function perfil(Router $router){
        session_start();
        isAuth();

        $alertas = [];
        $usuario = Usuario::find($_SESSION['id']);
        
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario->sincronizar($_POST);

            $alertas = $usuario->validar_perfil();

            if(empty($alertas)){

                $existeUsuario = Usuario::where('email', $usuario->email);

                if($existeUsuario && $existeUsuario->id !== $usuario->id){
                    //error
                    Usuario::setAlerta('error', 'Email no válido, ya pertenece a otra cuenta');
                    $alertas = $usuario->getAlertas();

                }else{
                $resultado = $usuario->guardar();
                    
                    //asignar valores a la barra
                    $_SESSION['nombre'] = $usuario->nombre;
                    $_SESSION['email'] = $usuario->email;

                    Usuario::setAlerta('exito', 'Gurdado Correctamente');
                    $alertas = $usuario->getAlertas();
                }             
            }
        }

        $router->render('dashboard/perfil', [
           'titulo' => 'Perfil',
           'alertas' => $alertas,
           'usuario' => $usuario
        ]);
    }

     public static function cambiar_password(Router $router){
        session_start();
        isAuth();

        $alertas = [];
        
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
           $usuario = Usuario::find($_SESSION['id']);

           $usuario->sincronizar($_POST);

           $alertas = $usuario->nuevo_password();

            if(empty($alertas)){
                $resultado = $usuario->comprobar_password();

                if($resultado){
                    //asignar nuevo password
                    $usuario->password = $usuario->password_nuevo;
                    //eliminar propiedades
                    unset($usuario->password_actual);
                    unset($usuario->password_nuevo);
                    //hash
                    $usuario->hashPassword();
                    //actualizar
                    $resultado = $usuario->guardar();
                    if($resultado){
                        Usuario::setAlerta('exito', 'Password Guardado Correctamente');
                        $alertas = $usuario->getAlertas();
                    }

                }else{
                    Usuario::setAlerta('error', 'Password Actual Incorrecto');
                    $alertas = $usuario->getAlertas();
                }
                            
            }

        }

        $router->render('dashboard/cambiar-password', [
           'titulo' => 'Cambiar Password',
           'alertas' => $alertas
        ]);
    }
}