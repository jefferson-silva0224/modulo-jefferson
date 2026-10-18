<?php

defined('BASEPATH') or exit('No direct script access allowed');


class Ordens_servico extends AdminController
{

    public function __construct()
    {
        parent::__construct();

    }


    /*
    |--------------------------------------------------------------------------
    | VERIFICA SE O USUÁRIO É TÉCNICO
    |--------------------------------------------------------------------------
    */

    private function usuario_e_tecnico()
    {
        $staffid = get_staff_user_id();

        $staff = $this->db
            ->select('staffid')
            ->where('staffid', $staffid)
            ->get(db_prefix() . 'staff')
            ->row();

        if (!$staff) {
            return false;
        }

        $this->db->reset_query();

        $staff_role = $this->db
            ->select('r.name')
            ->from(db_prefix() . 'staff as s')
            ->join(
                db_prefix() . 'roles as r',
                'r.roleid = s.role',
                'left'
            )
            ->where('s.staffid', $staffid)
            ->get()
            ->row();

        $this->db->reset_query();

        if (!$staff_role) {
            return false;
        }

        return trim($staff_role->name) === 'Técnico';
    }


    /*
    |--------------------------------------------------------------------------
    | PEGA O NOME DO TÉCNICO LOGADO
    |--------------------------------------------------------------------------
    */

    private function nome_tecnico_logado()
    {
        $staffid = get_staff_user_id();

        $staff = $this->db
            ->select('firstname, lastname')
            ->where('staffid', $staffid)
            ->get(db_prefix() . 'staff')
            ->row();

        $this->db->reset_query();

        if (!$staff) {
            return '';
        }

        return trim(
            $staff->firstname . ' ' . $staff->lastname
        );
    }


    /*
    |--------------------------------------------------------------------------
    | VERIFICA SE O TÉCNICO PODE ACESSAR A OS
    |--------------------------------------------------------------------------
    */

    private function tecnico_pode_acessar_os($ordem)
    {
        if (!$this->usuario_e_tecnico()) {
            return true;
        }

        $staffid = (int) get_staff_user_id();
        $nome_tecnico = $this->nome_tecnico_logado();

        // Registros novos usam o ID; registros antigos continuam funcionando pelo nome.
        if (isset($ordem->tecnico_id) && !empty($ordem->tecnico_id)) {
            return (int) $ordem->tecnico_id === $staffid;
        }

        return !empty($ordem->tecnico)
            && trim($ordem->tecnico) === trim($nome_tecnico);
    }


    /*
    |--------------------------------------------------------------------------
    | LISTAGEM
    |--------------------------------------------------------------------------
    */

