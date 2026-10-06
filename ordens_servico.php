<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Ordens de Serviço
Description: Módulo para cadastro e acompanhamento de ordens de serviço.
Version: 1.0.0
Requires at least: 3.4.*
*/

define('ORDENS_SERVICO_MODULE_NAME', 'ordens_servico');

hooks()->add_action('admin_init', 'ordens_servico_register_permissions');

function ordens_servico_register_permissions()
{
    $config = [];

   $config['capabilities'] = [
    'view'            => 'Visualizar Ordens de Serviço',
    'create'          => 'Criar Ordens de Serviço',
    'edit'            => 'Editar Ordens de Serviço',
    'edit_company'    => 'Editar empresa',
    'delete'          => 'Deletar Ordens de Serviço',
    'manage_schedule' => 'Definir técnico e data prevista',
    'manage_status'   => 'Alterar status',
    'add_observation' => 'Adicionar observação',
    'view_value'      => 'Visualizar valor',
];

    register_staff_capabilities(
        ORDENS_SERVICO_MODULE_NAME,
        $config,
        'Ordens de Serviço'
    );
}

hooks()->add_action('admin_init', 'ordens_servico_init_menu');

function ordens_servico_init_menu()
{
    $CI = &get_instance();

    if (staff_can('view', 'ordens_servico')) {
        $CI->app_menu->add_sidebar_menu_item('ordens-servico', [
            'name'     => 'Ordens de Serviço',
            'href'     => admin_url('ordens_servico'),
            'icon'     => 'fa fa-wrench',
            'position' => 25,
        ]);
    }
}

register_activation_hook(
    ORDENS_SERVICO_MODULE_NAME,
    'ordens_servico_activate'
);

function ordens_servico_activate()
{
    $CI = &get_instance();
    $table = db_prefix() . 'ordens_servico';

    if (!$CI->db->table_exists($table)) {
        $CI->db->query("CREATE TABLE `$table` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `empresa` VARCHAR(191) NOT NULL,
            `tecnico` VARCHAR(191) NOT NULL,
            `data_prevista` DATE NOT NULL,
            `data_realizada` DATE NULL,
            `status` VARCHAR(50) NOT NULL DEFAULT 'Pendente',
            `observacao` TEXT NULL,
            `valor` DECIMAL(10,2) NULL,
            `datecreated` DATETIME NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }
}