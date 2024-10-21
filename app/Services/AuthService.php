<?php

namespace App\Services;

use App\Models\CompanyModel;
use App\Models\PlanModel;
use App\Models\UserModel;
use Exception;
use RuntimeException;

class AuthService
{
    protected array $inputs;
    protected UserModel $userModel;

    public function __construct(array $inputs, UserModel $userModel)
    {
        $this->inputs    = $inputs;
        $this->userModel = $userModel;
        helper('response');
    }

    public function authenticate(): bool
    {
        log_message('info', 'Auth iniciado');

        // Verificar se os campos foram preenchidos
        if (empty($this->inputs['username'])) {
            log_message('debug', 'Preencha o campo username');

            throw new RuntimeException(lang('General.errosLogin.notEmail'));
        }

        if (empty($this->inputs['password'])) {
            log_message('debug', 'Preencha o campo password');

            throw new RuntimeException(lang('General.errosLogin.notPassword'));
        }

        $email    = $this->inputs['username'];
        $password = $this->inputs['password'];
        $remember = !empty($this->inputs['remember']) ? 1 : 0;

        // Buscar usuário pelo email
        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            log_message('debug', 'Usuário não encontrado');

            throw new RuntimeException(lang('General.errosLogin.errorEmail'));
        }

        // Comparar a senha usando o hash armazenado no banco de dados
        if (!password_verify($password, $user['password'])) {
            throw new RuntimeException(lang('General.errosLogin.errorPassword'));
        }

        if ($remember) {
            // Configurar cookies para login automático
            set_cookie('user_email', $email, 60 * 60 * 24 * 30);
            set_cookie('user_token', password_hash($user['token'], PASSWORD_BCRYPT), 60 * 60 * 24 * 30);
            set_cookie('user_auto', $remember, 60 * 60 * 24 * 30);
        } else {
            // Remover cookies de login automático
            delete_cookie('user_email');
            delete_cookie('user_token');
            delete_cookie('user_auto');
        }

        try {
            log_message('info', 'Verificando plano..');
            $verifyPlan = $this->verifyPlan($user['id_company']);
        } catch (Exception $e) {
            throw new RuntimeException('Error: ' . $e->getMessage());
        }

        //Retorna dados do super admin
        $mCompany = new CompanyModel();
        $id       = $mCompany->select('id_admin')->find($user['id_company']);
        // Preparar dados para a sessão
        $dataSession = [
            'isConnected'   => true,
            'id'            => (int)$user['id'],
            'company'       => (int)$user['id_company'],
            'name'          => $user['name'],
            'fone'          => $user['wa_number'],
            'email'         => $user['email'],
            'permission'    => (int)$user['permission'],
            'status'        => (int)$user['status'],
            'image'         => $user['image'] ?: null,
            'control'       => $id['id_admin'],
            'daysRemaining' => $verifyPlan,
        ];

        // Gravar dados na sessão
        session()->set(['user' => $dataSession]);

        log_message('debug', 'Login successful');

        // Autenticação bem-sucedida
        return true;
    }

    private function verifyPlan($company): bool|int
    {
        $mPlan = new PlanModel();
        $build = $mPlan->where('id_company', $company)->first();

        if (!$build) {
            log_message('debug', lang('Validation.plan.0'));

            throw new RuntimeException(lang('Validation.plan.0'));
        }

        $row = $build;

        // Exemplo de uso
        $dataCompra        = $row['payday'];
        $diasParaAdicionar = $row['valid_days'];

        /*session()->set(['user' => [
            'vencimento' => add_days_to_purchase_date($dataCompra, $diasParaAdicionar),
        ]]);*/

        $dataValidade = add_days_to_purchase_date($dataCompra, $diasParaAdicionar);

        if (is_plan_expired($dataValidade)) {
            log_message('debug', lang('Validation.plan.vencido.1'));

            throw new RuntimeException(lang('Validation.plan.vencido.0'));
        }

        log_message('debug', 'Verificação de plano concluída.');

        return days_until_expiry($dataValidade);
    }

    public function createAccount(): void
    {
    }
}
