<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Services\AuthService;
use App\Services\InstanceService;
use Exception;
use ReflectionException;

/**
 * CONTROLLER SEM PROTEÇÃO POR SENHA
 */
class Auth extends BaseController
{
    /**
     * ROUTE LOGIN
     */
    public function index(): string
    {
        //
        return view('admin/login/home');
    }

    public function signup(): string
    {
        return view('admin/login/signup');
    }

    /**
     * ROUTE POST LOGIN SIGNUP
     */
    public function newuser(): void
    {
        //print_r($this->request->getPost());
    }

    /**
     * ROUTE POST LOGIN
     */
    public function auth(): \CodeIgniter\HTTP\RedirectResponse
    {
        $userModel   = new UserModel();
        $input       = $this->request->getPost();
        $authService = new AuthService($input, $userModel);
        try {

            $authenticated = $authService->authenticate();

            if ($authenticated) {
                $instanceService = new InstanceService();
                $instanceService->verifyPlan();
                return redirect()->to(site_url('dashboard'));
            }

            return redirect()->to(site_url('login'));

        }catch (ReflectionException $e){
            // Trata erros de autenticação
            $errorMessage = $e->getMessage();
            $this->session->setFlashdata('error', $errorMessage);
            // Redirecionar de volta para a página anterior
            return redirect()->back();
        } catch (Exception $e) {
            // Trata erros de autenticação
            $errorMessage = $e->getMessage();
            $this->session->setFlashdata('error', $errorMessage);
            // Redirecionar de volta para a página anterior
            return redirect()->back();
            // Exiba a mensagem de erro para o usuário ou realize outras ações necessárias
        }

        /** @noinspection PhpUnreachableStatementInspection */
        return redirect()->to(site_url('dashboard'));
    }
}
