<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">

    <div class="content">

        <div class="row">

            <div class="col-md-12">

                <div class="panel_s">

                    <div class="panel-body">

                        <h4 class="no-margin">
                            <?php
                            echo isset($ordem)
                                ? 'Editar Ordem de Serviço'
                                : 'Nova Ordem de Serviço';
                            ?>
                        </h4>

                        <hr>

                        <?php

                        $editando = isset($ordem);

                        $pode_empresa = (
                            !$editando
                            ||
                            staff_can(
                                'edit_company',
                                'ordens_servico'
                            )
                        );

                        $pode_agenda = (
                            !$editando
                            ||
                            staff_can(
                                'manage_schedule',
                                'ordens_servico'
                            )
                        );

                        $pode_status = (
                            !$editando
                            ||
                            staff_can(
                                'manage_status',
                                'ordens_servico'
                            )
                        );

                        $pode_observacao = (
                            !$editando
                            ||
                            staff_can(
                                'add_observation',
                                'ordens_servico'
                            )
                        );

                        $pode_valor = staff_can(
                            'view_value',
                            'ordens_servico'
                        );

                        $status_atual = $editando
                            ? $ordem->status
                            : 'Pendente';

                        /*
                         * Se existe data realizada,
                         * o status obrigatoriamente é Concluída.
                         */
                        if (
                            $editando
                            &&
                            !empty($ordem->data_realizada)
                        ) {

                            $status_atual = 'Concluída';

                        }

                        ?>

                        <?php
                        echo form_open(
                            $editando
                                ? admin_url(
                                    'ordens_servico/edit/'
                                    . $ordem->id
                                )
                                : admin_url(
                                    'ordens_servico/create'
                                )
                        );
                        ?>


                        <!-- EMPRESA -->
                        <div class="form-group">

                            <label for="empresa">
                                Empresa
                            </label>

                            <?php if ($pode_empresa): ?>

                                <select
                                    name="empresa"
                                    id="empresa"
                                    class="form-control"
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
                                                echo (int) $cliente->userid;
                                            ?>"
                                            <?php

                                            if (
                                                $editando
                                                &&
                                                $ordem->empresa
                                                ===
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

                            <?php else: ?>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?php
                                        echo html_escape(
                                            $ordem->empresa
                                        );
                                    ?>"
                                    readonly
                                >

                                <input
                                    type="hidden"
                                    name="empresa"
                                    value="<?php
                                        echo html_escape(
                                            $ordem->empresa
                                        );
                                    ?>"
                                >

                            <?php endif; ?>

                        </div>


                        <!-- TÉCNICO -->
                        <div class="form-group">

                            <label for="tecnico">
                                Técnico
                            </label>

                            <?php if ($pode_agenda): ?>

                                <select
                                    name="tecnico"
                                    id="tecnico"
                                    class="form-control"
                                    required
                                >

                                    <option value="">
                                        Selecione um técnico
                                    </option>

                                    <?php foreach (
                                        $tecnicos as $tecnico
                                    ): ?>

                                        <?php
                                        $nome_tecnico =
                                            $tecnico->firstname
                                            . ' '
                                            . $tecnico->lastname;
                                        ?>

                                        <option
                                            value="<?php
                                                echo (int) $tecnico->staffid;
                                            ?>"
                                            <?php

                                            if (
                                                $editando
                                                &&
                                                $ordem->tecnico
                                                ===
                                                $nome_tecnico
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

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            <?php else: ?>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?php
                                        echo html_escape(
                                            $ordem->tecnico
                                        );
                                    ?>"
                                    readonly
                                >

                                <input
                                    type="hidden"
                                    name="tecnico"
                                    value="<?php
                                        echo html_escape(
                                            $ordem->tecnico
                                        );
                                    ?>"
                                >

                            <?php endif; ?>

                        </div>


                        <!-- DATA PREVISTA -->
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
                                    echo $editando
                                        ? html_escape(
                                            $ordem->data_prevista
                                        )
                                        : '';
                                ?>"
                                min="<?php
                                    echo date('Y-m-d');
                                ?>"
                                <?php
                                echo $pode_agenda
                                    ? ''
                                    : 'readonly';
                                ?>
                                required
                            >

                        </div>


                        <!-- DATA REALIZADA -->
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
                                        $editando
                                        &&
                                        !empty(
                                            $ordem->data_realizada
                                        )
                                    )
                                        ? html_escape(
                                            $ordem->data_realizada
                                        )
                                        : '';
                                ?>"
                                max="<?php
                                    echo date('Y-m-d');
                                ?>"
                                <?php
                                echo $pode_observacao
                                    ? ''
                                    : 'readonly';
                                ?>
                            >

                            <small class="text-muted">
                                Se informar a data realizada,
                                o status será automaticamente
                                definido como Concluída.
                            </small>

                        </div>


                        <!-- STATUS -->
                        <div class="form-group">

                            <label for="status">
                                Status
                            </label>

                            <select
                                name="status"
                                id="status"
                                class="form-control"
                                <?php
                                echo $pode_status
                                    ? ''
                                    : 'disabled';
                                ?>
                            >

                                <option
                                    value="Pendente"
                                    <?php
                                    echo $status_atual
                                        === 'Pendente'
                                        ? 'selected'
                                        : '';
                                    ?>
                                >
                                    Pendente
                                </option>

                                <option
                                    value="Em andamento"
                                    <?php
                                    echo $status_atual
                                        === 'Em andamento'
                                        ? 'selected'
                                        : '';
                                    ?>
                                >
                                    Em andamento
                                </option>

                                <option
                                    value="Concluída"
                                    <?php
                                    echo $status_atual
                                        === 'Concluída'
                                        ? 'selected'
                                        : '';
                                    ?>
                                >
                                    Concluída
                                </option>

                            </select>

                        </div>


                        <!-- OBSERVAÇÃO -->
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
                                echo $pode_observacao
                                    ? ''
                                    : 'readonly';
                                ?>
                            ><?php
                                echo $editando
                                    ? html_escape(
                                        $ordem->observacao
                                    )
                                    : '';
                            ?></textarea>

                        </div>


                        <!-- VALOR -->
                        <?php if ($pode_valor): ?>

                            <div class="form-group">

                                <label for="valor">
                                    Valor
                                </label>

                                <input
                                    type="number"
                                    name="valor"
                                    id="valor"
                                    class="form-control"
                                    step="0.01"
                                    min="0"
                                    value="<?php
                                        echo (
                                            $editando
                                            &&
                                            $ordem->valor
                                            !== null
                                        )
                                            ? html_escape(
                                                $ordem->valor
                                            )
                                            : '';
                                    ?>"
                                >

                            </div>

                        <?php endif; ?>


                        <!-- BOTÕES -->
                        <div class="form-group">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >

                                <i class="fa fa-save"></i>

                                Salvar

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


        function atualizarStatus() {

            if (!dataRealizada || !status) {
                return;
            }


            /*
             * Se existe Data realizada,
             * o status obrigatoriamente é Concluída.
             */
            if (dataRealizada.value !== '') {

                status.value = 'Concluída';

                status.disabled = true;

            } else {

                status.disabled = false;

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


<?php init_tail(); ?>