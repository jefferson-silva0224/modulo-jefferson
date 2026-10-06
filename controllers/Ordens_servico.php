<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ordens_servico extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->database();
    }

    /**
     * Lista as ordens de serviço
     */
    public function index()
    {
        $data['title'] = 'Ordens de Serviço';

        $data['ordens'] = $this->db
            ->order_by('data_prevista', 'ASC')
            ->get(db_prefix() . 'ordens_servico')
            ->result();

        $this->load->view(
            'ordens_servico/manage',
            $data
        );
    }

    /**
     * Cria uma nova ordem de serviço
     */
    public function create()
    {
        $today = date('Y-m-d');

        /*
         * EMPRESAS / CLIENTES
         */
        $data['clientes'] = $this->db
            ->select('userid, company')
            ->where('active', 1)
            ->order_by('company', 'ASC')
            ->get(db_prefix() . 'clients')
            ->result();

        /*
         * TÉCNICOS E ENCARREGADOS
         */
        $data['tecnicos'] = $this->db
            ->select(
                db_prefix() . 'staff.staffid, ' .
                db_prefix() . 'staff.firstname, ' .
                db_prefix() . 'staff.lastname, ' .
                db_prefix() . 'roles.name as role_name'
            )
            ->from(db_prefix() . 'staff')
            ->join(
                db_prefix() . 'roles',
                db_prefix() . 'roles.roleid = ' .
                db_prefix() . 'staff.role',
                'left'
            )
            ->where(db_prefix() . 'staff.active', 1)
            ->where_in(
                db_prefix() . 'roles.name',
                [
                    'Técnico',
                    'Encarregado',
                    'Encarregado Técnico'
                ]
            )
            ->order_by(
                db_prefix() . 'staff.firstname',
                'ASC'
            )
            ->get()
            ->result();

        /*
         * SALVAR
         */
        if ($this->input->post()) {

            $post = $this->input->post();

            /*
             * Verifica empresa
             */
            $cliente = $this->db
                ->where(
                    'userid',
                    (int) $post['empresa']
                )
                ->where('active', 1)
                ->get(db_prefix() . 'clients')
                ->row();

            if (!$cliente) {

                set_alert(
                    'danger',
                    'A empresa selecionada não existe.'
                );

                redirect(
                    admin_url('ordens_servico/create')
                );
            }

            /*
             * Verifica técnico
             */
            $tecnico = $this->db
                ->select(
                    db_prefix() . 'staff.staffid, ' .
                    db_prefix() . 'staff.firstname, ' .
                    db_prefix() . 'staff.lastname'
                )
                ->from(db_prefix() . 'staff')
                ->join(
                    db_prefix() . 'roles',
                    db_prefix() . 'roles.roleid = ' .
                    db_prefix() . 'staff.role',
                    'left'
                )
                ->where(
                    db_prefix() . 'staff.staffid',
                    (int) $post['tecnico']
                )
                ->where(
                    db_prefix() . 'staff.active',
                    1
                )
                ->where_in(
                    db_prefix() . 'roles.name',
                    [
                        'Técnico',
                        'Encarregado',
                        'Encarregado Técnico'
                    ]
                )
                ->get()
                ->row();

            if (!$tecnico) {

                set_alert(
                    'danger',
                    'O técnico selecionado não existe ou não possui a função permitida.'
                );

                redirect(
                    admin_url('ordens_servico/create')
                );
            }

            /*
             * Nome do técnico
             */
            $nome_tecnico = trim(
                $tecnico->firstname . ' ' . $tecnico->lastname
            );


            /*
             * DATA REALIZADA
             */
            $data_realizada = !empty(
                $post['data_realizada']
            )
                ? $post['data_realizada']
                : null;


            /*
             * A data realizada não pode ser futura.
             */
            if (
                $data_realizada !== null
                &&
                $data_realizada > $today
            ) {

                set_alert(
                    'danger',
                    'A data realizada não pode ser uma data futura.'
                );

                redirect(
                    admin_url('ordens_servico/create')
                );
            }


            /*
             * STATUS
             *
             * Se existe data realizada,
             * a OS obrigatoriamente fica concluída.
             */
            if ($data_realizada !== null) {

                $status = 'Concluída';

            } else {

                $status = !empty($post['status'])
                    ? $post['status']
                    : 'Pendente';
            }


            /*
             * Insere a ordem
             */
            $this->db->insert(
                db_prefix() . 'ordens_servico',
                [
                    'empresa' => trim($cliente->company),

                    'tecnico' => $nome_tecnico,

                    'data_prevista' =>
                        $post['data_prevista'],

                    'data_realizada' =>
                        $data_realizada,

                    'status' =>
                        $status,

                    'observacao' =>
                        !empty($post['observacao'])
                            ? $post['observacao']
                            : null,

                    'valor' =>
                        isset($post['valor']) &&
                        $post['valor'] !== ''
                            ? $post['valor']
                            : null,

                    'datecreated' =>
                        date('Y-m-d H:i:s'),
                ]
            );

            set_alert(
                'success',
                'Ordem de serviço criada com sucesso.'
            );

            redirect(
                admin_url('ordens_servico')
            );
        }

        /*
         * Dados do formulário
         */
        $data['title'] =
            'Nova Ordem de Serviço';

        $data['today'] =
            $today;

        /*
         * O header/footer são carregados
         * pelo próprio form.php.
         */
        $this->load->view(
            'ordens_servico/form',
            $data
        );
    }


    /**
     * Edita uma ordem de serviço
     */
    public function edit($id)
    {
        $table =
            db_prefix() . 'ordens_servico';


        /*
         * Busca a ordem
         */
        $ordem = $this->db
            ->where(
                'id',
                (int) $id
            )
            ->get($table)
            ->row();

        if (!$ordem) {
            show_404();
        }


        /*
         * Ordem concluída não pode ser alterada
         * por usuários que não são administradores.
         */
        if (
            $ordem->status === 'Concluída' &&
            !is_admin()
        ) {

            access_denied(
                'Esta ordem de serviço já foi concluída e não pode mais ser alterada.'
            );
        }


        /*
         * EMPRESAS
         */
        $data['clientes'] = $this->db
            ->select(
                'userid, company'
            )
            ->where(
                'active',
                1
            )
            ->order_by(
                'company',
                'ASC'
            )
            ->get(
                db_prefix() . 'clients'
            )
            ->result();


        /*
         * TÉCNICOS
         */
        $data['tecnicos'] = $this->db
            ->select(
                db_prefix() . 'staff.staffid, ' .
                db_prefix() . 'staff.firstname, ' .
                db_prefix() . 'staff.lastname, ' .
                db_prefix() . 'roles.name as role_name'
            )
            ->from(
                db_prefix() . 'staff'
            )
            ->join(
                db_prefix() . 'roles',
                db_prefix() . 'roles.roleid = ' .
                db_prefix() . 'staff.role',
                'left'
            )
            ->where(
                db_prefix() . 'staff.active',
                1
            )
            ->where_in(
                db_prefix() . 'roles.name',
                [
                    'Técnico',
                    'Encarregado',
                    'Encarregado Técnico'
                ]
            )
            ->order_by(
                db_prefix() . 'staff.firstname',
                'ASC'
            )
            ->get()
            ->result();


        /*
         * SALVAR ALTERAÇÕES
         */
        if ($this->input->post()) {

            $post =
                $this->input->post();


            /*
             * DATA REALIZADA
             */
            $data_realizada =
                !empty(
                    $post['data_realizada']
                )
                    ? $post['data_realizada']
                    : null;


            /*
             * A data realizada não pode ser futura.
             */
            if (
                $data_realizada !== null
                &&
                $data_realizada > date('Y-m-d')
            ) {

                set_alert(
                    'danger',
                    'A data realizada não pode ser uma data futura.'
                );

                redirect(
                    admin_url(
                        'ordens_servico/edit/' . $id
                    )
                );
            }


            /*
             * STATUS
             *
             * Se existe data realizada,
             * a OS obrigatoriamente fica concluída.
             */
            if ($data_realizada !== null) {

                $status =
                    'Concluída';

            } else {

                $status =
                    !empty($post['status'])
                        ? $post['status']
                        : $ordem->status;
            }


            /*
             * Dados que podem ser alterados
             */
            $dados = [

                'data_realizada' =>
                    $data_realizada,

                'status' =>
                    $status,

                'observacao' =>
                    isset(
                        $post['observacao']
                    )
                        ? $post['observacao']
                        : $ordem->observacao,

                'valor' =>
                    isset($post['valor']) &&
                    $post['valor'] !== ''
                        ? $post['valor']
                        : null,
            ];


            /*
             * ALTERAR EMPRESA
             */
            if (
                staff_can(
                    'edit_company',
                    'ordens_servico'
                )
                &&
                isset(
                    $post['empresa']
                )
            ) {

                $cliente = $this->db
                    ->where(
                        'userid',
                        (int) $post['empresa']
                    )
                    ->where(
                        'active',
                        1
                    )
                    ->get(
                        db_prefix() . 'clients'
                    )
                    ->row();

                if (!$cliente) {

                    set_alert(
                        'danger',
                        'A empresa selecionada não existe.'
                    );

                    redirect(
                        admin_url(
                            'ordens_servico/edit/' . $id
                        )
                    );
                }

                $dados['empresa'] =
                    trim(
                        $cliente->company
                    );
            }


            /*
             * ALTERAR TÉCNICO E DATA PREVISTA
             */
            if (
                staff_can(
                    'manage_schedule',
                    'ordens_servico'
                )
            ) {

                if (
                    isset(
                        $post['tecnico']
                    )
                    &&
                    !empty(
                        $post['tecnico']
                    )
                ) {

                    $tecnico = $this->db
                        ->select(
                            db_prefix() . 'staff.staffid, ' .
                            db_prefix() . 'staff.firstname, ' .
                            db_prefix() . 'staff.lastname'
                        )
                        ->from(
                            db_prefix() . 'staff'
                        )
                        ->join(
                            db_prefix() . 'roles',
                            db_prefix() . 'roles.roleid = ' .
                            db_prefix() . 'staff.role',
                            'left'
                        )
                        ->where(
                            db_prefix() . 'staff.staffid',
                            (int) $post['tecnico']
                        )
                        ->where(
                            db_prefix() . 'staff.active',
                            1
                        )
                        ->where_in(
                            db_prefix() . 'roles.name',
                            [
                                'Técnico',
                                'Encarregado',
                                'Encarregado Técnico'
                            ]
                        )
                        ->get()
                        ->row();

                    if (!$tecnico) {

                        set_alert(
                            'danger',
                            'O técnico selecionado não existe.'
                        );

                        redirect(
                            admin_url(
                                'ordens_servico/edit/' . $id
                            )
                        );
                    }

                    $dados['tecnico'] =
                        trim(
                            $tecnico->firstname .
                            ' ' .
                            $tecnico->lastname
                        );
                }


                if (
                    isset(
                        $post['data_prevista']
                    )
                ) {

                    /*
                     * A data prevista não pode
                     * ser anterior a hoje.
                     */
                    if (
                        $post['data_prevista']
                        < date('Y-m-d')
                    ) {

                        set_alert(
                            'danger',
                            'A data prevista não pode ser anterior a hoje.'
                        );

                        redirect(
                            admin_url(
                                'ordens_servico/edit/' . $id
                            )
                        );
                    }

                    $dados['data_prevista'] =
                        $post['data_prevista'];
                }
            }


            /*
             * Atualiza
             */
            $this->db
                ->where(
                    'id',
                    (int) $id
                )
                ->update(
                    $table,
                    $dados
                );


            set_alert(
                'success',
                'Ordem de serviço atualizada.'
            );


            redirect(
                admin_url(
                    'ordens_servico'
                )
            );
        }


        /*
         * Dados da página
         */
        $data['title'] =
            'Editar Ordem de Serviço';

        $data['ordem'] =
            $ordem;

        $data['today'] =
            date('Y-m-d');


        /*
         * Carrega somente a view.
         */
        $this->load->view(
            'ordens_servico/form',
            $data
        );
    }


    /**
     * Exclui uma ordem de serviço
     */
    public function delete($id)
    {
        $this->db
            ->where(
                'id',
                (int) $id
            )
            ->delete(
                db_prefix() . 'ordens_servico'
            );


        set_alert(
            'success',
            'Ordem de serviço excluída.'
        );


        redirect(
            admin_url(
                'ordens_servico'
            )
        );
    }
}