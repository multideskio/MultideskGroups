<?php

namespace App\Database\Seeds;

use App\Models\CompanyModel;
use App\Models\PlanModel;
use App\Models\SuperModel;
use App\Models\UserModel;
use CodeIgniter\Database\Seeder;
use ReflectionException;

class SuperAdmin extends Seeder
{
    public function run(): void
    {
        //
        helper('response');

        $sData = [
            'name'       => 'MultiDeskIo',
            'url_api_wa' => 'https://evo2.conect.app',
            'api_key_wa' => 'yi2f32pfkwfwavcc2y9penmh2rn9tiggv07pzjkl5wyig18jmq',
        ];

        $mSuper = new SuperModel();

        try {
            $idSuper = $mSuper->insert($sData);
        } catch (ReflectionException $e) {
            log_message('error', $e->getMessage());

            return;
        }

        echo "Super criado! \n";

        $cData = [
            'id_admin' => $idSuper,
            'name'     => 'Paulo Henrique',
            'company'  => 'MultiDesk',
            'email'    => 'igrsysten@gmail.com',
        ];

        $mCompany = new CompanyModel();

        try {
            $idCompany = $mCompany->insert($cData);
        } catch (ReflectionException $e) {
            log_message('error', $e->getMessage());

            return;
        }

        echo "Company criado!\n";

        $uData = [
            [
                'id_company' => $idCompany,
                'name'       => 'Paulo Henrique',
                'wa_number'  => '5562981154120',
                'email'      => 'contato@multidesk.io',
                'level'      => 'superadmin',
                'permission' => 1,
                'status'     => true,
                'password'   => password_hash('mudar@123', PASSWORD_BCRYPT),
                'token'      => randomSerial(),
            ],
            [
                'id_company' => $idCompany,
                'name'       => 'Paulo Henrique',
                'wa_number'  => '5562981154120',
                'email'      => 'igrsysten@gmail.com',
                'level'      => 'admin',
                'permission' => 2,
                'status'     => true,
                'password'   => password_hash('mudar@123', PASSWORD_BCRYPT),
                'token'      => randomSerial(),
            ],
        ];

        $mUser = new UserModel();

        try {
            $mUser->insertBatch($uData);
        } catch (ReflectionException $e) {
            log_message('error', $e->getMessage());

            return;
        }
        echo "Users Criados!\n";
        $pData = [
            'id_company'   => $idCompany,
            'id_user'      => 1,
            'num_instance' => 3,
            'valid_days'   => 365,
            'payday'       => date('Y-m-d'),
            'price'        => 0,
            'status'       => true,
            'size_files'   => 200,
        ];
        $mPlan = new PlanModel();
        try {
            $mPlan->insert($pData);
        } catch (ReflectionException $e) {
            log_message('error', $e->getMessage());
        }
        echo "Plano criado!\n\n";
        echo "Todas as ações foram realizadas com sucesso! \n";
    }
}
