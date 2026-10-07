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
     * com filtros de pesquisa.
     */
    public function index()
    {
        $data['title'] = 'Ordens de Serviço';


        /*
         * ==========================================
         * RECEBE OS FILTROS
         * ==========================================
         */

        $empresa = $this->input->get('empresa');

        $tecnico = $this->input->get('tecnico');

        $status = $this->input->get('status');

        $aviso = $this->input->get('aviso');

        $valor_min = $this->input->get('valor_min');

        $valor_max = $this->input->get('valor_max');

        $data_prevista_inicio =
            $this->input->get('data_prevista_inicio');

        $data_prevista_fim =
            $this->input->get('data_prevista_fim');

        $data_realizada_inicio =
            $this->input->get('data_realizada_inicio');

        $data_realizada_fim =
            $this->input->get('data_realizada_fim');


        /*
         * ==========================================
         * EMPRESAS PARA O FILTRO
         * ==========================================
         */

        $data['clientes'] = $this->db
            ->select('userid, company')
            ->where('active', 1)
            ->order_by('company', 'ASC')
            ->get(db_prefix() . 'clients')
            ->result();


        /*
         * ==========================================
         * TÉCNICOS PARA O FILTRO
         * ==========================================
         */

        $data['tecnicos'] = $this->db
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
         * ==========================================
         * DESCOBRE O NOME DA EMPRESA
         * ==========================================
         *
         * A tabela de ordens de serviço guarda
         * o nome da empresa.
         */

        $nome_empresa_filtro = null;

        if (!empty($empresa)) {

            $cliente_filtro = $this->db
                ->select('company')
                ->where(
                    'userid',
                    (int) $empresa
                )
                ->where(
                    'active',
                    1
                )
                ->get(
                    db_prefix() . 'clients'
                )
                ->row();

            if ($cliente_filtro) {

                $nome_empresa_filtro =
                    trim(
                        $cliente_filtro->company
                    );
            }
        }


        /*
         * ==========================================
         * DESCOBRE O NOME DO TÉCNICO
         * ==========================================
         *
         * A tabela de ordens de serviço guarda
         * o nome do técnico.
         */

        $nome_tecnico_filtro = null;

        if (!empty($tecnico)) {

            $tecnico_filtro = $this->db
                ->select(
                    db_prefix() . 'staff.firstname, ' .
                    db_prefix() . 'staff.lastname'
                )
                ->from(db_prefix() . 'staff')
                ->where(
                    db_prefix() . 'staff.staffid',
                    (int) $tecnico
                )
                ->where(
                    db_prefix() . 'staff.active',
                    1
                )
                ->get()
                ->row();

            if ($tecnico_filtro) {

                $nome_tecnico_filtro =
                    trim(
                        $tecnico_filtro->firstname .
                        ' ' .
                        $tecnico_filtro->lastname
                    );
            }
        }


        /*
         * ==========================================
         * CONSULTA DAS ORDENS DE SERVIÇO
         * ==========================================
         */

        $this->db->from(
            db_prefix() . 'ordens_servico'
        );


        /*
         * FILTRO POR EMPRESA
         */

        if ($nome_empresa_filtro !== null) {

            $this->db->where(
                'empresa',
                $nome_empresa_filtro
            );
        }


        /*
         * FILTRO POR TÉCNICO
         */

        if ($nome_tecnico_filtro !== null) {

            $this->db->where(
                'tecnico',
                $nome_tecnico_filtro
            );
        }


        /*
         * FILTRO POR STATUS
         */

        if (!empty($status)) {

            $this->db->where(
                'status',
                $status
            );
        }


        /*
         * FILTRO POR VALOR MÍNIMO
         */

        if (
            $valor_min !== null &&
            $valor_min !== ''
        ) {

            $this->db->where(
                'valor >=',
                (float) $valor_min
            );
        }


        /*
         * FILTRO POR VALOR MÁXIMO
         */

        if (
            $valor_max !== null &&
            $valor_max !== ''
        ) {

            $this->db->where(
                'valor <=',
                (float) $valor_max
            );
        }


        /*
         * FILTRO DATA PREVISTA - INÍCIO
         */

        if (!empty($data_prevista_inicio)) {

            $this->db->where(
                'data_prevista >=',
                $data_prevista_inicio
            );
        }


        /*
         * FILTRO DATA PREVISTA - FIM
         */

        if (!empty($data_prevista_fim)) {

            $this->db->where(
                'data_prevista <=',
                $data_prevista_fim
            );
        }


        /*
         * FILTRO DATA REALIZADA - INÍCIO
         */

        if (!empty($data_realizada_inicio)) {

            $this->db->where(
                'data_realizada >=',
                $data_realizada_inicio
            );
        }


        /*
         * FILTRO DATA REALIZADA - FIM
         */

        if (!empty($data_realizada_fim)) {

            $this->db->where(
                'data_realizada <=',
                $data_realizada_fim
            );
        }


        /*
         * ==========================================
         * FILTRO POR AVISO
         * ==========================================
         */

        if ($aviso === 'atrasada') {

            $this->db->where(
                'status !=',
                'Concluída'
            );

            $this->db->where(
                'data_prevista <',
                date('Y-m-d')
            );
        }


        if ($aviso === 'proxima') {

            $this->db->where(
                'status !=',
                'Concluída'
            );

            $this->db->where(
                'data_prevista >=',
                date('Y-m-d')
            );

            $data_limite_aviso = date(
                'Y-m-d',
                strtotime('+3 days')
            );

            $this->db->where(
                'data_prevista <=',
                $data_limite_aviso
            );
        }


        /*
         * ==========================================
         * BUSCA AS ORDENS
         * ==========================================
         */

        $data['ordens'] = $this->db
            ->order_by(
                'data_prevista',
                'ASC'
            )
            ->get()
            ->result();


        /*
         * ==========================================
         * MANTÉM OS FILTROS NA TELA
         * ==========================================
         */

        $data['filtros'] = [

            'empresa' =>
                $empresa,

            'tecnico' =>
                $tecnico,

            'status' =>
                $status,

            'aviso' =>
                $aviso,

            'valor_min' =>
                $valor_min,

            'valor_max' =>
                $valor_max,

            'data_prevista_inicio' =>
                $data_prevista_inicio,

            'data_prevista_fim' =>
                $data_prevista_fim,

            'data_realizada_inicio' =>
                $data_realizada_inicio,

            'data_realizada_fim' =>
                $data_realizada_fim,
        ];


        /*
         * ==========================================
         * CARREGA A LISTA
         * ==========================================
         */

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
                        'ordens_servico/create'
                    )
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
                    admin_url(
                        'ordens_servico/create'
                    )
                );
            }


            /*
             * Nome do técnico
             */

            $nome_tecnico = trim(
                $tecnico->firstname .
                ' ' .
                $tecnico->lastname
            );


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
                $data_realizada > $today
            ) {

                set_alert(
                    'danger',
                    'A data realizada não pode ser uma data futura.'
                );

                redirect(
                    admin_url(
                        'ordens_servico/create'
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

                $status = 'Concluída';

            } else {

                $status =
                    !empty($post['status'])
                        ? $post['status']
                        : 'Pendente';
            }


            /*
             * Insere a ordem
             */

            $this->db->insert(
                db_prefix() . 'ordens_servico',
                [

                    'empresa' =>
                        trim(
                            $cliente->company
                        ),

                    'tecnico' =>
                        $nome_tecnico,

                    'data_prevista' =>
                        $post['data_prevista'],

                    'data_realizada' =>
                        $data_realizada,

                    'status' =>
                        $status,

                    'observacao' =>
                        !empty(
                            $post['observacao']
                        )
                            ? $post['observacao']
                            : null,

                    'valor' =>
                        isset(
                            $post['valor']
                        )
                        &&
                        $post['valor'] !== ''
                            ? $post['valor']
                            : null,

                    'datecreated' =>
                        date(
                            'Y-m-d H:i:s'
                        ),
                ]
            );


            set_alert(
                'success',
                'Ordem de serviço criada com sucesso.'
            );


            redirect(
                admin_url(
                    'ordens_servico'
                )
            );
        }


        /*
         * Dados do formulário
         */

        $data['title'] =
            'Nova Ordem de Serviço';

        $data['today'] =
            $today;


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
            db_prefix() .
            'ordens_servico';


        /*
         * Busca a ordem
         */

        $ordem = $this->db
            ->where(
                'id',
                (int) $id
            )
            ->get(
                $table
            )
            ->row();


        if (!$ordem) {

            show_404();
        }


        /*
         * Ordem concluída não pode ser alterada
         * por usuários que não são administradores.
         */

        if (
            $ordem->status === 'Concluída'
            &&
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
                        'ordens_servico/edit/' .
                        $id
                    )
                );
            }


            /*
             * STATUS
             */

            if ($data_realizada !== null) {

                $status =
                    'Concluída';

            } else {

                $status =
                    !empty(
                        $post['status']
                    )
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
                    isset(
                        $post['valor']
                    )
                    &&
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
                        db_prefix() .
                        'clients'
                    )
                    ->row();


                if (!$cliente) {

                    set_alert(
                        'danger',
                        'A empresa selecionada não existe.'
                    );

                    redirect(
                        admin_url(
                            'ordens_servico/edit/' .
                            $id
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
                            db_prefix() .
                            'staff.staffid, ' .
                            db_prefix() .
                            'staff.firstname, ' .
                            db_prefix() .
                            'staff.lastname'
                        )
                        ->from(
                            db_prefix() .
                            'staff'
                        )
                        ->join(
                            db_prefix() .
                            'roles',
                            db_prefix() .
                            'roles.roleid = ' .
                            db_prefix() .
                            'staff.role',
                            'left'
                        )
                        ->where(
                            db_prefix() .
                            'staff.staffid',
                            (int) $post['tecnico']
                        )
                        ->where(
                            db_prefix() .
                            'staff.active',
                            1
                        )
                        ->where_in(
                            db_prefix() .
                            'roles.name',
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
                                'ordens_servico/edit/' .
                                $id
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
                                'ordens_servico/edit/' .
                                $id
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
         * Carrega a view
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
                db_prefix() .
                'ordens_servico'
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