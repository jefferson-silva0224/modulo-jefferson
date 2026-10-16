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

                        echo form_open(
                            isset($ordem)
                                ? admin_url(
                                    'ordens_servico/edit/'
                                    . $ordem->id
                                )
                                : admin_url(
                                    'ordens_servico/create'
                                )
                        );

                        ?>


                        <!-- ==========================================
                             EMPRESA
                             ========================================== -->

                        <div class="form-group">

                            <label for="empresa">
                                Empresa <span class="text-danger">*</span>
                            </label>

                            <select
                                name="empresa"
                                id="empresa"
                                class="form-control"
                                required
                                <?php
                                echo (
                                    isset($ordem)
                                    &&
                                    !staff_can(
                                        'edit_company',
                                        'ordens_servico'
                                    )
                                    &&
                                    !is_admin()
                                )
                                    ? 'disabled'
                                    : '';
                                ?>
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
                                            isset($ordem) && (
                                                (!empty($ordem->empresa_id) && (int) $ordem->empresa_id === (int) $cliente->userid)
                                                ||
                                                (empty($ordem->empresa_id) && isset($ordem->empresa) && $ordem->empresa === $cliente->company)
                                            )
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


                        <!-- ==========================================
                             TÉCNICO
                             ========================================== -->

                        <div class="form-group">

                            <label for="tecnico">
                                Técnico
                            </label>

                            <select
                                name="tecnico"
                                id="tecnico"
                                class="form-control"
                                <?php

                                if (
                                    isset($ordem)
                                    &&
                                    !staff_can(
                                        'manage_schedule',
                                        'ordens_servico'
                                    )
                                    &&
                                    !is_admin()
                                ) {

                                    echo 'disabled';
                                }

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
    value="<?php echo (int) $tecnico->staffid; ?>"
    <?php
    if (
        isset($ordem) && (
            (!empty($ordem->tecnico_id) && (int) $ordem->tecnico_id === (int) $tecnico->staffid)
            ||
            (empty($ordem->tecnico_id) && isset($ordem->tecnico) && trim($ordem->tecnico) === $nome_tecnico)
        )
    ) {
        echo 'selected';
    }
    ?>
>
    <?php echo html_escape($nome_tecnico); ?>
</option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- ==========================================
                             DATA PREVISTA
                             ========================================== -->

                        <div class="form-group">

                            <label for="data_prevista">
                                Data prevista
                            </label>

                            <input
                                type="date"
                                name="data_prevista"
                                id="data_prevista"
                                class="form-control"
                                min="<?php
                                    echo date('Y-m-d');
                                ?>"
                                value="<?php

                                    if (
                                        isset($ordem)
                                        &&
                                        !empty(
                                            $ordem->data_prevista
                                        )
                                    ) {

                                        echo html_escape(
                                            $ordem->data_prevista
                                        );
                                    }

                                ?>"
                                <?php

                                if (
                                    isset($ordem)
                                    &&
                                    !staff_can(
                                        'manage_schedule',
                                        'ordens_servico'
                                    )
                                    &&
                                    !is_admin()
                                ) {

                                    echo 'readonly';
                                }

                                ?>
                            >

                        </div>


                        <!-- ==========================================
                             DATA REALIZADA
                             ========================================== -->

                        <div class="form-group">

                            <label for="data_realizada">
                                Data realizada
                            </label>

                            <input
                                type="date"
                                name="data_realizada"
                                id="data_realizada"
                                class="form-control"
                                max="<?php
                                    echo date('Y-m-d');
                                ?>"
                                value="<?php

                                    if (
                                        isset($ordem)
                                        &&
                                        !empty(
                                            $ordem->data_realizada
                                        )
                                    ) {

                                        echo html_escape(
                                            $ordem->data_realizada
                                        );
                                    }

                                ?>"
                            >

                        </div>


                        <!-- ==========================================
                             STATUS
                             ========================================== -->

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
                                        !isset($ordem)
                                        ||
                                        (
                                            isset($ordem)
                                            &&
                                            $ordem->status === 'Aberto'
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
                                        isset($ordem)
                                        &&
                                        $ordem->status === 'Pendente'
                                    ) {

                                        echo 'selected';
                                    }

                                    ?>
                                >
                                    Pendente
                                </option>


                                <option
                                    value="Concluída"
                                    <?php

                                    if (
                                        isset($ordem)
                                        &&
                                        $ordem->status === 'Concluída'
                                    ) {

                                        echo 'selected';
                                    }

                                    ?>
                                >
                                    Concluída
                                </option>

                            </select>

                        </div>


                        <!-- ==========================================
                             OBSERVAÇÃO
                             ========================================== -->

                        <div class="form-group">

                            <label for="observacao">
                                Observação
                            </label>

                            <textarea
                                name="observacao"
                                id="observacao"
                                class="form-control"
                                rows="5"
                                placeholder="Digite a observação do serviço..."
                            ><?php

                            if (
                                isset($ordem)
                                &&
                                !empty(
                                    $ordem->observacao
                                )
                            ) {

                                echo html_escape(
                                    $ordem->observacao
                                );
                            }

                            ?></textarea>

                        </div>


                        <!-- ==========================================
                             VALOR
                             ========================================== -->

                        <?php if (
                            staff_can(
                                'view_value',
                                'ordens_servico'
                            )
                        ): ?>

                            <div class="form-group">

                                <label for="valor">
                                    Valor
                                </label>

                                <div class="input-group">

                                    <span class="input-group-addon">
                                        R$
                                    </span>

                                    <input
                                        type="number"
                                        name="valor"
                                        id="valor"
                                        class="form-control"
                                        step="0.01"
                                        min="0"
                                        placeholder="0,00"
                                        value="<?php

                                            if (
                                                isset($ordem)
                                                &&
                                                $ordem->valor
                                                !== null
                                            ) {

                                                echo html_escape(
                                                    $ordem->valor
                                                );
                                            }

                                        ?>"
                                    >

                                </div>

                            </div>

                        <?php endif; ?>


                        <hr>


                        <!-- ==========================================
                             BOTÕES
                             ========================================== -->

                        <div class="form-group">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >

                                <i class="fa fa-save"></i>

                                <?php
                                echo isset($ordem)
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

                                <i class="fa fa-arrow-left"></i>

                                Voltar

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


        /*
        |--------------------------------------------------------------------------
        | DATA REALIZADA = CONCLUÍDA
        |--------------------------------------------------------------------------
        */

        function verificarStatus() {

            if (
                dataRealizada
                &&
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


        if (dataRealizada) {

            dataRealizada.addEventListener(
                'change',
                verificarStatus
            );

        }


        verificarStatus();

    }
);

</script>


<?php init_tail(); ?>