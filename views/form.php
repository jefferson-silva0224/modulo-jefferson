<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">

    <div class="content">

        <div class="row">

            <div class="col-md-12">

                <div class="panel_s">

                    <div class="panel-body">

                        <h4 class="no-margin">
                            <?php echo $title; ?>
                        </h4>

                        <hr>


                        <?php

                        /*
                         * Verifica se estamos editando.
                         */

                        $editando =
                            isset($ordem) &&
                            !empty($ordem->id);


                        /*
                         * Permissões.
                         */

                        $pode_empresa =
                            staff_can(
                                'manage_schedule',
                                'ordens_servico'
                            );

                        $pode_agenda =
                            staff_can(
                                'manage_schedule',
                                'ordens_servico'
                            );

                        $pode_status =
                            staff_can(
                                'manage_status',
                                'ordens_servico'
                            );

                        $pode_observacao =
                            staff_can(
                                'add_observation',
                                'ordens_servico'
                            );

                        $pode_valor =
                            staff_can(
                                'view_value',
                                'ordens_servico'
                            );

                        ?>


                        <?php

                        /*
                         * Formulário Perfex.
                         *
                         * Não remover form_open().
                         * Ele evita erro 419 Page Expired.
                         */

                        echo form_open(
                            $editando
                                ? admin_url(
                                    'ordens_servico/edit/' .
                                    $ordem->id
                                )
                                : admin_url(
                                    'ordens_servico/create'
                                )
                        );

                        ?>


                        <!-- ================================================= -->
                        <!-- EMPRESA -->
                        <!-- ================================================= -->

                        <div class="form-group">

                            <label for="empresa">
                                Empresa
                            </label>

                            <select
                                name="empresa"
                                id="empresa"
                                class="form-control"
                                <?php
                                echo (
                                    $editando &&
                                    !$pode_empresa
                                )
                                    ? 'disabled'
                                    : '';
                                ?>
                                required
                            >

                                <option value="">
                                    Selecione uma empresa
                                </option>


                                <?php foreach (
                                    $clientes as $cliente
                                ): ?>

                                    <option
                                        value="<?php
                                            echo $cliente->userid;
                                        ?>"
                                        <?php

                                        if (
                                            $editando &&
                                            isset(
                                                $ordem->empresa
                                            ) &&
                                            $ordem->empresa ===
                                            $cliente->company
                                        ) {

                                            echo 'selected';
                                        }

                                        ?>
                                    >

                                        <?php
                                        echo html_escape(
                                            $cliente->company
                                        );
                                        ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- ================================================= -->
                        <!-- TÉCNICO -->
                        <!-- ================================================= -->

                        <div class="form-group">

                            <label for="tecnico">
                                Técnico
                            </label>

                            <select
                                name="tecnico"
                                id="tecnico"
                                class="form-control"
                                <?php

                                /*
                                 * Na criação:
                                 * técnico pode ficar vazio.
                                 *
                                 * Na edição:
                                 * quem não possui manage_schedule
                                 * não pode alterar.
                                 */

                                echo (
                                    $editando &&
                                    !$pode_agenda
                                )
                                    ? 'disabled'
                                    : '';

                                ?>
                            >

                                <option value="">
                                    Selecione um técnico
                                </option>


                                <?php foreach (
                                    $tecnicos as $tecnico
                                ): ?>

                                    <?php

                                    $nome_tecnico =
                                        trim(
                                            $tecnico->firstname .
                                            ' ' .
                                            $tecnico->lastname
                                        );

                                    ?>

                                    <option
                                        value="<?php
                                            echo $tecnico->staffid;
                                        ?>"
                                        <?php

                                        if (
                                            $editando &&
                                            isset(
                                                $ordem->tecnico
                                            ) &&
                                            trim(
                                                $ordem->tecnico
                                            ) ===
                                            trim(
                                                $nome_tecnico
                                            )
                                        ) {

                                            echo 'selected';
                                        }

                                        ?>
                                    >

                                        <?php
                                        echo html_escape(
                                            $nome_tecnico
                                        );
                                        ?>

                                        <?php if (
                                            !empty(
                                                $tecnico->role_name
                                            )
                                        ): ?>

                                            -
                                            <?php
                                            echo html_escape(
                                                $tecnico->role_name
                                            );
                                            ?>

                                        <?php endif; ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- ================================================= -->
                        <!-- DATA PREVISTA -->
                        <!-- ================================================= -->

                        <div class="form-group">

                            <label for="data_prevista">
                                Data prevista
                            </label>

                            <input
                                type="date"
                                name="data_prevista"
                                id="data_prevista"
                                class="form-control"
                                value="<?php

                                    echo (
                                        $editando &&
                                        !empty(
                                            $ordem->data_prevista
                                        )
                                    )
                                        ? $ordem->data_prevista
                                        : '';

                                ?>"
                                min="<?php
                                    echo date('Y-m-d');
                                ?>"
                                <?php

                                /*
                                 * Na criação:
                                 * NÃO é obrigatória.
                                 *
                                 * Na edição:
                                 * pode ser preenchida pelo
                                 * usuário com manage_schedule.
                                 */

                                echo (
                                    $editando &&
                                    !$pode_agenda
                                )
                                    ? 'readonly'
                                    : '';

                                ?>
                            >

                            <?php if (!$editando): ?>

                                <small class="text-muted">
                                    A data prevista será definida
                                    posteriormente pelo responsável.
                                </small>

                            <?php endif; ?>

                        </div>


                        <!-- ================================================= -->
                        <!-- DATA REALIZADA -->
                        <!-- ================================================= -->

                        <div class="form-group">

                            <label for="data_realizada">
                                Data realizada
                            </label>

                            <input
                                type="date"
                                name="data_realizada"
                                id="data_realizada"
                                class="form-control"
                                value="<?php

                                    echo (
                                        $editando &&
                                        !empty(
                                            $ordem->data_realizada
                                        )
                                    )
                                        ? $ordem->data_realizada
                                        : '';

                                ?>"
                                max="<?php
                                    echo date('Y-m-d');
                                ?>"
                            >

                        </div>


                        <!-- ================================================= -->
                        <!-- STATUS -->
                        <!-- ================================================= -->

                        <div class="form-group">

                            <label for="status">
                                Status
                            </label>

                            <select
                                name="status"
                                id="status"
                                class="form-control"
                            >

                                <option
                                    value="Aberto"
                                    <?php

                                    if (
                                        (
                                            $editando &&
                                            isset(
                                                $ordem->status
                                            ) &&
                                            $ordem->status ===
                                            'Aberto'
                                        )
                                        ||
                                        (
                                            !$editando
                                        )
                                    ) {

                                        echo 'selected';
                                    }

                                    ?>
                                >
                                    Aberto
                                </option>


                                <option
                                    value="Pendente"
                                    <?php

                                    if (
                                        $editando &&
                                        isset(
                                            $ordem->status
                                        ) &&
                                        $ordem->status ===
                                        'Pendente'
                                    ) {

                                        echo 'selected';
                                    }

                                    ?>
                                >
                                    Pendente
                                </option>


                                <option
                                    value="Em andamento"
                                    <?php

                                    if (
                                        $editando &&
                                        isset(
                                            $ordem->status
                                        ) &&
                                        $ordem->status ===
                                        'Em andamento'
                                    ) {

                                        echo 'selected';
                                    }

                                    ?>
                                >
                                    Em andamento
                                </option>


                                <option
                                    value="Concluída"
                                    <?php

                                    if (
                                        $editando &&
                                        isset(
                                            $ordem->status
                                        ) &&
                                        $ordem->status ===
                                        'Concluída'
                                    ) {

                                        echo 'selected';
                                    }

                                    ?>
                                >
                                    Concluída
                                </option>

                            </select>

                        </div>


                        <!-- ================================================= -->
                        <!-- OBSERVAÇÃO -->
                        <!-- ================================================= -->

                        <div class="form-group">

                            <label for="observacao">
                                Observação
                            </label>

                            <textarea
                                name="observacao"
                                id="observacao"
                                class="form-control"
                                rows="5"
                                <?php

                                echo (
                                    $editando &&
                                    !$pode_observacao
                                )
                                    ? 'readonly'
                                    : '';

                                ?>
                            ><?php

                                if (
                                    $editando &&
                                    isset(
                                        $ordem->observacao
                                    )
                                ) {

                                    echo html_escape(
                                        $ordem->observacao
                                    );
                                }

                                ?></textarea>

                        </div>


                        <!-- ================================================= -->
                        <!-- VALOR -->
                        <!-- ================================================= -->

                        <?php if ($pode_valor): ?>

                            <div class="form-group">

                                <label for="valor">
                                    Valor
                                </label>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="valor"
                                    id="valor"
                                    class="form-control"
                                    value="<?php

                                        echo (
                                            $editando &&
                                            isset(
                                                $ordem->valor
                                            ) &&
                                            $ordem->valor !== null
                                        )
                                            ? $ordem->valor
                                            : '';

                                    ?>"
                                >

                            </div>

                        <?php endif; ?>


                        <!-- ================================================= -->
                        <!-- BOTÕES -->
                        <!-- ================================================= -->

                        <div class="form-group">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >

                                <?php

                                echo $editando
                                    ? 'Salvar alterações'
                                    : 'Criar Ordem de Serviço';

                                ?>

                            </button>


                            <a
                                href="<?php
                                    echo admin_url(
                                        'ordens_servico'
                                    );
                                ?>"
                                class="btn btn-default"
                            >

                                Cancelar

                            </a>

                        </div>


                        <?php echo form_close(); ?>


                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<?php init_tail(); ?>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        var dataRealizada =
            document.getElementById(
                'data_realizada'
            );

        var status =
            document.getElementById(
                'status'
            );


        function atualizarStatus()
        {
            if (
                !dataRealizada ||
                !status
            ) {

                return;
            }


            /*
             * Se existe data realizada,
             * o status deve ser Concluída.
             */

            if (
                dataRealizada.value !== ''
            ) {

                status.value =
                    'Concluída';

                status.disabled =
                    true;

            } else {

                status.disabled =
                    false;
            }
        }


        atualizarStatus();


        if (dataRealizada) {

            dataRealizada.addEventListener(
                'change',
                atualizarStatus
            );

            dataRealizada.addEventListener(
                'input',
                atualizarStatus
            );
        }

    }
);

</script>