    public function index()
    {

        if (!staff_can('view', 'ordens_servico')) {
            access_denied('ordens_servico');
        }


        /*
        |--------------------------------------------------------------------------
        | CLIENTES
        |--------------------------------------------------------------------------
        */

        $clientes = $this->db
            ->select('userid, company')
            ->where('active', 1)
            ->order_by('company', 'ASC')
            ->get(db_prefix() . 'clients')
            ->result();

        $this->db->reset_query();


        /*
        |--------------------------------------------------------------------------
        | TÉCNICOS
        |--------------------------------------------------------------------------
        */

        $tecnicos = $this->db
            ->select(
                db_prefix() . 'staff.staffid,
                ' .
                db_prefix() . 'staff.firstname,
                ' .
                db_prefix() . 'staff.lastname'
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

        $this->db->reset_query();


        /*
        |--------------------------------------------------------------------------
        | FILTROS
        |--------------------------------------------------------------------------
        */

        $filtros = [
            'empresa' => $this->input->get('empresa'),
            'tecnico' => $this->input->get('tecnico'),
            'status' => $this->input->get('status'),
            'aviso' => $this->input->get('aviso'),
            'valor_min' => $this->input->get('valor_min'),
            'valor_max' => $this->input->get('valor_max'),
            'data_prevista_inicio' =>
                $this->input->get('data_prevista_inicio'),
            'data_prevista_fim' =>
                $this->input->get('data_prevista_fim'),
            'data_realizada_inicio' =>
                $this->input->get('data_realizada_inicio'),
            'data_realizada_fim' =>
                $this->input->get('data_realizada_fim'),
        ];


        /*
        |--------------------------------------------------------------------------
        | DEFINE SE O USUÁRIO É TÉCNICO
        |--------------------------------------------------------------------------
        */

        $usuario_e_tecnico = $this->usuario_e_tecnico();

        $nome_tecnico_logado = '';

        if ($usuario_e_tecnico) {
            $nome_tecnico_logado =
                $this->nome_tecnico_logado();
        }


        /*
        |--------------------------------------------------------------------------
        | IMPORTANTE:
        | LIMPA O QUERY BUILDER ANTES DA CONSULTA PRINCIPAL
        |--------------------------------------------------------------------------
        */

        $this->db->reset_query();


        /*
        |--------------------------------------------------------------------------
        | CONSULTA PRINCIPAL
        |--------------------------------------------------------------------------
        */

        $this->db
            ->select('*')
            ->from(db_prefix() . 'ordens_servico');


        /*
        |--------------------------------------------------------------------------
        | RESTRIÇÃO DO TÉCNICO
        |--------------------------------------------------------------------------
        */

        if ($usuario_e_tecnico) {
            $staffid_logado = (int) get_staff_user_id();

            $this->db->group_start();
            $this->db->where('tecnico_id', $staffid_logado);
            $this->db->or_group_start();
            $this->db->where('tecnico_id IS NULL', null, false);
            $this->db->where('tecnico', $nome_tecnico_logado);
            $this->db->group_end();
            $this->db->group_end();
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRO EMPRESA
        |--------------------------------------------------------------------------
        */

        if (!empty($filtros['empresa'])) {
            $empresa_nome_filtro = null;

            foreach ($clientes as $cliente_item) {
                if ((int) $cliente_item->userid === (int) $filtros['empresa']) {
                    $empresa_nome_filtro = $cliente_item->company;
                    break;
                }
            }

            $this->db->group_start();
            $this->db->where('empresa_id', (int) $filtros['empresa']);

            // Compatibilidade com ordens antigas que ainda não têm ID.
            if ($empresa_nome_filtro !== null) {
                $this->db->or_group_start();
                $this->db->where('empresa_id IS NULL', null, false);
                $this->db->where('empresa', $empresa_nome_filtro);
                $this->db->group_end();
            }
            $this->db->group_end();
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRO TÉCNICO
        |--------------------------------------------------------------------------
        */

        if (!empty($filtros['tecnico']) && !$usuario_e_tecnico) {
            $tecnico_nome_filtro = null;

            foreach ($tecnicos as $tecnico_item) {
                if ((int) $tecnico_item->staffid === (int) $filtros['tecnico']) {
                    $tecnico_nome_filtro = trim($tecnico_item->firstname . ' ' . $tecnico_item->lastname);
                    break;
                }
            }

            $this->db->group_start();
            $this->db->where('tecnico_id', (int) $filtros['tecnico']);

            // Compatibilidade com ordens antigas que ainda não têm ID.
            if ($tecnico_nome_filtro !== null) {
                $this->db->or_group_start();
                $this->db->where('tecnico_id IS NULL', null, false);
                $this->db->where('tecnico', $tecnico_nome_filtro);
                $this->db->group_end();
            }
            $this->db->group_end();
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRO STATUS
        |--------------------------------------------------------------------------
        */

        if (!empty($filtros['status'])) {

            $status_permitidos = [
                'Aberto',
                'Pendente',
                'Concluída',
                'Cancelado'
            ];

            if (
                in_array(
                    $filtros['status'],
                    $status_permitidos,
                    true
                )
            ) {
                // A migração preservou alguns status em minúsculas.
                $variantes_status = [
                    'Aberto' => ['Aberto', 'aberto'],
                    'Pendente' => ['Pendente', 'pendente'],
                    'Concluída' => ['Concluída', 'concluída', 'Concluida', 'concluida', 'Concluído', 'concluído', 'Concluido', 'concluido'],
                    'Cancelado' => ['Cancelado', 'cancelado'],
                ];

                $this->db->where_in(
                    'status',
                    $variantes_status[$filtros['status']]
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRO VALOR MÍNIMO
        |--------------------------------------------------------------------------
        */

        if (
            $filtros['valor_min'] !== null
            &&
            $filtros['valor_min'] !== ''
        ) {

            $this->db->where(
                'valor >=',
                (float) $filtros['valor_min']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRO VALOR MÁXIMO
        |--------------------------------------------------------------------------
        */

        if (
            $filtros['valor_max'] !== null
            &&
            $filtros['valor_max'] !== ''
        ) {

            $this->db->where(
                'valor <=',
                (float) $filtros['valor_max']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRO DATA PREVISTA INICIAL
        |--------------------------------------------------------------------------
        */

        if (!empty($filtros['data_prevista_inicio'])) {

            $this->db->where(
                'data_prevista >=',
                $filtros['data_prevista_inicio']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRO DATA PREVISTA FINAL
        |--------------------------------------------------------------------------
        */

        if (!empty($filtros['data_prevista_fim'])) {

            $this->db->where(
                'data_prevista <=',
                $filtros['data_prevista_fim']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRO DATA REALIZADA INICIAL
        |--------------------------------------------------------------------------
        */

        if (!empty($filtros['data_realizada_inicio'])) {

            $this->db->where(
                'data_realizada >=',
                $filtros['data_realizada_inicio']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRO DATA REALIZADA FINAL
        |--------------------------------------------------------------------------
        */

        if (!empty($filtros['data_realizada_fim'])) {

            $this->db->where(
                'data_realizada <=',
                $filtros['data_realizada_fim']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRO AVISO
        |--------------------------------------------------------------------------
        */

        if (
            !empty($filtros['aviso'])
        ) {

            $hoje = date('Y-m-d');

            $limite_proximo = date(
                'Y-m-d',
                strtotime('+5 days')
            );


            if (
                $filtros['aviso'] === 'atrasada'
            ) {

                $this->db->where(
                    'data_prevista <',
                    $hoje
                );

                $this->db->where(
                    'data_realizada IS NULL',
                    null,
                    false
                );

                $this->db->where_not_in(
                    'status',
                    ['Concluída', 'concluída', 'Concluida', 'concluida', 'Concluído', 'concluído', 'Concluido', 'concluido', 'Cancelado', 'cancelado']
                );

            } elseif (
                $filtros['aviso'] === 'proxima'
            ) {

                $this->db->where(
                    'data_prevista >=',
                    $hoje
                );

                $this->db->where(
                    'data_prevista <=',
                    $limite_proximo
                );

                $this->db->where(
                    'data_realizada IS NULL',
                    null,
                    false
                );

                $this->db->where_not_in(
                    'status',
                    ['Concluída', 'concluída', 'Concluida', 'concluida', 'Concluído', 'concluído', 'Concluido', 'concluido', 'Cancelado', 'cancelado']
                );
            } elseif (
                $filtros['aviso'] === 'dentro_prazo'
            ) {
                // OS ativas com vencimento além da janela de alerta de 5 dias.
                $this->db->where(
                    'data_prevista >',
                    $limite_proximo
                );

                $this->db->where(
                    'data_realizada IS NULL',
                    null,
                    false
                );

                $this->db->where_not_in(
                    'status',
                    ['Concluída', 'concluída', 'Concluida', 'concluida', 'Concluído', 'concluído', 'Concluido', 'concluido', 'Cancelado', 'cancelado']
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ORDENAÇÃO
        |--------------------------------------------------------------------------
        */

        $this->db->order_by(
            'data_prevista',
            'ASC'
        );

        $ordens = $this->db
            ->get()
            ->result();

        // Mapas de nomes atuais: alterações no cadastro aparecem também nas OS antigas.
        $nomes_empresas = [];
        foreach ($clientes as $cliente_item) {
            $nomes_empresas[(int) $cliente_item->userid] = $cliente_item->company;
        }

        $nomes_tecnicos = [];
        foreach ($tecnicos as $tecnico_item) {
            $nomes_tecnicos[(int) $tecnico_item->staffid] = trim(
                $tecnico_item->firstname . ' ' . $tecnico_item->lastname
            );
        }

        foreach ($ordens as $ordem_item) {
            $empresa_id_item = isset($ordem_item->empresa_id) ? (int) $ordem_item->empresa_id : 0;
            $tecnico_id_item = isset($ordem_item->tecnico_id) ? (int) $ordem_item->tecnico_id : 0;

            $ordem_item->empresa_exibicao = (
                $empresa_id_item && isset($nomes_empresas[$empresa_id_item])
            ) ? $nomes_empresas[$empresa_id_item] : $ordem_item->empresa;

            $ordem_item->tecnico_exibicao = (
                $tecnico_id_item && isset($nomes_tecnicos[$tecnico_id_item])
            ) ? $nomes_tecnicos[$tecnico_id_item] : $ordem_item->tecnico;
        }


        /*
        |--------------------------------------------------------------------------
        | DADOS DA VIEW
        |--------------------------------------------------------------------------
        */

        $data = [
            'title' => 'Ordens de Serviço',
            'ordens' => $ordens,
            'clientes' => $clientes,
            'tecnicos' => $tecnicos,
            'filtros' => $filtros,
        ];

        $this->load->view(
            'ordens_servico/manage',
            $data
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CRIAR
    |--------------------------------------------------------------------------
    */

    public function create()
    {

        if (!staff_can('create', 'ordens_servico')) {
            access_denied('ordens_servico');
        }


        /*
        |--------------------------------------------------------------------------
        | POST
        |--------------------------------------------------------------------------
        */

        if ($this->input->post()) {

            $post = $this->input->post();


            /*
            |--------------------------------------------------------------------------
            | EMPRESA
            |--------------------------------------------------------------------------
            */

            if (
                !isset($post['empresa'])
                ||
                empty($post['empresa'])
            ) {

                set_alert(
                    'danger',
                    'Selecione uma empresa.'
                );

                redirect(
                    admin_url(
                        'ordens_servico/create'
                    )
                );
            }


            $cliente = $this->db
                ->where(
                    'userid',
                    $post['empresa']
                )
                ->where(
                    'active',
                    1
                )
                ->get(
                    db_prefix() . 'clients'
                )
                ->row();

            $this->db->reset_query();


            if (!$cliente) {

                set_alert(
                    'danger',
                    'A empresa selecionada é inválida.'
                );

                redirect(
                    admin_url(
                        'ordens_servico/create'
                    )
                );
            }


            /*
            |--------------------------------------------------------------------------
            | TÉCNICO
            |--------------------------------------------------------------------------
            */

            $nome_tecnico = null;

            if (
                isset($post['tecnico'])
                &&
                !empty($post['tecnico'])
            ) {

                $tecnico = $this->db
                    ->select(
                        'firstname, lastname'
                    )
                    ->where(
                        'staffid',
                        $post['tecnico']
                    )
                    ->where(
                        'active',
                        1
                    )
                    ->get(
                        db_prefix() . 'staff'
                    )
                    ->row();

                $this->db->reset_query();


                if (!$tecnico) {

                    set_alert(
                        'danger',
                        'O técnico selecionado é inválido.'
                    );

                    redirect(
                        admin_url(
                            'ordens_servico/create'
                        )
                    );
                }


                $nome_tecnico = trim(
                    $tecnico->firstname .
                    ' ' .
                    $tecnico->lastname
                );
            }


            /*
            |--------------------------------------------------------------------------
            | DATA PREVISTA
            |--------------------------------------------------------------------------
            */

            $data_prevista = null;

            if (
                isset($post['data_prevista'])
                &&
                !empty($post['data_prevista'])
            ) {

                $data_prevista =
                    $post['data_prevista'];

                $hoje = date('Y-m-d');

                if (
                    $data_prevista < $hoje
                ) {

                    set_alert(
                        'danger',
                        'A data prevista não pode ser anterior à data de hoje.'
                    );

                    redirect(
                        admin_url(
                            'ordens_servico/create'
                        )
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | DATA REALIZADA
            |--------------------------------------------------------------------------
            */

            $data_realizada = null;

            if (
                isset($post['data_realizada'])
                &&
                !empty($post['data_realizada'])
            ) {

                $data_realizada =
                    $post['data_realizada'];

                $hoje = date('Y-m-d');

                if (
                    $data_realizada > $hoje
                ) {

                    set_alert(
                        'danger',
                        'A data realizada não pode ser futura.'
                    );

                    redirect(
                        admin_url(
                            'ordens_servico/create'
                        )
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | STATUS AUTOMÁTICO
            |--------------------------------------------------------------------------
            */

            $status_solicitado = isset($post['status'])
    ? trim($post['status'])
    : '';

if ($status_solicitado === 'Cancelado') {

    $status = 'Cancelado';

} elseif (!empty($data_realizada)) {

    $status = 'Concluída';

} elseif (
    empty($nome_tecnico)
    ||
    empty($data_prevista)
) {

    $status = 'Aberto';

} else {

    $status = 'Pendente';
}


            /*
            |--------------------------------------------------------------------------
            | OBSERVAÇÃO
            |--------------------------------------------------------------------------
            */

            $observacao = null;

            if (
                isset($post['observacao'])
                &&
                trim($post['observacao']) !== ''
            ) {

                $observacao =
                    trim($post['observacao']);
            }


            /*
            |--------------------------------------------------------------------------
            | VALOR
            |--------------------------------------------------------------------------
            */

            $valor = null;

            if (
                staff_can(
                    'view_value',
                    'ordens_servico'
                )
                &&
                isset($post['valor'])
                &&
                $post['valor'] !== ''
            ) {

                $valor =
                    str_replace(
                        ',',
                        '.',
                        $post['valor']
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | INSERÇÃO
            |--------------------------------------------------------------------------
            */

            $dados = [
                'empresa_id'     => (int) $cliente->userid,
                'tecnico_id'     => !empty($post['tecnico']) ? (int) $post['tecnico'] : null,
                'empresa'        => $cliente->company,
                'tecnico'        => $nome_tecnico,
                'data_prevista'  => $data_prevista,
                'data_realizada' => $data_realizada,
                'status'         => $status,
                'observacao'     => $observacao,
                'valor'          => $valor,
                'datecreated'    => date('Y-m-d H:i:s'),
            ];


            $this->db->insert(
                db_prefix() . 'ordens_servico',
                $dados
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
        |--------------------------------------------------------------------------
        | DADOS DO FORMULÁRIO
        |--------------------------------------------------------------------------
        */

        $clientes = $this->db
            ->select('userid, company')
            ->where('active', 1)
            ->order_by('company', 'ASC')
            ->get(db_prefix() . 'clients')
            ->result();

        $this->db->reset_query();


        $tecnicos = $this->db
            ->select(
                db_prefix() . 'staff.staffid,
                ' .
                db_prefix() . 'staff.firstname,
                ' .
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

        $this->db->reset_query();


        $data = [
            'title' => 'Nova Ordem de Serviço',
            'clientes' => $clientes,
            'tecnicos' => $tecnicos,
        ];


        $this->load->view(
            'ordens_servico/form',
            $data
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDITAR
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {

        $ordem = $this->db
            ->where(
                'id',
                $id
            )
            ->get(
                db_prefix() . 'ordens_servico'
            )
            ->row();

        $this->db->reset_query();


        if (!$ordem) {

            show_404();
        }


        /*
        |--------------------------------------------------------------------------
        | ORDEM CONCLUÍDA
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                mb_strtolower(trim((string) $ordem->status), 'UTF-8'),
                ['concluída', 'concluida', 'concluído', 'concluido'],
                true
            )
            &&
            !is_admin()
        ) {

            access_denied(
                'ordens_servico'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | TÉCNICO SÓ ACESSA A PRÓPRIA OS
        |--------------------------------------------------------------------------
        */

        if (
            !$this->tecnico_pode_acessar_os(
                $ordem
            )
        ) {

            access_denied(
                'ordens_servico'
            );
        }


        if (!staff_can('edit', 'ordens_servico')) {

            access_denied(
                'ordens_servico'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | POST
        |--------------------------------------------------------------------------
        */

        if ($this->input->post()) {

            $post = $this->input->post();


            /*
            |--------------------------------------------------------------------------
            | EMPRESA
            |--------------------------------------------------------------------------
            */

            $empresa_atual = $ordem->empresa;
            $empresa_id_atual = !empty($ordem->empresa_id) ? (int) $ordem->empresa_id : null;


            if (
                isset($post['empresa'])
                &&
                !empty($post['empresa'])
            ) {

                $cliente = $this->db
                    ->where(
                        'userid',
                        $post['empresa']
                    )
                    ->where(
                        'active',
                        1
                    )
                    ->get(
                        db_prefix() . 'clients'
                    )
                    ->row();

                $this->db->reset_query();


                if (!$cliente) {

                    set_alert(
                        'danger',
                        'A empresa selecionada é inválida.'
                    );

                    redirect(
                        admin_url(
                            'ordens_servico/edit/'
                            . $id
                        )
                    );
                }


                $empresa_atual = $cliente->company;
                $empresa_id_atual = (int) $cliente->userid;
            }


            /*
            |--------------------------------------------------------------------------
            | TÉCNICO ATUAL
            |--------------------------------------------------------------------------
            */

            $nome_tecnico_atual = $ordem->tecnico;
            $tecnico_id_atual = !empty($ordem->tecnico_id) ? (int) $ordem->tecnico_id : null;


            if (
                staff_can(
                    'manage_schedule',
                    'ordens_servico'
                )
                &&
                !$this->usuario_e_tecnico()
            ) {

                if (
                    isset($post['tecnico'])
                    &&
                    !empty($post['tecnico'])
                ) {

                    $tecnico = $this->db
                        ->select(
                            'firstname, lastname'
                        )
                        ->where(
                            'staffid',
                            $post['tecnico']
                        )
                        ->where(
                            'active',
                            1
                        )
                        ->get(
                            db_prefix() . 'staff'
                        )
                        ->row();

                    $this->db->reset_query();


                    if (!$tecnico) {

                        set_alert(
                            'danger',
                            'O técnico selecionado é inválido.'
                        );

                        redirect(
                            admin_url(
                                'ordens_servico/edit/'
                                . $id
                            )
                        );
                    }


                    $nome_tecnico_atual = trim(
                        $tecnico->firstname . ' ' . $tecnico->lastname
                    );
                    $tecnico_id_atual = (int) $post['tecnico'];

                } else {
                    $nome_tecnico_atual = null;
                    $tecnico_id_atual = null;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | DATA PREVISTA
            |--------------------------------------------------------------------------
            */

            $data_prevista =
                $ordem->data_prevista;


            if (
                isset($post['data_prevista'])
            ) {

                $data_prevista =
                    !empty($post['data_prevista'])
                    ? $post['data_prevista']
                    : null;
            }


            if (!empty($data_prevista)) {

                $hoje = date('Y-m-d');

                if (
                    $data_prevista < $hoje
                    &&
                    !in_array(
                        mb_strtolower(trim((string) $ordem->status), 'UTF-8'),
                        ['concluída', 'concluida', 'concluído', 'concluido'],
                        true
                    )
                ) {

                    set_alert(
                        'danger',
                        'A data prevista não pode ser anterior à data de hoje.'
                    );

                    redirect(
                        admin_url(
                            'ordens_servico/edit/'
                            . $id
                        )
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | DATA REALIZADA
            |--------------------------------------------------------------------------
            */

            $data_realizada =
                $ordem->data_realizada;


            if (
                isset($post['data_realizada'])
            ) {

                $data_realizada =
                    !empty($post['data_realizada'])
                    ? $post['data_realizada']
                    : null;
            }


            if (!empty($data_realizada)) {

                $hoje = date('Y-m-d');

                if (
                    $data_realizada > $hoje
                ) {

                    set_alert(
                        'danger',
                        'A data realizada não pode ser futura.'
                    );

                    redirect(
                        admin_url(
                            'ordens_servico/edit/'
                            . $id
                        )
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | STATUS AUTOMÁTICO
            |--------------------------------------------------------------------------
            */

            $status_solicitado = isset($post['status'])
    ? trim($post['status'])
    : '';

if (
    $status_solicitado === 'Cancelado'
    ||
    (
        $status_solicitado === ''
        &&
        $ordem->status === 'Cancelado'
    )
) {

    $status = 'Cancelado';

} elseif (!empty($data_realizada)) {

    $status = 'Concluída';

} elseif (
    empty($nome_tecnico_atual)
    ||
    empty($data_prevista)
) {

    $status = 'Aberto';

} else {

    $status = 'Pendente';
}


            /*
            |--------------------------------------------------------------------------
            | OBSERVAÇÃO
            |--------------------------------------------------------------------------
            */

            $observacao =
                $ordem->observacao;


            if (
                staff_can(
                    'add_observation',
                    'ordens_servico'
                )
                &&
                isset($post['observacao'])
            ) {

                $observacao =
                    trim($post['observacao']);

                if ($observacao === '') {
                    $observacao = null;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | VALOR
            |--------------------------------------------------------------------------
            */

            $valor =
                $ordem->valor;


            if (
                staff_can(
                    'view_value',
                    'ordens_servico'
                )
                &&
                isset($post['valor'])
            ) {

                if (
                    $post['valor'] === ''
                ) {

                    $valor = null;

                } else {

                    $valor =
                        str_replace(
                            ',',
                            '.',
                            $post['valor']
                        );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | ATUALIZAÇÃO
            |--------------------------------------------------------------------------
            */

            $dados = [
                'empresa_id'     => $empresa_id_atual,
                'tecnico_id'     => $tecnico_id_atual,
                'empresa'        => $empresa_atual,
                'tecnico'        => $nome_tecnico_atual,
                'data_prevista'  => $data_prevista,
                'data_realizada' => $data_realizada,
                'status'         => $status,
                'observacao'     => $observacao,
                'valor'          => $valor,
            ];


            $this->db
                ->where(
                    'id',
                    $id
                )
                ->update(
                    db_prefix() . 'ordens_servico',
                    $dados
                );


            set_alert(
                'success',
                'Ordem de serviço atualizada com sucesso.'
            );


            redirect(
                admin_url(
                    'ordens_servico'
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | DADOS DO FORMULÁRIO
        |--------------------------------------------------------------------------
        */

        $clientes = $this->db
            ->select('userid, company')
            ->where('active', 1)
            ->order_by('company', 'ASC')
            ->get(db_prefix() . 'clients')
            ->result();

        $this->db->reset_query();


        $tecnicos = $this->db
            ->select(
                db_prefix() . 'staff.staffid,
                ' .
                db_prefix() . 'staff.firstname,
                ' .
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

        $this->db->reset_query();


        $data = [
            'title' => 'Editar Ordem de Serviço',
            'ordem' => $ordem,
            'clientes' => $clientes,
            'tecnicos' => $tecnicos,
        ];


        $this->load->view(
            'ordens_servico/form',
            $data
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXCLUIR
    |--------------------------------------------------------------------------
    */

    public function delete($id)
    {

        if (
            !staff_can(
                'delete',
                'ordens_servico'
            )
        ) {

            access_denied(
                'ordens_servico'
            );
        }


        $ordem = $this->db
            ->where(
                'id',
                $id
            )
            ->get(
                db_prefix() . 'ordens_servico'
            )
            ->row();

        $this->db->reset_query();


        if (!$ordem) {

            show_404();
        }


        /*
        |--------------------------------------------------------------------------
        | CONCLUÍDA: SOMENTE ADMIN
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                mb_strtolower(trim((string) $ordem->status), 'UTF-8'),
                ['concluída', 'concluida', 'concluído', 'concluido'],
                true
            )
            &&
            !is_admin()
        ) {

            access_denied(
                'ordens_servico'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | TÉCNICO SÓ PODE EXCLUIR A PRÓPRIA OS
        |--------------------------------------------------------------------------
        */

        if (
            !$this->tecnico_pode_acessar_os(
                $ordem
            )
        ) {

            access_denied(
                'ordens_servico'
            );
        }


        $this->db
            ->where(
                'id',
                $id
            )
            ->delete(
                db_prefix() . 'ordens_servico'
            );


        set_alert(
            'success',
            'Ordem de serviço excluída com sucesso.'
        );


        redirect(
            admin_url(
                'ordens_servico'
            )
        );
    }
}