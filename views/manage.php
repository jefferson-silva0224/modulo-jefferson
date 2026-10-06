<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">

    <div class="content">

        <div class="row">

            <div class="col-md-12">

                <div class="panel_s">

                    <div class="panel-body">

                        <div class="clearfix">

                            <div class="pull-left">

                                <h4 class="no-margin">
                                    Ordens de Serviço
                                </h4>

                            </div>


                            <?php if (
                                staff_can(
                                    'create',
                                    'ordens_servico'
                                )
                            ): ?>

                                <div class="pull-right">

                                    <a
                                        href="<?php
                                            echo admin_url(
                                                'ordens_servico/create'
                                            );
                                        ?>"
                                        class="btn btn-primary"
                                    >
                                        <i class="fa fa-plus"></i>
                                        Nova Ordem de Serviço
                                    </a>

                                </div>

                            <?php endif; ?>

                        </div>

                        <hr>


                        <div class="table-responsive">

                            <table
                                class="table table-bordered"
                                style="table-layout: fixed; width: 100%;"
                            >

                                <thead>

                                    <tr>

                                        <th style="width: 5%;">
                                            ID
                                        </th>

                                        <th style="width: 13%;">
                                            Empresa
                                        </th>

                                        <th style="width: 10%;">
                                            Técnico
                                        </th>

                                        <th style="width: 10%;">
                                            Data prevista
                                        </th>

                                        <th style="width: 10%;">
                                            Data realizada
                                        </th>

                                        <th style="width: 9%;">
                                            Status
                                        </th>

                                        <th style="width: 19%;">
                                            Observação
                                        </th>

                                        <?php if (
                                            staff_can(
                                                'view_value',
                                                'ordens_servico'
                                            )
                                        ): ?>

                                            <th style="width: 9%;">
                                                Valor
                                            </th>

                                        <?php endif; ?>

                                        <th style="width: 9%;">
                                            Aviso
                                        </th>

                                        <th style="width: 12%;">
                                            Ações
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <?php if (!empty($ordens)): ?>

                                        <?php foreach (
                                            $ordens as $ordem
                                        ): ?>

                                            <?php

                                            $hoje = new DateTime();

                                            $prevista = new DateTime(
                                                $ordem->data_prevista
                                            );

                                            $dias = (int) $hoje
                                                ->diff($prevista)
                                                ->format('%r%a');

                                            ?>


                                            <tr>

                                                <!-- ID -->
                                                <td>
                                                    <?php
                                                    echo (int) $ordem->id;
                                                    ?>
                                                </td>


                                                <!-- EMPRESA -->
                                                <td
                                                    style="
                                                        word-wrap: break-word;
                                                        overflow-wrap: break-word;
                                                    "
                                                >
                                                    <?php
                                                    echo html_escape(
                                                        $ordem->empresa
                                                    );
                                                    ?>
                                                </td>


                                                <!-- TÉCNICO -->
                                                <td
                                                    style="
                                                        word-wrap: break-word;
                                                        overflow-wrap: break-word;
                                                    "
                                                >
                                                    <?php
                                                    echo html_escape(
                                                        $ordem->tecnico
                                                    );
                                                    ?>
                                                </td>


                                                <!-- DATA PREVISTA -->
                                                <td>

                                                    <?php if (
                                                        !empty(
                                                            $ordem->data_prevista
                                                        )
                                                    ): ?>

                                                        <?php
                                                        echo date(
                                                            'd/m/Y',
                                                            strtotime(
                                                                $ordem->data_prevista
                                                            )
                                                        );
                                                        ?>

                                                    <?php else: ?>

                                                        -

                                                    <?php endif; ?>

                                                </td>


                                                <!-- DATA REALIZADA -->
                                                <td>

                                                    <?php if (
                                                        !empty(
                                                            $ordem->data_realizada
                                                        )
                                                    ): ?>

                                                        <?php
                                                        echo date(
                                                            'd/m/Y',
                                                            strtotime(
                                                                $ordem->data_realizada
                                                            )
                                                        );
                                                        ?>

                                                    <?php else: ?>

                                                        -

                                                    <?php endif; ?>

                                                </td>


                                                <!-- STATUS -->
                                                <td>
                                                    <?php
                                                    echo html_escape(
                                                        $ordem->status
                                                    );
                                                    ?>
                                                </td>


                                                <!-- OBSERVAÇÃO -->
                                                <td
                                                    style="
                                                        max-width: 250px;
                                                        width: 250px;
                                                        white-space: normal;
                                                        word-break: break-word;
                                                        overflow-wrap: anywhere;
                                                    "
                                                >

                                                    <?php if (
                                                        !empty(
                                                            $ordem->observacao
                                                        )
                                                    ): ?>

                                                        <?php

                                                        $observacao =
                                                            $ordem->observacao;

                                                        $observacao_curta =
                                                            mb_strlen(
                                                                $observacao
                                                            ) > 80
                                                            ? mb_substr(
                                                                $observacao,
                                                                0,
                                                                80
                                                            ) . '...'
                                                            : $observacao;

                                                        ?>

                                                        <div
                                                            style="
                                                                margin-bottom: 6px;
                                                            "
                                                        >

                                                            <?php
                                                            echo nl2br(
                                                                html_escape(
                                                                    $observacao_curta
                                                                )
                                                            );
                                                            ?>

                                                        </div>


                                                        <?php if (
                                                            mb_strlen(
                                                                $observacao
                                                            ) > 80
                                                        ): ?>

                                                            <button
                                                                type="button"
                                                                class="btn btn-default btn-xs"
                                                                data-toggle="modal"
                                                                data-target="#modal_observacao_<?php
                                                                    echo (int) $ordem->id;
                                                                ?>"
                                                            >
                                                                <i
                                                                    class="fa fa-eye"
                                                                ></i>

                                                                Ver observação
                                                            </button>

                                                        <?php endif; ?>

                                                    <?php else: ?>

                                                        -

                                                    <?php endif; ?>

                                                </td>


                                                <!-- VALOR -->
                                                <?php if (
                                                    staff_can(
                                                        'view_value',
                                                        'ordens_servico'
                                                    )
                                                ): ?>

                                                    <td>

                                                        <?php if (
                                                            $ordem->valor
                                                            !== null
                                                            &&
                                                            $ordem->valor
                                                            !== ''
                                                        ): ?>

                                                            R$
                                                            <?php
                                                            echo number_format(
                                                                $ordem->valor,
                                                                2,
                                                                ',',
                                                                '.'
                                                            );
                                                            ?>

                                                        <?php else: ?>

                                                            R$ -

                                                        <?php endif; ?>

                                                    </td>

                                                <?php endif; ?>


                                                <!-- AVISO -->
                                                <!-- AVISO -->
                                                <td
                                                    style="
                                                        white-space: normal;
                                                        word-wrap: break-word;
                                                        overflow-wrap: break-word;
                                                        text-align: center;
                                                        vertical-align: middle;
                                                    "
                                                >

                                                    <?php if (
                                                        $ordem->status
                                                        !== 'Concluída'
                                                        && $dias < 0
                                                    ): ?>

                                                        <span
                                                            class="label label-danger"
                                                            style="
                                                                display: inline-block;
                                                                white-space: normal;
                                                                line-height: 1.4;
                                                            "
                                                        >
                                                            Atrasada
                                                        </span>

                                                    <?php elseif (
                                                        $ordem->status
                                                        !== 'Concluída'
                                                        && $dias >= 0
                                                        && $dias <= 3
                                                    ): ?>

                                                        <span
                                                            class="label label-warning"
                                                            style="
                                                                display: inline-block;
                                                                white-space: normal;
                                                                line-height: 1.4;
                                                                max-width: 100%;
                                                            "
                                                        >
                                                            Próxima do vencimento
                                                        </span>

                                                    <?php else: ?>

                                                        -

                                                    <?php endif; ?>

                                                </td>


                                                <!-- AÇÕES -->
                                                <td>

                                                    <?php

                                                    $pode_editar = (
                                                        staff_can(
                                                            'edit',
                                                            'ordens_servico'
                                                        )
                                                        &&
                                                        (
                                                            $ordem->status
                                                            !== 'Concluída'
                                                            ||
                                                            is_admin()
                                                        )
                                                    );

                                                    ?>


                                                    <?php if (
                                                        $pode_editar
                                                    ): ?>

                                                        <a
                                                            href="<?php
                                                                echo admin_url(
                                                                    'ordens_servico/edit/'
                                                                    . $ordem->id
                                                                );
                                                            ?>"
                                                            class="btn btn-default btn-sm"
                                                        >

                                                            <i
                                                                class="fa fa-pencil"
                                                            ></i>

                                                            Editar

                                                        </a>

                                                    <?php endif; ?>


                                                    <?php if (
                                                        staff_can(
                                                            'delete',
                                                            'ordens_servico'
                                                        )
                                                    ): ?>

                                                        <a
                                                            href="<?php
                                                                echo admin_url(
                                                                    'ordens_servico/delete/'
                                                                    . $ordem->id
                                                                );
                                                            ?>"
                                                            class="btn btn-danger btn-sm _delete"
                                                        >

                                                            <i
                                                                class="fa fa-trash"
                                                            ></i>

                                                            Excluir

                                                        </a>

                                                    <?php endif; ?>

                                                </td>

                                            </tr>


                                            <!-- MODAL DA OBSERVAÇÃO -->
                                            <?php if (
                                                !empty(
                                                    $ordem->observacao
                                                )
                                                &&
                                                mb_strlen(
                                                    $ordem->observacao
                                                ) > 80
                                            ): ?>

                                                <div
                                                    class="modal fade"
                                                    id="modal_observacao_<?php
                                                        echo (int) $ordem->id;
                                                    ?>"
                                                    tabindex="-1"
                                                    role="dialog"
                                                >

                                                    <div
                                                        class="modal-dialog modal-lg"
                                                        role="document"
                                                    >

                                                        <div class="modal-content">

                                                            <div class="modal-header">

                                                                <button
                                                                    type="button"
                                                                    class="close"
                                                                    data-dismiss="modal"
                                                                    aria-label="Fechar"
                                                                >
                                                                    <span
                                                                        aria-hidden="true"
                                                                    >
                                                                        &times;
                                                                    </span>
                                                                </button>

                                                                <h4 class="modal-title">

                                                                    <i
                                                                        class="fa fa-file-text-o"
                                                                    ></i>

                                                                    Observação da Ordem de Serviço
                                                                    #<?php
                                                                    echo (int) $ordem->id;
                                                                    ?>

                                                                </h4>

                                                            </div>


                                                            <div class="modal-body">

                                                                <div
                                                                    style="
                                                                        white-space: normal;
                                                                        word-break: break-word;
                                                                        overflow-wrap: anywhere;
                                                                        line-height: 1.6;
                                                                    "
                                                                >

                                                                    <?php
                                                                    echo nl2br(
                                                                        html_escape(
                                                                            $ordem->observacao
                                                                        )
                                                                    );
                                                                    ?>

                                                                </div>

                                                            </div>


                                                            <div class="modal-footer">

                                                                <button
                                                                    type="button"
                                                                    class="btn btn-default"
                                                                    data-dismiss="modal"
                                                                >

                                                                    Fechar

                                                                </button>

                                                            </div>

                                                        </div>

                                                    </div>

                                                </div>

                                            <?php endif; ?>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <tr>

                                            <td
                                                colspan="10"
                                                class="text-center"
                                            >
                                                Nenhuma ordem de serviço
                                                cadastrada.
                                            </td>

                                        </tr>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php init_tail(); ?>