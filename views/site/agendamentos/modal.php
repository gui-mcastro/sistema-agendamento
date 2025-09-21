<?php

use app\models\Colaboradores;
use kartik\date\DatePicker;
use kartik\time\TimePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;
use app\models\Agendamentos;

/**
 * @var $model     Agendamentos
 * @var $modelUser \app\models\User
 */

$form = ActiveForm::begin([
  'id' => 'agendamento-form',
  'action' => ['site/agendamentos'],
  'method' => 'post',
  'enableAjaxValidation' => true,
  'enableClientValidation' => false,
  'validateOnChange' => false,
  'validateOnSubmit' => true,
  'validateOnBlur' => true,
  'options' => ['class' => 'needs-validation'],
  'errorCssClass' => 'is-invalid',
  'successCssClass' => 'is-valid',
  'fieldConfig' => [
    'template' => "{label}\n{input}\n{error}",
    'labelOptions' => ['class' => 'col-lg-1 col-form-label mr-lg-3'],
    'inputOptions' => ['class' => 'col-lg-3 form-control'],
    'errorOptions' => ['class' => 'col-lg-7 invalid-feedback'],

  ],
]);
echo Html::activeHiddenInput($modelUser, 'st_ativo', ['value' => $modelUser->st_ativo ?: 1]);
?>
<div class="modal" id="agendamento-modal">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Agendamento</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-4">
                        <?=
                        $form->field($modelUser, 'nr_cpf')->widget(
                          MaskedInput::class,
                          [
                            'mask' => ['999.999.999-99'],
                            'clientOptions' => [
                              'keepStatic' => true,
                              'greedy' => false,
                              'clearIncomplete' => false,
                              'autoUnmask' => true,
                              'removeMaskOnSubmit' => true,
                            ],
                            'options' => [
                              'placeholder' => 'Digite o CPF...',
                              'class' => 'form-control',
                            ],
                          ]
                        )->label('CPF'); ?>
                    </div>
                    <div class="col-4">
                        <?=
                        $form->field($modelUser, 'nm_nome')->textInput([
                          'placeholder' => 'Digite o nome...',
                          'class' => 'form-control',
                        ])->label('Nome');
                        ?>
                    </div>
                    <div class="col-4">
                        <?=
                        $form->field($modelUser, 'email')->textInput([
                          'placeholder' => 'Digite o email...',
                          'class' => 'form-control',
                        ]);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="form-group">
                            <?php
                            $items = Agendamentos::find()
                              ->select([
                                "CONCAT(c.tp_especialidade, ' (', c.nm_colaborador, ')') as modalidade",
                              ])
                              ->leftJoin('colaboradores c', 'agendamentos.cd_colaborador = c.cd_colaborador')
                              ->groupBy(['agendamentos.cd_colaborador', 'modalidade'])
                              ->indexBy('cd_colaborador')
                              ->column();
                            echo $form->field($model, 'cd_colaborador')->dropDownList(
                              $items,
                              [
                                'prompt' => 'Selecione a modalidade...',
                                'class' => 'form-select',
                              ]
                            ) ?>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <?= $form->field($model, 'dt_agendamento')
                              ->widget(MaskedInput::class, [
                                'mask' => '99/99/9999',
                                'options' => ['placeholder' => 'dd/mm/aaaa',],
                              ])->widget(DatePicker::class, [
                                'language' => 'pt',
                                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                                'pluginOptions' => [
                                  'autoclose' => true,
                                  'format' => 'dd/mm/yyyy',
                                  'orientation' => 'bottom',
                                ],
                                'options' => ['placeholder' => 'dd/mm/aaaa',],
                              ]) ?>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <?= $form->field($model, 'hrAgendamento')->widget(TimePicker::class, [
                              'pluginOptions' => [
                                'showSeconds' => false,
                                'showMeridian' => false,
                                'minuteStep' => 30,
                                'minTime' => '08:00',
                                'maxTime' => '18:00',
                                'defaultTime' => false,
                              ],
                              'options' => ['placeholder' => 'HH:mm', 'value' => $model->hrAgendamento ?: '',],
                            ])
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <?= Html::button('<i class="fas fa-save"></i> Salvar', [
                  'id' => 'btn-save-agendamento',
                  'name' => 'btn_save',
                  'class' => 'btn btn-success',
                  'encode' => false
                ]) ?>

                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <i class="fa fa-times"></i> Fechar
                </button>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
<script>
    (function () {
        'use strict';
        $('#user-nr_cpf').on('blur', function () {
            var cpf = $(this).val();
            if (cpf.length > 0) {
                $.ajax({
                    url: '<?= Yii::$app->urlManager->createUrl(['site/carrega-campos']) ?>',
                    type: 'POST',
                    data: {
                        cpf: cpf,
                    },
                    success: function (data) {
                        console.log(data);
                        if (data && data.nm_nome) {
                            $('#user-nm_nome').prop('disabled', true).val(data.nm_nome);
                            $('#user-email').prop('disabled', true).val(data.email);
                            $('#user-st_ativo').val(data.st_ativo);
                        } else {
                            $('#user-nm_nome').prop('disabled', false).val('');
                            $('#user-email').prop('disabled', false).val('');
                        }
                    },
                });
            }
        });
    })();
</script>

