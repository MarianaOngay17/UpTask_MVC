<?php

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Model\Usuario;

class LoginController {

    public static function login(Router $router){

        if($_SERVER['REQUEST_METHOD'] === 'POST'){

        }

        $router->render('auth/login', [
            'titulo' => 'Iniciar Sesión'
        ]);

    }

    public static function logout(){
        echo "desde logout";
    }

    public static function crear(Router $router){

        $alertas = [];
        $usuario = new Usuario();

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            if(empty($alertas)){

                $existeUsuario = Usuario::where('email', $usuario->email);

                if($existeUsuario){
                    Usuario::setAlerta('error', 'El usuario ya está registrado');
                    $alertas = Usuario::getAlertas();
                }else{

                    //hash de password
                    $usuario->hashPassword();

                    //eliminar password2
                    unset($usuario->password2);

                    //generar token
                    $usuario->crearToken();

                    //crear un nuevo usuario
                    $resultado = $usuario->guardar();   
                    
                    //enviar email

                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();

                    if($resultado){
                        header('Location: /mensaje');
                    }
                }
            }
        }

        $router->render('auth/crear', [
            'titulo' => 'Crea tu cuenta en UpTask',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
        

    }

    public static function olvide(Router $router){
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if(empty($alertas)){
                //buscar usuario
                $usuario = Usuario::where('email', $usuario->email);
                
                if($usuario && $usuario->confirmado){
      
                    //generar nuevo token
                    $usuario->crearToken();
                    unset($usuario->password2);

                    //actualizar usuario
                    $usuario->guardar();

                    //enviar email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();
                    //alerta
                    Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email');

                }else{
                    Usuario::setAlerta('error', 'El usuario no existe o no está confirmado');
                }
            
            }
      
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide', [
            'titulo' => 'Olvide mi Password',
            'alertas' => $alertas
        ]);

    }

    public static function reestablecer(Router $router){

        $token = s($_GET['token']);
        $mostrar = true;
        if(!$token) header('Location: /');

        //identificar usuario con este token
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)){
            Usuario::setAlerta('error', 'Token no válido');
            $mostrar = false;
        }

        $alertas = Usuario::getAlertas();

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            //añadir el nuevo password

            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarPassword();

            if(empty($alertas)){
                //eliminar password2 y token
                unset($usuario->password2);
                $usuario->token = null;

                //hashear el nuevo password
                $usuario->hashPassword();

                //guardar el usuario
                $resultado = $usuario->guardar();
                if($resultado){
                    header('Location: /');
                }
            }
        }

        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer Password',
            'alertas' => $alertas,
            'mostrar' => $mostrar
        ]);

    }
    public static function mensaje(Router $router){

        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta Creada Exitosamente'
        ]);

    }
    public static function confirmar(Router $router){

        $token = s($_GET['token']);

        if(!$token) header('Location: /');

        //buscar usuario por su token
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)){
            Usuario::setAlerta('error', 'Token no válido');
        }else{
            //confirmar cuenta
            $usuario->confirmado = "1";
            $usuario->token = '';
            unset($usuario->password2);

            $usuario->guardar();

            Usuario::setAlerta('exito', 'Cuenta comprobada correctamente');
            
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/confirmar', [
            'titulo' => 'Confirma tu cuenta UpTask',
            'alertas' => $alertas
        ]);
    }
}